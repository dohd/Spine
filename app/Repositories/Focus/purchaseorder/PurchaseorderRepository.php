<?php

namespace App\Repositories\Focus\purchaseorder;

use App\Models\purchaseorder\Purchaseorder;
use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\assetequipment\Assetequipment;
use App\Models\bill\Bill;
use App\Models\billitem\BillItem;
use App\Models\items\GrnItem;
use App\Models\items\PurchaseorderItem;
use App\Models\purchaseorder\Grn;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;

use Illuminate\Support\Facades\DB;

/**
 * Class PurchaseorderRepository.
 */
class PurchaseorderRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Purchaseorder::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

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

        $order = $input['order'];
        foreach ($order as $key => $val) {
            $rate_keys = [
                'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
                'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
            ];
            if (in_array($key, ['date', 'due_date'], 1))
                $order[$key] = date_for_database($val);
            if (in_array($key, $rate_keys, 1)) 
                $order[$key] = numberClean($val);
        }
        $result = Purchaseorder::create($order);

        $order_items = $input['order_items'];
        $order_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'ins' => $result->ins,
                'user_id' => $result->user_id,
                'purchaseorder_id' => $result->id,
                'rate' => numberClean($v['rate']),
                'taxrate' => numberClean($v['taxrate']),
                'amount' => numberClean($v['amount'])
            ]);
        }, $order_items);
        PurchaseorderItem::insert($order_items);

        DB::commit();
        
        // proof check line item totals against parent totals
        $grandtax = $result->items->sum('taxrate');
        $subtotal = $result->items->sum('amount') - $result->items->sum('taxrate');
        if (round($result->grandtax) != round($grandtax) || round($result->paidttl) != round($subtotal)) {
            $result['omission_error'] = true;
        }

        if ($result) return $result;   

        throw new GeneralException(trans('exceptions.backend.purchaseorders.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Purchaseorder $purchaseorder
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($purchaseorder, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $order = $input['order'];
        foreach ($order as $key => $val) {
            $rate_keys = [
                'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
                'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
            ];    
            if (in_array($key, ['date', 'due_date'], 1)) 
                $order[$key] = date_for_database($val);
            if (in_array($key, $rate_keys, 1)) 
                $order[$key] = numberClean($val);
        }
        $purchaseorder->update($order);

        $order_items = $input['order_items'];
        // delete omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $order_items);
        $purchaseorder->products()->whereNotIn('id', $item_ids)->delete();
        // update or create new items
        foreach ($order_items as $item) {
            $item = array_replace($item, [
                'ins' => $order['ins'],
                'user_id' => $order['user_id'],
                'purchaseorder_id' => $purchaseorder->id,
                'rate' => numberClean($item['rate']),
                'taxrate' => numberClean($item['taxrate']),
                'amount' => numberClean($item['amount'])
            ]);
            $order_item = PurchaseorderItem::firstOrNew(['id' => $item['id']]);
            $order_item->fill($item);
            if (!$order_item->id) unset($order_item->id);
            $order_item->save();                
        }

        DB::commit();
        if ($purchaseorder) return true;

        throw new GeneralException(trans('exceptions.backend.purchaseorders.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Purchaseorder $purchaseorder
     * @throws GeneralException
     * @return bool
     */
    public function delete($purchaseorder)
    {
        try {
            DB::beginTransaction();

            $purchaseorder->transactions()->delete();
            aggregate_account_transactions();
            $purchaseorder->delete();

            DB::commit();
            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new GeneralException(trans('exceptions.backend.purchaseorders.delete_error'));
        }     
    }

    
    /**
     * Store goods received
     */
    public function create_grn($purchaseorder, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $order = $input['order'];
        foreach ($order as $key => $val) {
            $keys = ['purchaseorder_id', 'stock_grn', 'expense_grn', 'asset_grn', 'ins', 'user_id'];
            if (!in_array($key, $keys)) $order[$key] = numberClean($val);
        }
        $grn = Grn::create($order);    

        $order_items = $input['order_items'];
        $order_items = array_map(function ($v) use($grn) {
            if (empty($v['qty'])) $v['qty'] = 0;
            return array_replace($v, [
                'grn_id' => $grn->id,
                'ins' => $grn->ins,
                'user_id' => $grn->user_id,
                'date' => date_for_database($v['date']),
            ]);
        }, $order_items);
        GrnItem::insert($order_items);

        // create bill
        $bill_data = collect([$grn])->map(function ($v) use($grn) {
            $v = array_diff_key($v->toArray(), array_flip(['id', 'purchaseorder_id', 'stock_grn', 'expense_grn', 'asset_grn']));
            $po = $grn->purchaseorder;
            $v = array_replace($v, [
                'tid' => Bill::max('tid') + 1,
                'date' => date('Y-m-d'), 
                'due_date' => date('Y-m-d'), 
                'supplier_type' => 'supplier',
                'supplier_id' => $po->supplier_id,
                'supplier_taxid' => $po->supplier_taxid,
                'project_id' => $po->project_id,
                'doc_ref_type' => $po->doc_ref_type,
                'doc_ref' => $po->doc_ref,
                'po_id' => $po->id,
                'tax' => $po->tax,
                'note' => $po->note,
            ]);
            return $v;
        })->first();
        $bill = Bill::create($bill_data);

        // create bill items from grn items
        $bill_items_data = $grn->items->map(function ($v) use($bill) {
            $po_item = $v->purchaseorder_item;
            $rate_per_item = $po_item->taxrate / $po_item->qty;
            $amount_per_item = $po_item->amount / $po_item->qty;

            $item = array_diff_key($po_item->toArray(), array_flip(['id', 'uom', 'purchaseorder_id']));
            $item = array_replace($item, [
                'bill_id' => $bill->id,
                'qty' => $v->qty,
                'taxrate' => $rate_per_item * $v->qty,
                'amount' => $amount_per_item * $v->qty,
            ]);
            return $item;
        })->toArray();
        BillItem::insert($bill_items_data);

        // increase product stock
        foreach ($grn->items as $item) {
            $po_item = $item->purchaseorder_item;
            if ($po_item->type == 'Stock' && $po_item->product) {
                $productvariation = $po_item->product;
                $product_unit = $productvariation->product->unit;
                
                // apply quantity conversion
                if ($product_unit->base_unit == $item['uom']) {
                    $productvariation->increment('qty', $item['qty']);
                } elseif ($product_unit->compound_unit == $item['uom']) {
                    $qty = $product_unit->bas_ratio * $item['qty'];
                    $productvariation->increment('qty', $qty);
                } 
            }
        }

        // update purchaseorder status
        $grn_qty = $purchaseorder->grn_items->sum('qty');
        $order_qty = $purchaseorder->items->sum('qty');
        if ($order_qty > $grn_qty) $purchaseorder->update(['status' => 'Partial']);
        else $purchaseorder->update(['status' => 'Complete']);

        /**accounting */
        $this->post_transaction($bill);

        DB::commit();
        if ($grn) return $grn;
    }

    /**
     * Post Account Transaction
     */
    protected function post_transaction($bill) 
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
            'is_primary' => 1
        ];
        Transaction::create($cr_data);

        $dr_data = array();
        // debit Inventory/Stock Account
        unset($cr_data['credit'], $cr_data['is_primary']);
        $is_stock = $bill->items()->where('type', 'Stock')->count();
        if ($is_stock) {
            $account = Account::where('system', 'stock')->first(['id']);
            $dr_data[] = array_replace($cr_data, [
                'account_id' => $account->id,
                'debit' => $bill->stock_subttl,
            ]);    
        }
        // debit Expense and Asset Account
        foreach ($bill->items as $item) {
            $subttl = $item->amount - $item->taxrate;
            if ($item->type == 'Expense') {
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $item->item_id,
                    'debit' => $subttl,
                ]);
            } elseif ($item->type == 'Asset') {
                $asset = Assetequipment::find($item->item_id);
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $asset->account_id,
                    'debit' => $subttl,
                ]);
            }
        }
        // debit Tax
        if ($bill->grandtax > 0) {
            $account = Account::where('system', 'tax')->first(['id']);
            $dr_data[] = array_replace($cr_data, [
                'account_id' => $account->id, 
                'debit' => $bill->grandtax,
            ]);
        }
        Transaction::insert($dr_data); 
        aggregate_account_transactions();
    }
}