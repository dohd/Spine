<?php

namespace App\Repositories\Focus\bill;

use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Repositories\BaseRepository;
use App\Models\bill\Bill;
use App\Models\bill\Paidbill;
use App\Models\billitem\BillItem;
use App\Models\items\PaidbillItem;
use App\Models\supplier\Supplier;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;

/**
 * Class PurchaseorderRepository.
 */
class BillRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Bill::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = Bill::query();

        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $bill = $input['bill'];
        foreach ($bill as $key => $val) {
            if (in_array($key, ['date', 'due_date'], 1)) 
                $bill[$key] = date_for_database($val);
            if (in_array($key, ['amount_ttl', 'deposit_ttl', 'deposit'], 1)) 
                $bill[$key] = numberClean($val);
        }
        $result = Paidbill::create($bill);
        $result->note = $result->doc_ref_type . ' - '. $result->doc_ref . ' ' . $result->note;

        $bill_items = $input['bill_items'];
        foreach ($bill_items as $k => $item) {
            $item['paidbill_id'] = $result->id;
            $item['paid'] = numberClean($item['paid']);
            $bill_items[$k] = $item;
        }
        PaidbillItem::insert($bill_items);

        // update paid amount in bills
        $bill_ids = $result->items()->pluck('bill_id')->toArray();
        $paid_bills = PaidbillItem::whereIn('bill_id', $bill_ids)
            ->select(DB::raw('bill_id as id, SUM(paid) as amountpaid'))
            ->groupBy('bill_id')
            ->get()->toArray();
        Batch::update(new Bill, $paid_bills, 'id');
        
        // update payment status in bills
        foreach ($result->items as $item) {            
            $bill = $item->bill;
            if ($bill->grandttl == $bill->amountpaid) $bill->update(['status' => 'paid']);  
            elseif ($bill->amountpaid == 0) $bill->update(['status' => 'pending']);
            elseif ($bill->grandttl > $bill->amountpaid) $bill->update(['status' => 'partial']);
        }

        /** accounts */
        $this->post_transaction($result);

        DB::commit();
        if ($result) return true;

        throw new GeneralException(trans('exceptions.backend.purchaseorders.create_error'));
    }

    // bill payment transaction 
    public function post_transaction($bill)
    {
        // credit Bank (Income)
        $tr_category = Transactioncategory::where('code', 'pmt')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $bill['account_id'],
            'trans_category_id' => $tr_category->id,
            'credit' => $bill['deposit_ttl'],
            'tr_date' => $bill['date'],
            'due_date' => $bill['due_date'],
            'user_id' => $bill['user_id'],
            'ins' => $bill['ins'],
            'tr_type' => $tr_category->code,
            'tr_ref' => $bill['id'],
            'user_type' => 'supplier',
            'is_primary' => 1,
            'note' => $bill['note'],
        ];
        Transaction::create($cr_data);

        // debit Accounts Payable (Creditor)
        unset($cr_data['credit'], $cr_data['is_primary']);
        $account = Account::where('system', 'payable')->first(['id']);
        $dr_data = array_replace($cr_data, [
            'account_id' => $account->id,
            'debit' => $bill['deposit_ttl'],
        ]);
        Transaction::create($dr_data);
        aggregate_account_transactions();    
    }

    /**
     * Create KRA Bill
     */
    public function create_kra(array $input)
    {
        // dd($input);
        foreach ($input as $key => $val) {
            if ($key == 'reg_date') $input[$key] = date_for_database($val);
            if ($key == 'total') $input[$key] = numberClean($val);
            if ($key == 'amount') 
                $input[$key] = array_map(function ($n) { return numberClean($n); }, $val); 
        }
        $data = Arr::only($input, ['supplier_id', 'tid', 'reg_date', 'reg_no', 'note', 'total']);
        $data_items = Arr::only($input, ['payment_type', 'tax_type', 'tax_period', 'amount']);
        if (!$data_items) throw ValidationException::withMessages(['Payment Details line items required!']);

        DB::beginTransaction();

        $data = (object) $data;
        $data->amount = $data->total;
        $supplier = Supplier::find($data->supplier_id);
        $bill_data = [
            'date' => $data->reg_date,
            'due_date' => $data->reg_date,
            'supplier_type' => 'supplier',
            'supplier_id' => $supplier->id,
            'supplier_taxid' => $supplier->taxid,
            'expense_subttl' => $data->amount,
            'expense_grandttl' => $data->amount,
            'paidttl' => $data->amount,
            'grandttl' => $data->amount,
            'note' => $data->note,
            'ins' => auth()->user()->ins,
            'user_id' => auth()->user()->id,
            'doc_ref_type' => 'Receipt',
            'doc_ref' => $data->reg_no,
            'suppliername' => $supplier->name,
            'tid' => $data->tid,
        ];
        unset($data->total);
        $result = Bill::create($bill_data);

        $bill_items_data = array_map(function ($v) use($result) {
            return [
                'bill_id' => $result->id,
                'description' => implode(' - ', array($v['payment_type'], $v['tax_type'], $v['tax_period'])),
                'qty' => 1,
                'rate' => $v['amount'],
                'amount' => $v['amount'],
                'ins' => $result->ins,
                'user_id' => $result->user_id,
                'type' => 'Expense',
            ];
        }, modify_array($data_items));
        BillItem::create($bill_items_data);

        /** accounting */
        $this->post_kra_transaction($result);

        DB::commit();
        if ($result) return $result;

        throw new GeneralException(trans('exceptions.backend.purchaseorders.create_error'));
    }

    // create kra bill transaction
    public function post_kra_transaction($bill) 
    {
        // credit Accounts Payable (Creditors)
        $account = Account::where('system', 'payable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'bill')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $bill->grandttl,
            'tr_date' => $bill->date,
            'due_date' => $bill->due_date,
            'user_id' => $bill->user_id,
            'note' => $bill->note,
            'ins' => $bill->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $bill->id,
            'user_type' => 'supplier',
            'is_primary' => 1,
        ];
        Transaction::create($cr_data);

        // debit Expense account
        $account = Account::where('system', 'kra_tax')->first(['id']);
        unset($cr_data['credit'], $cr_data['is_primary']);
        $item = $bill->items->first();
        $dr_data = array_replace($cr_data, [
            'account_id' => $account->id,
            'debit' => $item->amount,
        ]);
        Transaction::create($dr_data); 
        aggregate_account_transactions();
    }    
}
