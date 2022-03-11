<?php

namespace App\Repositories\Focus\purchase;

use App\Models\purchase\Purchase;
use App\Exceptions\GeneralException;
use App\Models\bill\Bill;
use App\Models\billitem\BillItem;
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

        $q = $this->query();

        $q->when(request('rel_type') == 2, function ($q) {
            return $q->where('payer_id', '=', request('rel_id', 0));
        });
        $q->when(request('rel_type') == 3, function ($q) {
            return $q->where('payer_id', '=', request('rel_id', 0));
        });

        $q->when(request('rel_type') == 2, function ($q) {
            return $q->where('payer_type', '=', 'supplier');
        });
        $q->when(request('rel_type') == 3, function ($q) {
            return $q->where('payer_type', '=', 'customer');
        });



        $q->when(request('rel_type') == 1, function ($q) {
            return  $q->where('is_bill', 1);
        });
        $q->when(request('rel_type') == 1, function ($q) {
            return  $q->where('transaction_type', 'purchases');;
        });

        $q->when(request('i_rel_type') == 1, function ($q) {

            return $q->where('supplier_id', '=', request('i_rel_id', 0));
        });

        return
            $q->get();
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
        $bill = array_replace($bill, [
            'date' =>  date_for_database($bill['date']),
            'due_date' => date_for_database($bill['due_date'])
        ]);
        $result = Bill::create($bill);

        // inject new keys
        $bill_items = $input['bill_items'];
        foreach ($bill_items as $k => $item) {
            $bill_items[$k] = $item + [
                'ins' => $bill['ins'], 
                'user_id' => $bill['user_id'],
                'bills_id' => $result->id
            ];
        }
        BillItem::insert($bill_items);


        DB::commit();

        dd($bill_items);
        return true;

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
