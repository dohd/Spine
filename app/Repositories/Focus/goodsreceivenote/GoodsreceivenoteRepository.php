<?php

namespace App\Repositories\Focus\goodsreceivenote;

use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\items\GoodsreceivenoteItem;
use App\Models\items\UtilityBillItem;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;

/**
 * Class ProductcategoryRepository.
 */
class GoodsreceivenoteRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Goodsreceivenote::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('supplier_id'), function ($q) {
            $q->where('supplier_id', request('supplier_id'));
        });
        
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();
        // sanitize
        foreach ($input as $key => $val) {
            if ($key == 'date') $input[$key] = date_for_database($val);
            if ($key == 'tax_rate') $input[$key] = numberClean($val);
            if (in_array($key, ['qty', 'rate'])) $input[$key] = array_map(function ($v) { 
                return numberClean($v); 
            }, $val);
        }

        $result = Goodsreceivenote::create($input);

        $data_items = Arr::only($input, ['qty', 'rate', 'purchaseorder_item_id', 'item_id']);
        $data_items = array_filter(modify_array($data_items), function ($v) {
            return $v['qty'] > 0;
        });
        foreach ($data_items as $i => $item) {
            $data_items[$i] = array_replace($item, [
                'goods_receive_note_id' => $result->id,
                'tax_rate' => $result->tax_rate
            ]);
        }
        GoodsreceivenoteItem::insert($data_items);
        
        // increase stock qty
        foreach ($result->items as $item) {
            $po_item = $item->purchaseorder_item;
            $po_item->increment('qty_received', $item->qty);

            // apply unit conversion
            $prod_variation = $po_item->productvariation;
            $units = $prod_variation->product->units;
            foreach ($units as $unit) {
                if ($unit->code == $po_item['uom']) {
                    if ($unit->unit_type == 'base') {
                        $prod_variation->increment('qty', $po_item['qty']);
                    } else {
                        $converted_qty = $po_item['qty'] * $unit->base_ratio;
                        $prod_variation->increment('qty', $converted_qty);
                    }
                }
            }   
        }

        // update purchase order status
        $received_goods_qty = $result->items->sum('qty');
        $order_goods_qty = $result->purchaseorder->items->sum('qty');
        if ($received_goods_qty == 0) $result->purchaseorder->update(['status' => 'pending']);
        elseif (round($received_goods_qty) < round($order_goods_qty)) $result->purchaseorder->update(['status' => 'partial']);
        else $result->purchaseorder->update(['status' => 'complete']);

        /**accounting */
        if ($result->invoice_no) $this->generate_bill($result);
        else $this->post_transaction($result);
        
        DB::commit();
        if ($result) return $result;

        throw new GeneralException('Error Creating Lead');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     * @param  array $input
     * @throws GeneralException
     * @return \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     */
    public function update(Goodsreceivenote $goodsreceivenote, array $input)
    {
        dd($input);

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     * @throws GeneralException
     * @return bool
     */
    public function delete(Goodsreceivenote $goodsreceivenote)
    {     
        DB::beginTransaction();
        
        // reduce stock qty
        foreach ($goodsreceivenote->items as $item) {
            $po_item = $item->purchaseorder_item;
            $po_item->decrement('qty_received', $item->qty);
            // stock subtotal amount
            $goodsreceivenote->subtotal += ($item->qty * $po_item->rate / $po_item->qty);

            // apply unit conversion
            $prod_variation = $po_item->productvariation;
            $units = $prod_variation->product->units;
            foreach ($units as $unit) {
                if ($unit->code == $po_item['uom']) {
                    if ($unit->unit_type == 'base') {
                        $prod_variation->decrement('qty', $po_item['qty']);
                    } else {
                        $converted_qty = $po_item['qty'] * $unit->base_ratio;
                        $prod_variation->decrement('qty', $converted_qty);
                    }
                }
            }   
        }

        $current = $goodsreceivenote;
        $goodsreceivenotes = GoodsreceivenoteItem::whereHas('goodsreceivenote', function ($q) use($current) {
            $q->where('purchaseorder_id', $current->purchaseorder_id)->whereNotIn('id', $current->id);
        });
        $received_goods_qty = 0;
        foreach ($goodsreceivenotes as $row) {
            $received_goods_qty += $row->items->sum('qty');
        }

        // update purchase order status
        $purchaseorder = $goodsreceivenote->purchaseorder;
        $order_goods_qty = $purchaseorder->items->sum('qty');
        if ($received_goods_qty == 0) $purchaseorder->update(['status' => 'pending']);
        elseif (round($order_goods_qty) > round($received_goods_qty)) $purchaseorder->update(['status' => 'partial']);
        else $purchaseorder->update(['status' => 'complete']);

        $result = $goodsreceivenote->delete();
        if ($result) {
            DB::commit(); 
            return true;
        }
                
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }


    /**
     * Generate Bill For Goods Receive with invoice
     * @param Goodsreceivenote $grn
     * @return void
     */
    public function generate_bill($grn)
    {
        $tid = UtilityBill::max('tid') + 1;
        $bill_data = [
            'tid' => $tid,
            'supplier_id' => $grn->supplier_id,
            'reference' => $grn->invoice_no,
            'document_type' => 'goods_receive_note',
            'ref_id' => $grn->id,
            'date' => $grn->date,
            'due_date' => $grn->date,
            'subtotal' => $grn->subtotal,
            'tax' => $grn->tax,
            'total' => $grn->total,
            'note' => $grn->note,
        ];
        $bill = UtilityBill::create($bill_data);

        $bill_item_data = [
            'bill_id' => $bill->id,
            'note' => $bill->note,
            'qty' => 1,
            'subtotal' => $grn->subtotal,
            'tax' => $grn->tax,
            'total' => $grn->total,
        ];
        UtilityBillItem::create($bill_item_data);

        /**accounting */
        $this->invoiced_grn_transaction($bill);
    }

    public function invoiced_grn_transaction($utility_bill)
    {
        // debit Inventory Account (liability)
        $account = Account::where('system', 'stock')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'bill')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $dr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'debit' => $utility_bill->subtotal,
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

        // debit TAX
        if ($utility_bill->tax > 0) {
            $account = Account::where('system', 'tax')->first(['id']);
            $cr_data = array_replace($dr_data, [
                'account_id' => $account->id,
                'debit' => $utility_bill->tax,
            ]);
            Transaction::create($cr_data);
        }

        // credit Accounts Payable (creditors)
        unset($dr_data['debit'], $dr_data['is_primary']);
        $account = Account::where('system', 'grn')->first(['id']);
        $cr_data = array_replace($dr_data, [
            'account_id' => $account->id,
            'credit' => $utility_bill->total,
        ]);    
        Transaction::create($cr_data);
        aggregate_account_transactions();
    }

    /**
     * Post Goods Received Account transactions
     * @param \App\Models\goodsreceivenote\Goodsreceivenote $grn
     * @return void
     */
    public function post_transaction($grn)
    {
        // credit Uninvoiced Goods Received Note (liability)
        $account = Account::where('system', 'grn')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'grn')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $grn->total,
            'tr_date' => $grn->date,
            'due_date' => $grn->date,
            'user_id' => $grn->user_id,
            'note' => $grn->note,
            'ins' => $grn->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $grn->id,
            'user_type' => 'supplier',
            'is_primary' => 1
        ];
        Transaction::create($cr_data);

        // debit Inventory (Stock) Account
        unset($cr_data['credit'], $cr_data['is_primary']);
        $account = Account::where('system', 'stock')->first(['id']);
        $dr_data = array_replace($cr_data, [
            'account_id' => $account->id,
            'debit' => $grn->total,
        ]);    
        Transaction::create($dr_data);
        aggregate_account_transactions();
    }
}