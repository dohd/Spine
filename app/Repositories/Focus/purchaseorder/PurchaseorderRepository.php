<?php

namespace App\Repositories\Focus\purchaseorder;

use App\Models\purchaseorder\Purchaseorder;
use App\Exceptions\GeneralException;
use App\Models\bill\Bill;
use App\Models\items\GrnItem;
use App\Models\items\PurchaseorderItem;
use App\Models\purchaseorder\Grn;
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
        // sanitize
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
        // sanitize
        foreach ($order_items as $i => $item) {
            $order_items[$i] = $item + [
                'ins' => $order['ins'],
                'user_id' => $order['user_id'],
                'purchaseorder_id' => $result->id
            ];
            foreach ($item as $key => $val) {
                if (in_array($key, ['rate', 'tax', 'amount'], 1)) {
                    $order_items[$i][$key] = numberClean($val);
                }
            }
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
        // sanitize
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
     * For storing grn 
     */
    public function create_grn($purchaseorder, array $input)
    {
        DB::beginTransaction();

        $order = $input['order'];
        $result = Grn::create($order);    

        $order_items = $input['order_items'];
        foreach ($order_items as $k => $item) {
            $item = $item + [
                'grn_id' => $result->id,
                'ins' => $order['ins'],
                'user_id' => $order['user_id'],
            ];
            $item['date'] = date_for_database($item['date']);
            if (!$item['qty']) $item['qty'] = 0;
            if ($item['grn_qty']) {
                $item['grn_qty'] = $item['grn_qty'] + $item['qty'];
            } else $item['grn_qty'] = $item['qty'];

            $order_items[$k] = $item;
        }
        GrnItem::insert($order_items);

        // create bill
        $exclude_keys = ['purchaseorder_id', 'stock_grn', 'expense_grn', 'asset_grn'];
        $bill = array_diff_key($order, array_flip($exclude_keys));
        $bill = $bill + [
            'supplier_id' => $result->purchaseorder->supplier_id, 
            'po_id' => $result->purchaseorder->id
        ];
        Bill::create($bill);

        DB::commit();
        if ($result) return true;
    }
}
