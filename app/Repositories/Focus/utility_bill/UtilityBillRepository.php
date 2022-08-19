<?php

namespace App\Repositories\Focus\utility_bill;

use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\items\UtilityBillItem;
use App\Models\utility_bill\UtilityBill;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;

/**
 * Class ProductcategoryRepository.
 */
class UtilityBillRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = UtilityBill::class;

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

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return \App\Models\utility_bill\UtilityBill $utility_bill
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();
        // sanitize
        foreach ($input as $key => $val) {
            if (in_array($key, ['date', 'due_date'])) $input[$key] = date_for_database($val);
            if (in_array($key, ['subtotal', 'tax', 'total'])) $input[$key] = numberClean($val);
            if (in_array($key, ['row_subtotal', 'row_tax', 'row_total'])) {
                $input[$key] = array_map(function ($v) { 
                    return numberClean($v); 
                }, $val);
            }
        }

        $result = UtilityBill::create($input);

        $data_items = Arr::only($input, ['row_ref_id', 'row_note', 'row_qty', 'row_subtotal', 'row_tax', 'row_total']);
        $data_items = array_map(function ($v) use($result) {
            return [
                'bill_id' => $result->id,
                'ref_id' => $v['row_ref_id'],
                'note' => $v['row_note'],
                'qty' => $v['row_qty'],
                'subtotal' => $v['row_subtotal'],
                'tax' => $v['row_tax'],
                'total' => $v['row_total']
            ];
        }, modify_array($data_items));
        UtilityBillItem::insert($data_items);

        /**accounting */
        switch ($result->document_type) {
            case 'goods_receive_note': $this->goods_receive_note_transaction($result); break;
            case 'kra': $this->kra_transaction($result); break;
        }

        DB::commit();
        if ($result) return $result;

        throw new GeneralException('Error Creating Lead');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param \App\Models\utility_bill\UtilityBill $utility_bill
     * @param  array $input
     * @throws GeneralException
     * @return \App\Models\utility_bill\UtilityBill $utility_bill
     */
    public function update(UtilityBill $utility_bill, array $input)
    {
        dd($input);

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\utility_bill\UtilityBill $utility_bill
     * @throws GeneralException
     * @return bool
     */
    public function delete(UtilityBill $utility_bill)
    {     
        DB::beginTransaction();
        
        Transaction::where(['tr_type' => 'bill', 'note' => $utility_bill->note, 'tr_ref' => $utility_bill->id])->delete();
        $result = $utility_bill->delete();
        if ($result) {
            DB::commit(); 
            return true;
        }
                
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }

    /**
     * Create KRA Bill
     * @param array $input
     * @return UtilityBill $utility_bill
     */
    public function create_kra(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if ($key == 'reg_date') $input[$key] = date_for_database($val);
            if ($key == 'total') $input[$key] = numberClean($val);
            if ($key == 'amount') {
                $input[$key] = array_map(function ($n) { return numberClean($n); }, $val); 
            }                
        }
        $data = Arr::only($input, ['supplier_id', 'tid', 'reg_date', 'reg_no', 'note', 'total']);
        $data_items = Arr::only($input, ['payment_type', 'tax_type', 'tax_period', 'amount']);
        if (!$data_items) throw ValidationException::withMessages(['Payment Details line items required!']);

        $data = (object) $data;
        $bill_data = [
            'tid' => $data->tid,
            'supplier_id' => $data->supplier_id,
            'reference' => $data->reg_no,
            'document_type' => 'kra_bill',
            'date' => $data->reg_date,
            'due_date' => $data->reg_date,
            'subtotal' => $data->total,
            'total' => $data->total,
            'note' => $data->note,
        ];
        $result = UtilityBill::create($bill_data);

        // dd($data_items);
        $bill_items_data = array_map(function ($v) use($result) {
            return [
                'bill_id' => $result->id,
                'note' => implode(' - ', array($v['payment_type'], $v['tax_type'], $v['tax_period'])), 
                'qty' => 1,
                'subtotal' => $v['amount'],
                'total' => $v['amount'],
            ];
        }, modify_array($data_items));
        UtilityBillItem::insert($bill_items_data);

        /** accounting */
        $this->kra_transaction($result);

        DB::commit();
        if ($result) return $result;

        throw new GeneralException(trans('exceptions.backend.purchaseorders.create_error'));
    }    

    /**
     * KRA Account transactions
     * @param \App\Models\utility_bill\UtilityBill $utility_bill
     * @return void
     */
    public function kra_transaction($utility_bill)
    {
        // credit Accounts Payable (Creditors)
        $account = Account::where('system', 'payable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'bill')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $utility_bill->total,
            'tr_date' => $utility_bill->date,
            'due_date' => $utility_bill->due_date,
            'user_id' => $utility_bill->user_id,
            'note' => $utility_bill->note,
            'ins' => $utility_bill->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $utility_bill->id,
            'user_type' => 'supplier',
            'is_primary' => 1,
        ];
        Transaction::create($cr_data);

        // debit Expense account
        $account = Account::where('system', 'kra_tax')->first(['id']);
        unset($cr_data['credit'], $cr_data['is_primary']);
        $dr_data = array_replace($cr_data, [
            'account_id' => $account->id,
            'debit' => $utility_bill->total,
        ]);
        Transaction::create($dr_data); 
        aggregate_account_transactions();
    }

    /**
     * Post Goods Received Account transactions
     * @param \App\Models\utility_bill\UtilityBill $utility_bill
     * @return void
     */
    public function goods_receive_note_transaction($utility_bill)
    {
        // debit Uninvoiced Goods Received Note (liability)
        $account = Account::where('system', 'grn')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'bill')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $dr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'debit' => $utility_bill->total,
            'tr_date' => $utility_bill->date,
            'due_date' => $utility_bill->due_date,
            'user_id' => $utility_bill->user_id,
            'note' => $utility_bill->note,
            'ins' => $utility_bill->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $utility_bill->id,
            'user_type' => 'supplier',
            'is_primary' => 1
        ];
        Transaction::create($dr_data);

        // credit Accounts Payable (creditors)
        unset($dr_data['debit'], $dr_data['is_primary']);
        $account = Account::where('system', 'grn')->first(['id']);
        $cr_data = array_replace($dr_data, [
            'account_id' => $account->id,
            'credit' => $utility_bill->subtotal,
        ]);    
        Transaction::create($cr_data);

        // credit TAX
        if ($utility_bill->tax > 0) {
            $account = Account::where('system', 'tax')->first(['id']);
            $cr_data = array_replace($dr_data, [
                'account_id' => $account->id,
                'credit' => $utility_bill->tax,
            ]);
            Transaction::create($cr_data);
        }
        aggregate_account_transactions();
    }    
}