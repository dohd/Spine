<?php

namespace App\Repositories\Focus\utility_bill;

use App\Exceptions\GeneralException;
use App\Models\items\UtilityBillItem;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;


/**
 * Class UtilityBillRepository.
 */
class UtilityBillRepository extends BaseRepository
{
    use Accounting;

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
        return $q;
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
        foreach ($input as $key => $val) {
            if (in_array($key, ['date', 'due_date'])) $input[$key] = date_for_database($val);
            if (in_array($key, ['subtotal', 'tax', 'total'])) $input[$key] = numberClean($val);
            if (in_array($key, ['item_subtotal', 'item_tax', 'item_total'])) {
                $input[$key] = array_map(function ($v) { 
                    return numberClean($v); 
                }, $val);
            }
        }

        DB::beginTransaction();
        $result = UtilityBill::create($input);

        $data_items = Arr::only($input, ['item_ref_id', 'item_note', 'item_qty', 'item_subtotal', 'item_tax', 'item_total']);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['item_qty']);
        if (!$data_items) throw ValidationException::withMessages(['Cannot generate bill for empty line items!']);

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
        if ($result->document_type == 'goods_receive_note') {
            $this->post_grn_bill($result);
        }
            
        if ($result) {
            DB::commit();
            return $result;
        }

        DB::rollBack();
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
        foreach ($input as $key => $val) {
            if (in_array($key, ['date', 'due_date'])) $input[$key] = date_for_database($val);
            if (in_array($key, ['subtotal', 'tax', 'total'])) $input[$key] = numberClean($val);
            if (in_array($key, ['item_subtotal', 'item_tax', 'item_total'])) {
                $input[$key] = array_map(function ($v) { 
                    return numberClean($v); 
                }, $val);
            }
        }

        DB::beginTransaction();
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
            $utility_bill->transactions()->delete();
            $this->post_grn_bill($utility_bill);
        }
            
        if ($result) {
            DB::commit();
            return $result;
        }
        DB::rollBack();
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
        if ($utility_bill->payments()->exists()) {
            foreach ($utility_bill->payments as $key => $pmt_item) {
                $tids[] = @$pmt_item->bill_payment->tid ?: '';
            }
            throw ValidationException::withMessages(['Bill is linked to payments: (' . implode(', ', $tids) . ')']);
        }
        
        DB::beginTransaction();
        $utility_bill->transactions()->delete();
        $utility_bill->items()->delete();
        $result = $utility_bill->delete();
        if ($result) {
            DB::commit(); 
            return true;
        }
    }   
}