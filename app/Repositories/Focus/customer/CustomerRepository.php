<?php

namespace App\Repositories\Focus\customer;

use DB;
use App\Models\customer\Customer;
use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Storage;
use App\Models\branch\Branch;
use App\Models\Company\Company;
use App\Models\invoice\Invoice;
use App\Models\items\JournalItem;
use App\Models\manualjournal\Journal;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
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
        // $q->when(request('g_rel_type'), function ($q) {
        //     return $q->where('rel_id', '=',request('g_rel_id',-1));
        // });
        // if (!request('g_rel_type') AND request('g_rel_id')) {
        //     $q->whereHas('group', function ($s) {
        //         return $s->where('customer_group_id', '=', request('g_rel_id', 0));
        //     });
        // }
        return $q->get(['id','name','company','email','address','picture','active','created_at']);
    }

    /**
     * Customer Invoices data
     */
    public function getInvoicesForDataTable($customer_id = 0)
    {
        return Invoice::where('customer_id', request('customer_id', $customer_id))->get();
    }

    /**
     * Statement on account transactions
     */
    public function getTransactionsForDataTable($customer_id = 0)
    {            
        $params = ['customer_id' => request('customer_id', $customer_id)];
        $customer = Customer::find(request('customer_id'), ['id', 'open_balance_note']);

        $q = Transaction::whereHas('account', function ($q) { 
            $q->where('system', 'receivable');  
        })->where(function ($q) use($params) {
            $q->where('tr_type', 'inv')->whereHas('invoice', function ($q1) use($params) { 
                $q1->where($params); 
            })->orWhere('tr_type', 'pmt')->whereHas('paidinvoice', function ($q1) use($params) {
                $q1->where($params);
            })->orWhere('tr_type', 'withholding')->whereHas('withholding', function ($q1) use($params) {
                $q1->where($params);
            })->orWhere('tr_type', 'cnote')->whereHas('creditnote', function ($q1) use($params) {
                $q1->where($params);
            });
        })->orwhere(function ($q) use($customer) {
            $note = "%{$customer->id}-customer Account Opening Balance {$customer->open_balance_note}%";
            $q->where('tr_type', 'genjr')->where('debit', '>', 0)->where('note', 'LIKE', $note);
        });       
        
        // on date filter
        if (request('start_date') && request('is_transaction')) {
            $from = date_for_database(request('start_date'));
            $tr_ids = $q->pluck('id')->toArray();
            
            $params = ['id', 'tr_date', 'tr_type', 'note', 'debit', 'credit'];
            $transactions = Transaction::whereIn('id', $tr_ids)->whereBetween('tr_date', [$from, date('Y-m-d')])->get($params);
            // compute balance brought foward as of start date
            $bf_transactions = Transaction::whereIn('id', $tr_ids)->where('tr_date', '<', $from)->get($params);
            $debit_balance = $bf_transactions->sum('debit') - $bf_transactions->sum('credit');
            if ($debit_balance) {
                $record = (object) array(
                    'id' => 0,
                    'tr_date' => date('Y-m-d', strtotime($from . ' - 1 day')),
                    'tr_type' => 'balance',
                    'note' => '** Balance Brought Foward ** ',
                    'debit' => $debit_balance > 0 ? $debit_balance : 0,
                    'credit' => $debit_balance < 0 ? ($debit_balance * -1) : 0,
                );
                // merge brought foward balance with the rest of the transactions
                $transactions = collect([$record])->merge($transactions);
            }

            return $transactions;
        }

        return $q->get();
    }

    /**
     * Statement on invoice records
     */
    public function getStatementForDataTable($customer_id = 0)
    {
        $q = Invoice::where('customer_id', request('customer_id', $customer_id));
        
        $q->with(['payments', 'withholding_payments', 'creditnotes', 'debitnotes']);
        
        return $this->generate_statement($q->get());
    }

    // generate statement
    public function generate_statement($invoices = [])
    {
        $i = 0;
        $statement = collect();
        foreach ($invoices as $invoice) {
            $i++;
            $invoice_id = $invoice->id;
            $tid = gen4tid('Inv-', $invoice->tid);
            $note = $invoice->notes;
            $inv_record = (object) array(
                'id' => $i,
                'date' => $invoice->invoicedate,
                'type' => 'invoice',
                'note' => '(' . $tid . ')' . ' ' . $note,
                'debit' => $invoice->total,
                'credit' => 0,
                'invoice_id' => $invoice_id
            );

            $payments = collect();
            foreach ($invoice->payments as $pmt) {
                if (!$pmt->paid_invoice) continue;
                $i++;
                $reference = $pmt->paid_invoice->reference;
                $mode = $pmt->paid_invoice->payment_mode;
                $pmt_tid = gen4tid('pmt-', $pmt->paid_invoice->tid);
                $account = $pmt->paid_invoice->account->holder;
                $amount = $pmt->paid_invoice->amount;
                $record = (object) array(
                    'id' => $i,
                    'date' => $pmt->paid_invoice->date,
                    'type' => 'payment',
                    'note' => '(' . $tid . ')' . ' ' . $pmt_tid . ' ' . ' reference: ' . $reference . ' mode: ' 
                        . ucfirst($mode) . ', account: ' . $account . ', amount: ' . numberFormat($amount),
                    'debit' => 0,
                    'credit' => $pmt->paid,
                    'invoice_id' => $invoice_id,
                    'payment_item_id' => $pmt->id
                );
                $payments->add($record);
            }    

            $withholdings = collect();
            foreach ($invoice->withholding_payments as $pmt) {
                $i++;
                $reference = @$pmt->withholding->reference;
                $certificate = @$pmt->withholding->certificate;
                $note = @$pmt->withholding->note;
                $date = @$pmt->withholding->date;
                $record = (object) array(
                    'id' => $i,
                    'date' => $date,
                    'type' => 'withholding',
                    'note' => "({$tid}) {$reference} - {$certificate} - {$note}",
                    'debit' => 0,
                    'credit' => $pmt->paid,
                    'invoice_id' => $invoice_id,
                    'withholding_item_id' => $pmt->id 
                );
                $withholdings->add($record);
            }  

            $creditnotes = collect();
            foreach ($invoice->creditnotes as $cnote) {
                $i++;
                $record = (object) array(
                    'id' => $i,
                    'date' => $cnote->date,
                    'type' => 'credit-note',
                    'note' => '(' . $tid . ')' . ' ' . $cnote->note,
                    'debit' => 0,
                    'credit' => $cnote->total,
                    'invoice_id' => $invoice_id,
                    'creditnote_id' => $cnote->id
                );
                $creditnotes->add($record);
            }   

            $debitnotes = collect();
            foreach ($invoice->debitnotes as $dnote) {
                $i++;
                $record = (object) array(
                    'id' => $i,
                    'date' => $dnote->date,
                    'type' => 'debit-note',
                    'note' => '(' . $tid . ')' . ' ' . $dnote->note,
                    'dedit' => $dnote->total,
                    'credit' => 0,
                    'invoice_id' => $invoice_id,
                    'debitnote_id' => $dnote->id
                );
                $debitnotes->add($record);
            }   

            $statement->add($inv_record);
            $statement = $statement->merge($payments);
            $statement = $statement->merge($creditnotes);
            $statement = $statement->merge($withholdings);
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
        DB::beginTransaction();

        if (isset($input['picture'])) $input['picture'] = $this->uploadPicture($input['picture']);
            
        $is_company = Customer::where('company', $input['company'])->exists();
        if ($is_company) throw ValidationException::withMessages(['Company already exists']);
        $email_exists = Customer::where('email', $input['email'])->whereNotNull('email')->exists();
        if ($email_exists) throw ValidationException::withMessages(['Duplicate email']);

        if (@$input['taxid']) {
            $taxid_exists = Customer::where('taxid', $input['taxid'])->whereNotNull('taxid')->exists();
            if ($taxid_exists) throw ValidationException::withMessages(['Duplicate Tax Pin']);
            $is_company = Company::where(['id' => auth()->user()->ins, 'taxid' => $input['taxid']])->whereNotNull('taxid')->exists();
            if ($is_company) throw ValidationException::withMessages(['Company Tax Pin not allowed']);
            if (strlen($input['taxid']) != 11) 
                throw ValidationException::withMessages(['Customer Tax Pin should contain 11 characters!']);
            if (!in_array($input['taxid'][0], ['P','A'])) 
                throw ValidationException::withMessages(['First character of Tax Pin must be letter "P" or "A"']);
            $pattern = "/^[0-9]+$/i";
            if (!preg_match($pattern, substr($input['taxid'],1,9))) 
                throw ValidationException::withMessages(['Character between first and last letters must be numbers']);
            $letter_pattern = "/^[a-zA-Z]+$/i";
            if (!preg_match($letter_pattern, $input['taxid'][-1])) 
                throw ValidationException::withMessages(['Last character of Tax Pin must be a letter!']);
        }
        
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

            // unrecognised sale
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

            // recognise sale as journal entry
            if ($result->sale_account_id) {
                $data = [
                    'tid' => Journal::where('ins', auth()->user()->ins)->max('tid') + 1,
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
                    if ($v == 1) {
                        $data['debit'] = $open_balance;
                    } else {
                        $balance_account = Account::where('system', 'retained_earning')->first(['id']);
                        $data['account_id'] = $balance_account->id;
                        $data['credit'] = $open_balance;
                    }   
                    JournalItem::create($data);
                }

                /** accounting */
                $data = array_replace($journal->toArray(), [
                    'open_balance' => $open_balance,
                    'account_id' => $debtor_account->id,
                ]);
                $this->post_transaction((object) $data);
            }
        }

        if ($result) {
            DB::commit();
            return $result;
        }
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
        DB::beginTransaction();

        if (isset($input['picture'])) {
            $this->removePicture($customer, 'picture');
            $input['picture'] = $this->uploadPicture($input['picture']);
        }
        if (empty($input['password'])) unset($input['password']);

        $is_company = Customer::where('id', '!=', $customer->id)->where('company', $input['company'])->exists();
        if ($is_company) throw ValidationException::withMessages(['Company already exists']);
        $email_exists = Customer::where('id', '!=', $customer->id)->where('email', $input['email'])->whereNotNull('email')->exists();
        if ($email_exists) throw ValidationException::withMessages(['Email already in use']);

        if (@$input['taxid']) {
            $taxid_exists = Customer::where('id', '!=', $customer->id)->where('taxid', $input['taxid'])->whereNotNull('taxid')->exists();
            if ($taxid_exists) throw ValidationException::withMessages(['Duplicate Tax Pin']);
            $is_company = Company::where(['id' => auth()->user()->ins, 'taxid' => $input['taxid']])->whereNotNull('taxid')->exists();
            if ($is_company) throw ValidationException::withMessages(['Company Tax Pin not allowed']);
            if (strlen($input['taxid']) != 11) 
                throw ValidationException::withMessages(['Customer Tax Pin should contain 11 characters']);
            if (!in_array($input['taxid'][0], ['P','A'])) 
                throw ValidationException::withMessages(['First character of Tax Pin must be letter "P" or "A"']);
            $pattern = "/^[0-9]+$/i";
            if (!preg_match($pattern, substr($input['taxid'],1,9))) 
                throw ValidationException::withMessages(['Character between first and last letters must be numbers']);
            $letter_pattern = "/^[a-zA-Z]+$/i";
            if (!preg_match($letter_pattern, $input['taxid'][-1])) 
                throw ValidationException::withMessages(['Last character of Tax Pin must be a letter!']);
        }
        
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
            if ($journal) {
                // remove previous transactions
                Transaction::where(['tr_ref' => $journal->id, 'note' => $journal->note])->delete();                 

                // update invoice
                $invoice = Invoice::where('notes', $journal->note)->first();
                if ($invoice) $invoice->update([
                    'notes' => $note, 
                    'subtotal' => $open_balance, 
                    'total' => $open_balance,
                    'account_id' => $customer->sale_account_id
                ]);   
                                   
                // recognised sale
                if ($customer->sale_account_id) {
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
                } else $journal->delete();
            } else {
                // unrecognised sale
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

                // recognise sale
                if ($customer->sale_account_id) {
                    $data = [
                        'tid' => Journal::where('ins', auth()->user()->ins)->max('tid') + 1,
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
                        if ($v == 1) {
                            $data['debit'] = $open_balance;
                        } else {
                            $balance_account = Account::where('system', 'retained_earning')->first(['id']);
                            $data['account_id'] = $balance_account->id;
                            $data['credit'] = $open_balance;
                        }   
                        JournalItem::create($data);
                    }

                    $data = array_replace($journal->toArray(), [
                        'open_balance' => $open_balance,
                        'account_id' => $debtor_account->id
                    ]);
                }
            }
            
            /**accounting */    
            if ($data) $this->post_transaction((object) $data);    
        }     

        if ($result) {
            DB::commit();
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.customers.update_error'));
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
            throw ValidationException::withMessages(['Customer has attached Tickets']);
        if ($customer->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.customers.delete_error'));
    }

    /**
     * Customer Opening Balance Transaction
     * @param object $result
     */
    public function post_transaction($result)
    {
        // debit Accounts Receivable (Debtor)
        $tr_category = Transactioncategory::where('code', 'genjr')->first(['id', 'code']);
        $dr_data = [
            'tid' => Transaction::where('ins', auth()->user()->ins)->max('tid') + 1,
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
        unset($dr_data['debit'], $dr_data['is_primary']);
        $account = Account::where('system', 'retained_earning')->first(['id']);
        $cr_data = array_replace($dr_data, [
            'account_id' => $account->id, 
            'credit' => $result->open_balance
        ]);
        Transaction::create($cr_data);
        aggregate_account_transactions();
    }

    /*
    * Upload logo image
    */
    public function uploadPicture($file)
    {
        $image = time() . $file->getClientOriginalName();
        $this->storage->put($this->customer_picture_path . $image, file_get_contents($file->getRealPath()));

        return $image;
    }

    /*
    * Remove logo or favicon icon
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
}
