<?php

namespace App\Repositories\Focus\purchase;

use App\Models\purchase\Purchase;
use App\Exceptions\GeneralException;
use App\Models\items\PurchaseItem;
use App\Repositories\BaseRepository;

use Illuminate\Support\Facades\DB;

/**
 * Class PurchaseorderRepository.
 */
class PurchaseRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Purchase::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query()->where('po_id', 0);

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

        $purchase = $input['purchase'];
        // sanitize
        $rate_keys = [
            'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
        ];
        foreach ($purchase as $key => $val) {
            if (in_array($key, ['date', 'due_date'], 1)) {
                $purchase[$key] = date_for_database($val);
            }
            if (in_array($key, $rate_keys, 1)) {
                $purchase[$key] = numberClean($val);
            }
        }
        $result = Purchase::create($purchase);

        $purchase_items = $input['purchase_items'];
        // sanitize
        foreach ($purchase_items as $i => $item) {
            $purchase_items[$i] = $item + [
                'ins' => $result->ins,
                'user_id' => $result->user_id,
                'bill_id' => $result->id
            ];
            foreach ($item as $key => $val) {
                if (in_array($key, ['rate', 'taxrate', 'amount'], 1)) {
                    $purchase_items[$i][$key] = numberClean($val);
                }
            }
        }
        PurchaseItem::insert($purchase_items);

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
    public function update(Purchase $purchase, array $input)
    {
        DB::beginTransaction();

        $bill = $input['bill'];
        // sanitize
        $rate_keys = [
            'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
        ];
        foreach ($bill as $key => $val) {
            if (in_array($key, ['date', 'due_date'], 1)) {
                $bill[$key] = date_for_database($val);
            }
            if (in_array($key, $rate_keys, 1)) {
                $bill[$key] = numberClean($val);
            }
        }
        $purchase->update($bill);

        $bill_items = $input['bill_items'];
        // delete items excluded
        $item_ids = array_reduce($bill_items, function ($init, $item) {
            array_push($init, $item['id']);
            return $init;
        }, []);
        $purchase->products()->whereNotIn('id', $item_ids)->delete();

        // update or create new items
        foreach ($bill_items as $item) {
            $item = $item + [
                'ins' =>  $purchase->ins,
                'user_id' =>  $purchase->user_id,
                'bill_id' => $purchase->id
            ];

            $bill_item = PurchaseItem::firstOrNew(['id' => $item['id']]);
            foreach($item as $key => $val) {
                if (in_array($key, ['rate', 'taxrate', 'amount'], 1)) {
                    $bill_item[$key] = numberClean($val);
                } 
                else $bill_item[$key] = $val;
            }
            if (!$bill_item->id) unset($bill_item->id);
            $bill_item->save();                
        }

        DB::commit();
        if ($purchase) return true;

        throw new GeneralException(trans('exceptions.backend.purchaseorders.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Purchaseorder $purchaseorder
     * @throws GeneralException
     * @return bool
     */
    public function delete($purchase)
    {
        if ($purchase->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.purchaseorders.delete_error'));
    }
}
