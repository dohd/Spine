<?php

namespace App\Repositories\Focus\supplier;

use DB;
use App\Models\supplier\Supplier;
use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\billpayment\Billpayment;
use App\Models\Company\Company;
use App\Models\items\JournalItem;
use App\Models\items\UtilityBillItem;
use App\Models\manualjournal\Journal;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Class SupplierRepository.
 */
class SupplierRepository extends BaseRepository
{
    /**
     *customer_picture_path .
     *
     * @var string
     */
    protected $person_picture_path;
    /**
     * Storage Class Object.
     *
     * @var \Illuminate\Support\Facades\Storage
     */
    protected $storage;
    /**
     * Associated Repository Model.
     */
    const MODEL = Supplier::class;
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->person_picture_path = 'img' . DIRECTORY_SEPARATOR . 'supplier' . DIRECTORY_SEPARATOR;
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
        return $this->query()->get();
    }

    public function getBillsForDataTable($supplier_id = 0)
    {
        return UtilityBill::where('supplier_id', request('supplier_id', $supplier_id))->get();
    }

    public function getTransactionsForDataTable($supplier_id = 0)
    {
        $params = ['supplier_id' => request('supplier_id', $supplier_id)];
        $supplier = Supplier::find(request('supplier_id'), ['id', 'open_balance_note']);

        $transactions = collect();
        // bills
        $bills = UtilityBill::where($params)->get();
        foreach ($bills as $i => $bill) {
            // skip opening balance bill
            if ($bill->tid == 0) continue;

            $tid = gen4tid('BILL-', $bill->tid);
            $transactions->add((object) [
                'id' => $i+1,
                'tr_date' => $bill->date,
                'tr_type' => 'bill',
                'note' => "({$tid}) " . $bill->note . " ({$bill->reference_type}-{$bill->reference})",
                'debit' => 0,
                'credit' => $bill->total,
            ]);
        }
        // bill payments
        $bill_payments = Billpayment::where($params)->get();
        $j = $transactions->last()? $transactions->last()->id : 0;
        foreach ($bill_payments as $pmt) {
            $j++;
            $tid = gen4tid('PMT-', $pmt->tid);
            $transactions->add((object) [
                'id' => $j,
                'tr_date' => $pmt->date,
                'tr_type' => 'pmt',
                'note' => "({$tid}) " . $pmt->note . " ({$pmt->payment_mode}-{$pmt->reference})",
                'debit' => $pmt->amount,
                'credit' => 0,
            ]);
        }
        // opening balance
        $note = "%{$supplier->id}-supplier Account Opening Balance {$supplier->open_balance_note}%";
        $open_balance_tr = Transaction::where('tr_type', 'genjr')->where('credit', '>', 0)
            ->where('note', 'LIKE', $note)->first();
        if ($open_balance_tr) {
            $i = $transactions->last()? $transactions->last()->id : 0;
            $transactions->add((object) [
                'id' => $i+1,
                'tr_date' => $open_balance_tr->tr_date,
                'tr_type' => $open_balance_tr->tr_type,
                'note' => $open_balance_tr->note,
                'debit' => $open_balance_tr->debit,
                'credit' => $open_balance_tr->credit,
            ]);
        }

        // add balance brought foward logic on datefilter
        // 

        return $transactions;


        /**
        $q = Transaction::whereHas('account', function ($q) { 
            $q->where('system', 'payable');  
        })->where(function ($q) use($params) {
            $q->where('tr_type', 'pmt')->where(function ($q) use($params) {
                $q->whereHas('bill_payment', function ($q) use($params) {
                    $q->where($params);
                });
            })
            ->orwhere('tr_type', 'bill')->where(function ($q) use($params) {
                $q->where('credit', '>', 0)->where(function  ($q) use($params) {
                    $q->whereHas('direct_purchase_bill', function ($p) use($params) {
                        $p->where($params);
                    })
                    ->orwhereHas('grn_bill', function ($q) use($params) {
                        $q->where($params);
                    })
                    ->orWhereHas('grn_invoice_bill', function ($q) use($params) {
                        $q->where($params);
                    });   
                });  
            });                
        })->orwhere(function ($q) use($supplier) {
            // opening balance
            $note = "%{$supplier->id}-supplier Account Opening Balance {$supplier->open_balance_note}%";
            $q->where('tr_type', 'genjr')->where('credit', '>', 0)->where('note', 'LIKE', $note);
        });

        // on date filter
        if (request('start_date') && request('is_transaction')) {
            $from = date_for_database(request('start_date'));
            $tr_ids = $q->pluck('id')->toArray();
            
            $params = ['id', 'tr_date', 'tr_type', 'note', 'debit', 'credit'];
            $transactions = Transaction::whereIn('id', $tr_ids)->whereBetween('tr_date', [$from, date('Y-m-d')])->get($params);
            // compute balance brought foward as of start date
            $bf_transactions = Transaction::whereIn('id', $tr_ids)->where('tr_date', '<', $from)->get($params);
            $credit_balance = $bf_transactions->sum('credit') - $bf_transactions->sum('debit');
            if ($credit_balance) {
                $record = (object) array(
                    'id' => 0,
                    'tr_date' => date('Y-m-d', strtotime($from . ' - 1 day')),
                    'tr_type' => 'balance',
                    'note' => '** Balance Brought Foward ** ',
                    'debit' => $credit_balance < 0 ? ($credit_balance * -1) : 0,
                    'credit' => $credit_balance > 0 ? $credit_balance : 0,
                );
                // merge brought foward balance with the rest of the transactions
                $transactions = collect([$record])->merge($transactions);
            }

            return $transactions;
        }

        return $q->get(); 
        **/
    }

    public function getStatementForDataTable($supplier_id = 0)
    {
        $q = UtilityBill::where('supplier_id', request('supplier_id', $supplier_id))->with('payments');
        $bills = $q->get();

        $i = 0;
        $statement = collect();
        foreach ($bills as $bill) {
            $i++;
            $bill_id = $bill->id;
            $tid = gen4tid('BILL-', $bill->tid);
            $bill_record = (object) array(
                'id' => $i,
                'date' => $bill->date,
                'type' => 'bill',
                'note' => "({$tid}) {$bill->note}",
                'debit' => 0,
                'credit' => $bill->total,
                'bill_id' => $bill_id
            );

            $payments = collect();
            foreach ($bill->payments as $pmt) {
                if (!$pmt->bill_payment) continue;
                $i++;
                $reference = $pmt->bill_payment->reference;
                $pmt_tid = gen4tid('PMT-', $pmt->bill_payment->tid);
                $account = $pmt->bill_payment->account? $pmt->bill_payment->account->holder : '';
                $amount = numberFormat($pmt->bill_payment->amount);
                $payment_mode = ucfirst($pmt->bill_payment->payment_mode);
                $record = (object) array(
                    'id' => $i,
                    'date' => $pmt->bill->date,
                    'type' => 'payment',
                    'note' => "({$tid}) {$pmt_tid} reference: {$reference} mode: {$payment_mode} account: {$account} amount: {$amount}",
                    'debit' => $pmt->paid,
                    'credit' => 0,
                    'bill_id' => $bill_id,
                    'payment_item_id' => $pmt->id
                );
                $payments->add($record);
            }   
            $statement->add($bill_record);
            $statement = $statement->merge($payments);
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
        $data = $input['data'];
        if (!empty($data['picture'])) $data['picture'] = $this->uploadPicture($data['picture']);

        if (@$data['taxid']) {
            $taxid_exists = Supplier::where('taxid', $data['taxid'])->whereNotNull('taxid')->exists();
            if ($taxid_exists) throw ValidationException::withMessages(['Duplicate Tax Pin']);
            $is_company = Company::where(['id' => auth()->user()->ins, 'taxid' => $data['taxid']])->exists();
            if ($is_company) throw ValidationException::withMessages(['Company Tax Pin not allowed!']);
            if (strlen($data['taxid']) != 11) 
                throw ValidationException::withMessages(['Supplier Tax Pin should contain 11 characters']);
            if (!in_array($data['taxid'][0], ['P','A'])) 
                throw ValidationException::withMessages(['First character of Tax Pin must be letter "P" or "A"']);
            $pattern = "/^[0-9]+$/i";
            if (!preg_match($pattern, substr($data['taxid'],1,9))) 
                throw ValidationException::withMessages(['Characters between 2nd and 10th letters must be numbers']);
            $letter_pattern = "/^[a-zA-Z]+$/i";
            if (!preg_match($letter_pattern, $data['taxid'][-1])) 
                throw ValidationException::withMessages(['Last character of Tax Pin must be a letter']);
        }

        DB::beginTransaction();

        $account_data = $input['account_data'];
        $data['open_balance'] = numberClean($account_data['open_balance']);
        $data['open_balance_date'] = date_for_database($account_data['open_balance_date']);
        $result = Supplier::create($data);

        $open_balance = $result->open_balance;
        $open_balance_date = $result->open_balance_date;
        if ($open_balance > 0) {
            $note = $result->id . '-supplier Account Opening Balance' . $result->open_balance_note;
            $user_id = auth()->user()->id;

            // unrecognised expense bill
            $bill_data = [
                'supplier_id' => $result->id,
                'document_type' => 'opening_balance',
                'date' => $open_balance_date,
                'due_date' => $open_balance_date,
                'subtotal' => $open_balance,
                'total' => $open_balance,
                'note' => $note,
                'user_id' => $user_id,
                'ins' => auth()->user()->ins,                
            ];
            $bill = UtilityBill::create($bill_data);

            UtilityBillItem::create([
                'bill_id' => $bill->id,
                'note' => $note,
                'qty' => 1,
                'subtotal' => $bill->subtotal,
                'total' => $bill->total
            ]);

            // recognise expense as journal entry
            if ($result->expense_account_id) {
                $data = [
                    'tid' => Journal::where('ins', auth()->user()->ins)->max('tid') + 1,
                    'date' => $open_balance_date,
                    'note' => $note,
                    'debit_ttl' => $open_balance,
                    'credit_ttl' => $open_balance,
                    'ins' => $result->ins,
                    'user_id' => $user_id
                ];
                $journal = Journal::create($data);
    
                $creditor_account = Account::where('system', 'payable')->first(['id']);
                foreach ([1, 2] as $v) {
                    $data = [
                        'journal_id' => $journal->id,
                        'account_id' => $creditor_account->id,
                    ];
                    if ($v == 1) $data['credit'] = $open_balance;
                    else {
                        $balance_account = Account::where('system', 'retained_earning')->first(['id']);
                        $data['account_id'] = $balance_account->id;
                        $data['debit'] = $open_balance;
                    }
                    JournalItem::create($data);
                }

                /**accounting */
                $data = array_replace($journal->toArray(), [
                    'open_balance' => $open_balance,
                    'account_id' => $creditor_account->id
                ]);
                $this->post_transaction((object) $data);
            } 
        }

        DB::commit();
        if ($result) return $result;
    }


    /**
     * For updating the respective Model in storage
     *
     * @param Supplier $supplier
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($supplier, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        if (!empty($input['picture'])) {
            $this->removePicture($supplier, 'picture');
            $data['picture'] = $this->uploadPicture($data['picture']);
        }

        if (@$data['taxid']) {
            $taxid_exists = Supplier::where('id', '!=', $supplier->id)->where('taxid', $data['taxid'])->whereNotNull('taxid')->exists();
            if ($taxid_exists) throw ValidationException::withMessages(['Duplicate Tax Pin']);
            $is_company = Company::where(['id' => auth()->user()->ins, 'taxid' => $data['taxid']])->exists();
            if ($is_company) throw ValidationException::withMessages(['Company Tax Pin is not allowed!']);
            if (strlen($data['taxid']) != 11) 
                throw ValidationException::withMessages(['Supplier Tax Pin should contain 11 characters!']);
            if (!in_array($data['taxid'][0], ['P','A'])) 
                throw ValidationException::withMessages(['Initial character of Tax Pin must be letter "P" or "A"']);
            $pattern = "/^[0-9]+$/i";
            if (!preg_match($pattern, substr($data['taxid'],1,9))) 
                throw ValidationException::withMessages(['Character between first and last letters must be numbers']);
            $letter_pattern = "/^[a-zA-Z]+$/i";
            if (!preg_match($letter_pattern, $data['taxid'][-1])) 
                throw ValidationException::withMessages(['Last character of Tax Pin must be a letter!']);
        }

        $account_data = $input['account_data'];
        $data = array_replace($data, [
            'open_balance' => numberClean($account_data['open_balance']),
            'open_balance_date' => date_for_database($account_data['open_balance_date']),
            'open_balance_note' => $account_data['open_balance_note'],
            'expense_account_id' => $account_data['expense_account_id'],
        ]);
        $result = $supplier->update($data);

        $open_balance = $supplier->open_balance;
        $open_balance_date = $supplier->open_balance_date;
        $journal_data = [];
        if ($open_balance > 0) {
            $user_id = auth()->user()->id;
            $note = $supplier->id .  '-supplier Account Opening Balance ' . $supplier->open_balance_note;
            $journal = Journal::where('note', 'LIKE', '%' . $supplier->id .  '-supplier Account Opening Balance ' . '%')->first();
            if ($journal) {
                // remove previous transactions
                Transaction::where(['tr_ref' => $journal->id, 'note' => $journal->note])->delete();

                // update bill
                $bill = UtilityBill::where('note', $journal->note)->first();
                if ($bill) {
                    $bill->update([
                        'date' => $open_balance_date,
                        'due_date' => $open_balance_date,
                        'subtotal' => $open_balance,
                        'total' => $open_balance,
                        'note' => $note,
                    ]);   
                    if ($bill->item) {
                        $bill->item->update([
                            'subtotal' => $open_balance,
                            'total' => $open_balance,
                            'note' => $note,
                        ]);
                    }
                }

                // recognise expense
                if ($supplier->expense_account_id) {
                    $journal->update([
                        'note' => $note,
                        'date' => $open_balance_date,
                        'debit_ttl' => $open_balance,
                        'credit_ttl' => $open_balance,
                    ]);

                    $account = Account::where('system', 'payable')->first(['id']);
                    $journal_data = array_replace($journal->toArray(), [
                        'open_balance' => $open_balance,
                        'account_id' => $account->id
                    ]);

                    foreach ($journal->items as $item) {
                        if ($item->debit > 0) $item->update(['debit' => $open_balance]);
                        elseif ($item->credit > 0) $item->update(['credit' => $open_balance]);
                    }
                } 
                else $journal->delete();
            } else {
                // unrecognised expense
                $bill_data = [
                    'supplier_id' => $supplier->id,
                    'document_type' => 'opening_balance',
                    'date' => $open_balance_date,
                    'due_date' => $open_balance_date,
                    'subtotal' => $open_balance,
                    'total' => $open_balance,
                    'note' => $note,
                    'user_id' => $user_id,
                    'ins' => $supplier->ins,                
                ];
                $bill = UtilityBill::create($bill_data);
    
                UtilityBillItem::create([
                    'bill_id' => $bill->id,
                    'note' => $note,
                    'qty' => 1,
                    'subtotal' => $bill->subtotal,
                    'total' => $bill->total
                ]);

                // recognise expense as a journal entry
                if ($supplier->expense_account_id) {
                    $data = [
                        'tid' => Journal::where('ins', auth()->user()->ins)->max('tid')+1,
                        'date' => $open_balance_date,
                        'note' => $note,
                        'debit_ttl' => $open_balance,
                        'credit_ttl' => $open_balance,
                        'ins' => $supplier->ins,
                        'user_id' => $user_id,
                    ];
                    $journal = Journal::create($data);

                    $creditor_account = Account::where('system', 'payable')->first(['id']);
                    foreach ([1, 2] as $v) {
                        $data = [
                            'journal_id' => $journal->id,
                            'account_id' => $creditor_account->id,
                        ];
                        if ($v == 1) $data['credit'] = $open_balance;
                        else {
                            $balance_account = Account::where('system', 'retained_earning')->first(['id']);
                            $data['account_id'] = $balance_account->id;
                            $data['debit'] = $open_balance;
                        }
                        JournalItem::create($data);
                    }

                    $journal_data = array_replace($journal->toArray(), [
                        'open_balance' => $open_balance,
                        'account_id' => $creditor_account->id
                    ]);
                }               
            }
            /**accounting */
            if ($journal_data) $this->post_transaction((object) $journal_data);
        }

        if ($result) {
            DB::commit();
            return $result;
        }

        throw new GeneralException(trans('exceptions.backend.suppliers.update_error'));
    }

    /**
     * Supplier Opening Balance Transaction
     * @param object $result
     */
    public function post_transaction($result)
    {   
        // credit Accounts Payable (Creditor)
        $tr_category = Transactioncategory::where('code', 'genjr')->first(['id', 'code']);
        $cr_data = [
            'tid' => Transaction::where('ins', auth()->user()->ins)->max('tid') + 1,
            'account_id' => $result->account_id,
            'trans_category_id' => $tr_category->id,
            'tr_date' => $result->date,
            'due_date' => $result->date,
            'user_id' => $result->user_id,
            'note' => $result->note,
            'credit' => $result->open_balance,
            'ins' => $result->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $result->id,
            'user_type' => 'supplier',
            'is_primary' => 1,
        ];
        Transaction::create($cr_data);

        // debit Retained Earning (Equity)
        unset($cr_data['credit'], $cr_data['is_primary']);
        $account = Account::where('system', 'retained_earning')->first(['id']);
        $dr_data = array_replace($cr_data, ['account_id' => $account->id, 'debit' => $result->open_balance]);
        Transaction::create($dr_data);

        aggregate_account_transactions();
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Supplier $supplier
     * @return bool
     * @throws GeneralException
     */
    public function delete($supplier)
    {
        if ($supplier->bills->count())
            throw ValidationException::withMessages(['Supplier has attached Bill!']);
        if ($supplier->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.suppliers.delete_error'));
    }

    /*
    * Upload logo image
    */
    public function uploadPicture($logo)
    {
        $image_name = $this->person_picture_path . time() . $logo->getClientOriginalName();
        $this->storage->put($image_name, file_get_contents($logo->getRealPath()));

        return $image_name;
    }

    /*
    * remove logo or favicon icon
    */
    public function removePicture(Supplier $supplier, $type)
    {
        if ($supplier->$type) {
            $image = $this->person_picture_path . $supplier->type;
            if ($this->storage->exists($image)) $this->storage->delete($image);
        }
        if ($supplier->update([$type => null])) return true;

        throw new GeneralException(trans('exceptions.backend.settings.update_error'));
    }
}
