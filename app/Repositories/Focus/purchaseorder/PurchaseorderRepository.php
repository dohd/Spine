<?php

namespace App\Repositories\Focus\purchaseorder;

use App\Models\purchaseorder\Purchaseorder;
use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\assetequipment\Assetequipment;
use App\Models\items\PurchaseorderItem;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

        $q->when(request('supplier_id'), function ($q) {
            $q->where('supplier_id', request('supplier_id'));
        })->when(request('status'), function ($q) {
            if (request('status') == 'Closed') $q->where('closure_status', 1);   
            else $q->where('status', request('status'))->where('closure_status', 0);     
        });

        return $q;
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
        
        $tid = Purchaseorder::where('ins', $order['ins'])->max('tid');
        if ($order['tid'] <= $tid) $order['tid'] = $tid+1;
        $result = Purchaseorder::create($order);

        $order_items = $input['order_items'];
        foreach ($order_items as $item) {
            if (@$item['type'] == 'Stock' && !$item['uom'])
            throw ValidationException::withMessages(['Unit of Measure (uom) required for Inventory Items']);
        }
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
        
        if ($result) {
            DB::commit();
            return $result;   
        }

        DB::rollBack();
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
        foreach ($order_items as $item) {
            if (@$item['type'] == 'Stock' && !$item['uom'])
            throw ValidationException::withMessages(['Unit of Measure (uom) required for Inventory Items']);
        }
        // delete omitted items
        $item_ids = array_map(fn($v) => $v['id'], $order_items);
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

        if ($purchaseorder) {
            DB::commit();
            return true;
        }

        DB::rollBack();
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
        if ($purchaseorder->grn_items->count()) 
            throw ValidationException::withMessages(['Purchase order is attached to a Goods Receive Note!']);

        DB::beginTransaction();
        try {
            $purchaseorder->transactions()->delete();
            aggregate_account_transactions();
            
            if ($purchaseorder->delete()) {
                DB::commit();
                return true;
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new GeneralException(trans('exceptions.backend.purchaseorders.delete_error'));
        }     
    }


    /**
     * Post Account Transaction
     */
    protected function post_transaction($bill) 
    {
        // credit Accounts Payable (Creditors) 
        $account = Account::where('system', 'payable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'bill')->first(['id', 'code']);
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
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