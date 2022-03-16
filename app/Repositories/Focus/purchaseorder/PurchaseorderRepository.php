<?php

namespace App\Repositories\Focus\purchaseorder;

use App\Models\purchaseorder\Purchaseorder;
use App\Exceptions\GeneralException;
use App\Models\items\PurchaseorderItem;
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
        foreach ($order_items as $i => $item) {
            // inject new keys
            $order_items[$i] = $item + [
                'ins' => $order['ins'],
                'user_id' => $order['user_id'],
                'purchaseorder_id' => $result->id
            ];
            // sanitize
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
        dd($input);
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

        // update or create new items
        $order_items = $input['order_items'];
        foreach ($order_items as $item) {
            $item = $item + [
                'ins' => $order['ins'],
                'user_id' => $order['user_id'],
                'purchaseorder_id' => $purchaseorder->id
            ];

            $order_item = PurchaseorderItem::firstOrNew(['id' => $item['id']]);
            foreach($item as $key => $value) {
                if (in_array($key, ['rate', 'tax', 'amount'], 1)) {
                    $order_items[$key] = numberClean($value);
                } else $order_item[$key] = $value;
            }
            
            unset($order_item['id']);
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
}
