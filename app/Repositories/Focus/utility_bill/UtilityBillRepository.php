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
use Illuminate\Validation\ValidationException;
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;


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
        $q = $this->query();

        // date filter
        if (request('start_date') && request('end_date')) {
            $q->whereBetween('date', [
                date_for_database(request('start_date')), 
                date_for_database(request('end_date'))
            ]);
        }

        // supplier and status filter
        $q->when(request('supplier_id'), function ($q) {
            $q->where('supplier_id', request('supplier_id'));
        })->when(request('bill_type'), function ($q) {
            // bill type
            $type = request('bill_type');
            switch ($type) {
                case 'direct_purchase':
                    $q->where('document_type', $type);
                    break; 
                case 'goods_receive_note':
                    $q->where('document_type', $type);
                    break;
                case 'opening_balance':
                    $q->where('document_type', $type);
                    break;
                case 'kra_bill':
                    $q->where('document_type', $type);
                    break;
            }         
        })->when(request('bill_status'), function ($q) {
            // bill due status
            switch (request('bill_status')) {
                case 'not yet due': 
                    $q->where('due_date', '>', date('Y-m-d'));
                    break;
                case 'due':  
                    $q->where('due_date', '<=', date('Y-m-d'));
                    break; 
            }
        })->when(request('payment_status'), function ($q) {
            // payment status
            switch (request('payment_status')) {
                case 'unpaid':
                    $q->where('amount_paid', 0);
                    break; 
                case 'partially paid':
                    $q->whereColumn('amount_paid', '<', 'total')->where('amount_paid', '>', 0);
                    break;
                case 'paid':
                    $q->whereColumn('amount_paid', '>=', 'total');
                    break;
            }         
        });

        return $q->get();
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

        try {
            foreach ($input as $key => $val) {
                if (in_array($key, ['date', 'due_date'])) $input[$key] = date_for_database($val);
                if (in_array($key, ['subtotal', 'tax', 'total'])) $input[$key] = numberClean($val);
                if (in_array($key, ['item_subtotal', 'item_tax', 'item_total'])) {
                    $input[$key] = array_map(function ($v) { 
                        return numberClean($v); 
                    }, $val);
                }
            }
            $result = UtilityBill::create($input);
    
            $data_items = Arr::only($input, ['item_ref_id', 'item_note', 'item_qty', 'item_subtotal', 'item_tax', 'item_total']);
            $data_items = modify_array($data_items);
            $data_items = array_map(function ($v) use($result) {
                return [
                    'bill_id' => $result->id,
                    'ref_id' => $v['item_ref_id'],
                    'note' => $v['item_note'],
                    'qty' => $v['item_qty'],
                    'subtotal' => $v['item_subtotal'],
                    'tax' => $v['item_tax'],
                    'total' => $v['item_total']
                ];
            }, $data_items);
            UtilityBillItem::insert($data_items);
    
            /**accounting */
            if ($result->document_type == 'goods_receive_note') 
                $this->goods_receive_note_transaction($result);
    
            if ($result) {
                DB::commit();
                return $result;
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new GeneralException('Error Creating Bill');
        }
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
        // dd($input);
        DB::beginTransaction();

        try {
            foreach ($input as $key => $val) {
                if (in_array($key, ['date', 'due_date'])) $input[$key] = date_for_database($val);
                if (in_array($key, ['subtotal', 'tax', 'total'])) $input[$key] = numberClean($val);
                if (in_array($key, ['item_subtotal', 'item_tax', 'item_total'])) {
                    $input[$key] = array_map(function ($v) { 
                        return numberClean($v); 
                    }, $val);
                }
            }
            $prev_note = $utility_bill->note;
            $result = $utility_bill->update($input);

            $data_items = Arr::only($input, ['id', 'item_ref_id', 'item_note', 'item_qty', 'item_subtotal', 'item_tax', 'item_total']);
            $data_items = array_map(function ($v) {
                return [
                    'id' => $v['id'],
                    'ref_id' => $v['item_ref_id'],
                    'note' => $v['item_note'],
                    'qty' => $v['item_qty'],
                    'subtotal' => $v['item_subtotal'],
                    'tax' => $v['item_tax'],
                    'total' => $v['item_total']
                ];
            }, modify_array($data_items));
            Batch::update(new UtilityBillItem, $data_items, 'id');

            /**accounting */
            if ($utility_bill->document_type == 'goods_receive_note') {
                Transaction::where(['tr_type' => 'bill', 'note' => $prev_note, 'tr_ref' => $utility_bill->id])->delete();
                $this->goods_receive_note_transaction($utility_bill);
            }
                
            if ($result) {
                DB::commit();
                return $result;
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
        }
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
        if ($utility_bill->delete()) {
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

        try {
            foreach ($input as $key => $val) {
                if ($key == 'reg_date') $input[$key] = date_for_database($val);
                if ($key == 'total') $input[$key] = numberClean($val);
                if ($key == 'amount') {
                    $input[$key] = array_map(function ($n) { 
                        return numberClean($n); 
                    }, $val); 
                }                
            }
            $data = (object) Arr::only($input, ['supplier_id', 'tid', 'reg_date', 'reg_no', 'note', 'total']);
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

            $data_items = Arr::only($input, ['payment_type', 'tax_type', 'tax_period', 'amount']);
            if (!$data_items) throw ValidationException::withMessages(['Payment Details line items required!']);
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

            if ($result) {
                DB::commit();
                return $result;
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new GeneralException(trans('exceptions.backend.purchaseorders.create_error'));
        }
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
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
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
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
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