<?php

namespace App\Repositories\Focus\customer;

use DB;
use App\Models\customer\Customer;
use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Storage;
use App\Models\branch\Branch;
use App\Models\invoice\Invoice;
use App\Models\items\JournalItem;
use App\Models\manualjournal\Journal;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Class CustomerRepository.
 */
class CustomerRepository extends BaseRepository
{
    /**
     *customer_picture_path .
     *
     * @var string
     */
    protected $customer_picture_path;


    /**
     * Storage Class Object.
     *
     * @var \Illuminate\Support\Facades\Storage
     */
    protected $storage;

    /**
     * Associated Repository Model.
     */
    const MODEL = Customer::class;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->customer_picture_path = 'img' . DIRECTORY_SEPARATOR . 'customer' . DIRECTORY_SEPARATOR;
        $this->storage = Storage::disk('public');
    }

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
        $q->when(request('g_rel_type'), function ($q) {
            return $q->where('rel_id', '=',request('g_rel_id',-1));
        });
        if (!request('g_rel_type') AND request('g_rel_id')) {
            $q->whereHas('group', function ($s) {
                return $s->where('customer_group_id', '=', request('g_rel_id', 0));
            });
        }
        return $q->get(['id','name','company','email','address','picture','active','created_at']);
    }

    public function getInvoicesForDataTable($customer_id = 0)
    {
        return Invoice::where('customer_id', request('customer_id', $customer_id))->get();
    }

    public function getTransactionsForDataTable($customer_id = 0)
    {
        $id = request('customer_id', $customer_id);
         
        $q = Transaction::whereHas('account', function ($q) { 
            $q->where('system', 'receivable');  
        })->where('tr_type', 'inv')->whereHas('invoice', function ($q) use($id) { 
            $q->where('customer_id', $id); 
        })->orWhereHas('account', function ($q) { 
            $q->where('system', 'receivable');  
        })->where('tr_type', 'pmt')->whereHas('paidinvoice', function ($q) use($id) {
            $q->where('customer_id', $id);
        })->orWhereHas('account', function ($q) { 
            $q->where('system', 'receivable');  
        })->where('tr_type', 'withholding')->whereHas('withholding', function ($q) use($id) {
            $q->where('customer_id', $id);
        })->orWhereHas('account', function ($q) { 
            $q->where('system', 'receivable');  
        })->where('tr_type', 'cnote')->whereHas('creditnote', function ($q) use($id) {
            $q->where('customer_id', $id);
        })->orWhereHas('account', function ($q) { 
            $q->where('system', 'receivable');  
        })->where('tr_type', 'dnote')->whereHas('debitnote', function ($q) use($id) {
            $q->where('customer_id', $id);
        });       
                    
        // on date filter
        $start_date = request('start_date');
        $end_date = request('end_date');
        if ($start_date && $end_date && request('is_transaction')) {
            $start_date = date_for_database($start_date);
            $end_date = date_for_database($end_date);
            $prior_date = date('Y-m-d', strtotime($start_date . ' - 1 day'));

            $params = ['id', 'tr_date', 'tr_type', 'note', 'debit', 'credit'];
            $transactions = Transaction::whereIn('id', $q->pluck('id'))->whereBetween('tr_date', [$start_date, $end_date])->get($params);
            // compute balance brought foward as of start date
            $bf_transactions = Transaction::whereIn('id', $q->pluck('id'))->where('tr_date', '<', $start_date)->get($params);
            $debit_balance = $bf_transactions->sum('debit') - $bf_transactions->sum('credit');
            if ($debit_balance) {
                $record = (object) array(
                    'id' => 0,
                    'tr_date' => $prior_date,
                    'tr_type' => 'bbf',
                    'note' => 'Balance brought foward as of '. dateFormat($start_date),
                    'debit' => $debit_balance > 0 ? $debit_balance : 0,
                    'credit' => $debit_balance < 0 ? ($debit_balance * -1) : 0,
                );
                // merge balance to the rest of records within date boundary
                $transactions = collect([$record])->merge($transactions);
            }

            return $transactions;
        }

        return $q->get();
    }

    public function getStatementForDataTable($customer_id = 0)
    {
        $i = 0;
        $statement = collect();
        $invoices = $this->getInvoicesForDataTable($customer_id);
        foreach ($invoices as $invoice) {
            $i++;
            $tid = gen4tid('Inv-', $invoice->tid);
            $note = $invoice->notes;
            $inv_record = (object) array(
                'id' => $i,
                'date' => $invoice->invoicedate,
                'type' => 'invoice',
                'note' => '(' . $tid . ')' . ' ' . $note,
                'debit' => $invoice->total,
                'credit' => 0
            );

            $payments = collect();
            if ($invoice->payments->count()) {
                foreach ($invoice->payments as $pmt) {
                    $i++;
                    $reference = $pmt->paid_invoice->reference;
                    $mode = $pmt->paid_invoice->payment_mode;
                    $record = (object) array(
                        'id' => $i,
                        'date' => $pmt->paid_invoice->date,
                        'type' => 'payment',
                        'note' => '(' . $tid . ')' . ' reference: ' . $reference . ' mode: ' . ucfirst($mode),
                        'debit' => 0,
                        'credit' => $pmt->paid
                    );
                    $payments->add($record);
                }    
            }
            $withholdings = collect();
            if ($invoice->withholding_payments->count()) {
                foreach ($invoice->withholding_payments as $pmt) {
                    $i++;
                    $note = $pmt->withholding->doc_ref . ' - ' . $pmt->withholding->certificate . ' ' . $pmt->withholding->note;
                    $record = (object) array(
                        'id' => $i,
                        'date' => $pmt->withholding->date,
                        'type' => 'withholding',
                        'note' => '(' . $tid . ')' . ' ' . $note,
                        'debit' => 0,
                        'credit' => $pmt->paid
                    );
                    $withholdings->add($record);
                }   
            }
            $creditnotes = collect();
            if ($invoice->creditnotes->count()) {
                foreach ($invoice->creditnotes as $cnote) {
                    $i++;
                    $record = (object) array(
                        'id' => $i,
                        'date' => $cnote->date,
                        'type' => 'credit-note',
                        'note' => '(' . $tid . ')' . ' ' . $cnote->note,
                        'debit' => 0,
                        'credit' => $cnote->total
                    );
                    $creditnotes->add($record);
                }   
            }
            $debitnotes = collect();
            if ($invoice->debitnotes->count()) {
                foreach ($invoice->debitnotes as $dnote) {
                    $i++;
                    $record = (object) array(
                        'id' => $i,
                        'date' => $dnote->date,
                        'type' => 'debit-note',
                        'note' => '(' . $tid . ')' . ' ' . $dnote->note,
                        'dedit' => $dnote->total,
                        'credit' => 0,
                    );
                    $debitnotes->add($record);
                }   
            }

            if ($payments->count() || $withholdings->count() || $creditnotes->count()) {
                $statement->add($inv_record);
                $statement = $statement->merge($payments);
                $statement = $statement->merge($creditnotes);
                $statement = $statement->merge($withholdings);
            } else $statement->add($inv_record);
        }

        return $statement;
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @return bool
     * @throws GeneralException
     */
    public function create(array $input)
    {
        // dd($input);      
        if (!empty($input['picture'])) 
            $input['picture'] = $this->uploadPicture($input['picture']);

        DB::beginTransaction();
        
        $email_exists = Customer::where('email', $input['email'])->count();
        if ($email_exists) throw ValidationException::withMessages(['Duplicate Email!']);

        $input['open_balance'] = numberClean($input['open_balance']);
        $input['open_balance_date'] = date_for_database($input['open_balance_date']);  
        $result = Customer::create($input);

        $branches = [['name' => 'All Branches'], ['name' => 'Head Office']];
        $branches = array_map(function ($v) use($result) {
            return array_replace($v, [
                'customer_id' => $result->id,
                'ins' => $result->ins
            ]);
        }, $branches);
        Branch::insert($branches);

        $open_balance = $result->open_balance;
        $open_balance_date = $result->open_balance_date;
        if ($open_balance > 0) {
            $note = $result->id . '-customer Account Opening Balance ' . $result->open_balance_note;
            $user_id = auth()->user()->id;
            $data = [
                'tid' => Journal::max('tid') + 1,
                'date' => $open_balance_date,
                'note' => $note,
                'debit_ttl' => $open_balance,
                'credit_ttl' => $open_balance,
                'ins' => $result->ins,
                'user_id' => $user_id,
            ];
            $journal = Journal::create($data);

            $debtor_account = Account::where('system', 'receivable')->first(['id']);
            foreach ([1,2] as $v) {
                $data = [
                    'journal_id' => $journal->id,
                    'account_id' => $debtor_account->id,
                ];
                if ($v == 1) $data['debit'] = $open_balance;
                else {
                    $balance_account = Account::where('system', 'retained_earning')->first(['id']);
                    $data['account_id'] = $balance_account->id;
                    $data['credit'] = $open_balance;
                }                
                JournalItem::create($data);
            }

            // generate invoice (unrecognised sale)
            $invoice_data = [
                'invoicedate' => $open_balance_date,
                'invoiceduedate' => $open_balance_date,
                'subtotal' => $open_balance,
                'total' => $open_balance,
                'notes' => $note,
                'customer_id' => $result->id,
                'user_id' => $user_id,
                'ins' => $result->ins,
                'account_id' => $result->sale_account_id
            ];
            Invoice::create($invoice_data);

            /** accounting */
            $data = array_replace($journal->toArray(), [
                'open_balance' => $open_balance,
                'account_id' => $debtor_account->id,
            ]);
            $this->post_transaction((object) $data);
        }

        DB::commit();
        if ($result) return $result;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Customer $customer
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($customer, array $input)
    {
        // dd($input);
        if (!empty($input['picture'])) {
            $this->removePicture($customer, 'picture');
            $input['picture'] = $this->uploadPicture($input['picture']);
        }
        if (empty($input['password'])) unset($input['password']);

        $email_exists = Customer::where('id', '!=', $customer->id)->where('email', $input['email'])->count();
        if ($email_exists) throw ValidationException::withMessages(['Email already in use!']);

        DB::beginTransaction();

        $input = array_replace($input, [
            'open_balance' => numberClean($input['open_balance']),
            'open_balance_date' =>  date_for_database($input['open_balance_date'])
        ]);
        $result = $customer->update($input);

        $open_balance = $customer->open_balance;
        $open_balance_date = $customer->open_balance_date;
        if ($open_balance > 0) {
            $data = array();
            $user_id = auth()->user()->id;
            $note = $customer->id . '-customer Account Opening Balance ' . $customer->open_balance_note;
            $journal = Journal::where('note', 'LIKE', '%' . $customer->id . '-customer Account Opening Balance ' . '%')->first();

            if (!$journal) {
                $data = [
                    'tid' => Journal::max('tid') + 1,
                    'date' => $open_balance_date,
                    'note' => $note,
                    'debit_ttl' => $open_balance,
                    'credit_ttl' => $open_balance,
                    'ins' => $customer->ins,
                    'user_id' => $user_id,
                ];
                $journal = Journal::create($data);
    
                $debtor_account = Account::where('system', 'receivable')->first(['id']);
                foreach ([1,2] as $v) {
                    $data = [
                        'journal_id' => $journal->id,
                        'account_id' => $debtor_account->id,
                    ];
                    if ($v == 1) $data['debit'] = $open_balance;
                    else {
                        $balance_account = Account::where('system', 'retained_earning')->first(['id']);
                        $data['account_id'] = $balance_account->id;
                        $data['credit'] = $open_balance;
                        
                    }                
                    JournalItem::create($data);
                }

                // generate invoice (unrecognised sale)
                $invoice_data = [
                    'invoicedate' => $open_balance_date,
                    'invoiceduedate' => $open_balance_date,
                    'subtotal' => $open_balance,
                    'total' => $open_balance,
                    'notes' => $note,
                    'customer_id' => $customer->id,
                    'user_id' => $user_id,
                    'ins' => $customer->ins,
                    'account_id' => $customer->sale_account_id
                ];
                Invoice::create($invoice_data);
                
                $data = array_replace($journal->toArray(), [
                    'open_balance' => $open_balance,
                    'account_id' => $debtor_account->id
                ]);
            } else {
                // remove previous transactions
                Transaction::where(['tr_ref' => $journal->id, 'note' => $journal->note])->delete(); 

                Invoice::where('notes', $journal->note)->first()->update([
                    'notes' => $note, 
                    'subtotal' => $open_balance, 
                    'total' => $open_balance,
                    'account_id' => $customer->sale_account_id
                ]);        
                
                $journal->update([
                    'note' => $note,
                    'date' => $open_balance_date,
                    'debit_ttl' => $open_balance,
                    'credit_ttl' => $open_balance,
                ]);

                $debtor_account = Account::where('system', 'receivable')->first(['id']);   
                $data = array_replace($journal->toArray(), [
                    'open_balance' => $open_balance,
                    'account_id' => $debtor_account->id,
                ]);
    
                foreach ($journal->items as $item) {
                    if ($item->debit > 0) $item->update(['debit' => $open_balance]);
                    elseif ($item->credit > 0) $item->update(['credit' => $open_balance]);
                }
            }
            
            /**accounting */           
            $this->post_transaction((object) $data);
        }     

        DB::commit();
        if ($result) return true;

        throw new GeneralException(trans('exceptions.backend.customers.update_error'));
    }

    public function post_transaction($result)
    {
        // debit Accounts Receivable (Debtor)
        $tr_category = Transactioncategory::where('code', 'genjr')->first(['id', 'code']);
        $dr_data = [
            'tid' => Transaction::max('tid') + 1,
            'account_id' => $result->account_id,
            'trans_category_id' => $tr_category->id,
            'tr_date' => $result->date,
            'due_date' => $result->date,
            'user_id' => $result->user_id,
            'note' => $result->note,
            'debit' => $result->open_balance,
            'ins' => $result->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $result->id,
            'user_type' => 'customer',
            'is_primary' => 1,
        ];
        Transaction::create($dr_data);

        // credit Retained Earning (Equity)
        $account = Account::where('system', 'retained_earning')->first(['id']);
        unset($dr_data['debit'], $dr_data['is_primary']);
        $cr_data = array_replace($dr_data, ['account_id' => $account->id, 'credit' => $result->open_balance]);
        Transaction::create($cr_data);
        aggregate_account_transactions();
    }

    /*
    * Upload logo image
    */
    public function uploadPicture($logo)
    {
        $image = $this->customer_picture_path . time() . $logo->getClientOriginalName();
        $this->storage->put($image, file_get_contents($logo->getRealPath()));

        return $image;
    }

    /*
    * remove logo or favicon icon
    */
    public function removePicture(Customer $customer, $type)
    {
        $path = $this->customer_picture_path;
        $storage_exists = $this->storage->exists($path . $customer->$type);
        if ($customer->$type && $storage_exists) {
            $this->storage->delete($path . $customer->$type);
        }

        if ($customer->update([$type => ''])) return true;
            
        throw new GeneralException(trans('exceptions.backend.settings.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Customer $customer
     * @return bool
     * @throws GeneralException
     */
    public function delete($customer)
    {
        if ($customer->leads->count()) 
            throw ValidationException::withMessages(['Customer has attached Ticket']);
        if ($customer->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.customers.delete_error'));
    }
}
