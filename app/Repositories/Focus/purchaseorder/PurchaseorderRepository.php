<?php

namespace App\Repositories\Focus\purchaseorder;

use App\Models\purchaseorder\Purchaseorder;
use App\Exceptions\GeneralException;
use App\Models\bill\Bill;
use App\Models\billitem\BillItem;
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
        $q = $this->query()->where('is_po', 1);

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

        $bill = $input['bill'];
        $bill['is_po'] = 1;
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
        $result = Bill::create($bill);

        $bill_items = $input['bill_items'];
        foreach ($bill_items as $i => $item) {
            // inject new keys
            $bill_items[$i] = $item + [
                'ins' => $bill['ins'],
                'user_id' => $bill['user_id'],
                'bills_id' => $result->id
            ];
            // sanitize
            foreach ($item as $key => $val) {
                if (in_array($key, ['rate', 'tax', 'amount'], 1)) {
                    $bill_items[$i][$key] = numberClean($val);
                }
            }
        }
        BillItem::insert($bill_items);

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
    public function update(Purchaseorder $purchaseorder, array $input)
    {
       
        throw new GeneralException(trans('exceptions.backend.purchaseorders.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Purchaseorder $purchaseorder
     * @throws GeneralException
     * @return bool
     */
    public function delete(Purchaseorder $purchaseorder)
    {
        if ($purchaseorder->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.purchaseorders.delete_error'));
    }
}
