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
        DB::beginTransaction();

        $order = $input['order'];
        $rate_keys = [
            'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
        ];
        foreach ($order as $key => $val) {
            if (in_array($key, ['date', 'due_date'], 1)) {
                $order[$key] = date_for_database($val);
            }
            if (in_array($key, $rate_keys, 1)) {
                $order[$key] = numberClean($val);
            }
        }
        $result = Purchaseorder::create($order);

        $order_items = $input['order_items'];
        foreach ($order_items as $i => $item) {
            $item = $item + [
                'ins' => $order['ins'],
                'user_id' => $order['user_id'],
                'purchaseorder_id' => $result->id
            ];
            foreach ($item as $key => $val) {
                if (in_array($key, ['rate', 'taxrate', 'amount'], 1)) {
                    $item[$key] = numberClean($val);
                }
            }
            $order_items[$i] = $item;
        }
        PurchaseorderItem::insert($order_items);

        DB::commit();
        if ($result) return true;

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
        DB::beginTransaction();

        $order = $input['order'];
        $rate_keys = [
            'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
        ];
        foreach ($order as $key => $val) {
            if (in_array($key, ['date', 'due_date'], 1)) 
                $order[$key] = date_for_database($val);
            if (in_array($key, $rate_keys, 1)) 
                $order[$key] = numberClean($val);
        }
        $purchaseorder->update($order);

        $order_items = $input['order_items'];
        // delete items excluded
        $item_ids = array_reduce($order_items, function ($init, $item) {
            array_push($init, $item['id']);
            return $init;
        }, []);
        $purchaseorder->products()->whereNotIn('id', $item_ids)->delete();

        // update or create new items
        foreach ($order_items as $item) {
            $item = $item + [
                'ins' => $order['ins'],
                'user_id' => $order['user_id'],
                'purchaseorder_id' => $purchaseorder->id
            ];

            $order_item = PurchaseorderItem::firstOrNew(['id' => $item['id']]);
            foreach($item as $key => $val) {
                if (in_array($key, ['rate', 'taxrate', 'amount'], 1)) {
                    $order_item[$key] = numberClean($val);
                } 
                else $order_item[$key] = $val;
            }
            if (!$order_item->id) unset($order_item['id']);
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
        if ($purchaseorder->delete()) return true;            
        
        throw new GeneralException(trans('exceptions.backend.purchaseorders.delete_error'));
    }

    
    /**
     * Store goods received
     */
    public function create_grn($purchaseorder, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $order = $input['order'];
        $grn = Grn::create($order);    

        $order_items = $input['order_items'];
        foreach ($order_items as $k => $item) {
            if (!isset($item['qty'])) $item['qty'] = 0;
            $order_items[$k] = array_replace($item, [
                'grn_id' => $grn->id,
                'ins' => $order['ins'],
                'user_id' => $order['user_id'],
                'date' => date_for_database($item['date']),
            ]);
        }
        GrnItem::insert($order_items);

        // increase stock
        foreach ($grn->items as $item) {
            $product = $item->purchaseorder_item->product;
            if ($product) $product->increment('qty', $item->qty);
        }

        // update purchaseorder status
        $grn_qty = $purchaseorder->grn_items->sum('qty');
        if ($grn_qty) {
            $order_qty = $purchaseorder->items->sum('qty');
            if ($grn_qty < $order_qty) $purchaseorder->update(['status' => 'Partial']);
            else $purchaseorder->update(['status' => 'Complete']);
        }

        // create bill
        $exclude_keys = ['id', 'purchaseorder_id', 'stock_grn', 'expense_grn', 'asset_grn', 'items'];
        $bill_inp = array_diff_key($grn->toArray(), array_flip($exclude_keys));
        $po = $grn->purchaseorder;
        $bill_inp = $bill_inp + [
            'date' => date('Y-m-d'), 
            'due_date' => $po->due_date, 
            'supplier_type' => 'supplier',
            'supplier_id' => $po->supplier_id,
            'supplier_taxid' => $po->supplier_taxid,
            'project_id' => $po->project_id,
            'doc_ref_type' => $po->doc_ref_type,
            'doc_ref' => $po->doc_ref,
            'po_id' => $po->id,
            'tax' => $po->tax,
            'note' => $po->note,
        ];
        $bill['tid'] = Bill::max('tid') + 1;
        $bill = Bill::create($bill_inp);

        // create bill items
        $bill_items = array();
        foreach ($grn->items as $item) {
            $poitem = $item->purchaseorder_item->toArray();
            $bill_item = array_diff_key($poitem, array_flip(['id', 'uom', 'purchaseorder_id', 'product']));
            $bill_item = array_replace($bill_item, [
                'bill_id' => $bill->id,
                'qty' => $item->qty,
                'taxrate' => ($poitem['taxrate'] / $poitem['qty'] * $item->qty),
                'amount' => ($poitem['amount'] / $poitem['qty'] * $item->qty),
            ]);
            $bill_items[] = $bill_item;
        }
        BillItem::insert($bill_items);

        // accounting
        $this->post_transaction($bill_inp, $bill_items, $bill);

        DB::commit();
        if ($grn) return true;
    }

    /**
     * Post Account Transaction
     */
    protected function post_transaction($order, $order_items, $bill) 
    {
        /** credit accounts payable */ 
        $account = Account::where('system', 'payable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'BILL')->first(['id', 'code']);
        $cr_data = [
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $order['grandttl'],
            'tr_date' => date('Y-m-d'),
            'due_date' => $order['due_date'],
            'user_id' => $order['user_id'],
            'note' => $bill->note,
            'ins' => $order['ins'],
            'tr_type' => $tr_category->code,
            'tr_ref' => $bill->id,
            'user_type' => 'supplier',
            'is_primary' => 1
        ];
        $tr = Transaction::create($cr_data);
        $bill->update(['tr_ref' => $tr->id]);

        /** debit */
        $dr_data = array();
        unset($cr_data['credit'], $cr_data['is_primary']);
        // stock
        $stock_items = array_filter($order_items, function ($item) { return $item['type'] == 'Stock'; });
        if ($stock_items) {
            $account = Account::where('system', 'stock')->first(['id']);
            $stock_tr_category = Transactioncategory::where('code', 'stock')->first(['id']);
            $dr_data[] = array_replace($cr_data, [
                'account_id' => $account->id,
                'trans_category_id' => $stock_tr_category->id,
                'debit' => $order['stock_subttl'],
            ]);    
        }
        // expense and asset
        $asset_tr_category = Transactioncategory::where('code', 'p_asset')->first(['id']);
        foreach ($order_items as $item) {
            $subttl = $item['amount'] - $item['taxrate'];
            if ($item['type'] == 'Expense') {
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $item['item_id'],
                    'debit' => $subttl,
                ]);
            }
            if ($item['type'] == 'Asset') {
                $asset = Assetequipment::find($item['item_id']);
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $asset->account_id,
                    'trans_category_id' => $asset_tr_category->id,
                    'debit' => $subttl,
                ]);
            }
        }
        // tax
        $account = Account::where('system', 'tax')->first(['id']);
        $dr_data[] = array_replace($cr_data, [
            'account_id' => $account->id, 
            'debit' => $order['grandtax'],
        ]);
        Transaction::insert($dr_data); 
        
        // update account ledgers debit and credit totals
        aggregate_account_transactions();
    }
}