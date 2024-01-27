<?php

namespace App\Repositories\Focus\opening_stock;

use DB;
use App\Exceptions\GeneralException;
use App\Models\items\OpeningStockItem;
use App\Models\opening_stock\OpeningStock;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use Illuminate\Support\Arr;

class OpeningStockRepository extends BaseRepository
{
    use Accounting;

    /**
     * Associated Repository Model.
     */
    const MODEL = OpeningStock::class;

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
     * @return OpeningStock $opening_stock
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if ($key == 'date') $input[$key] = date_for_database($val);
            if ($key == 'total') $input[$key] = numberClean($val);
            if (in_array($key, ['qty_alert', 'purchase_price', 'qty', 'amount'])) {
                $input[$key] = array_map(function ($v) {
                    return numberClean($v);
                }, $val);
            }
        }
        $result = OpeningStock::create($input);

        $data_items = Arr::only($input, ['product_id', 'parent_id', 'qty_alert', 'purchase_price', 'qty', 'amount']);
        $data_items = array_filter(modify_array($data_items), fn($v) => $v['qty'] > 0);
        $data_items = array_map(function ($v) use ($result) {
            return array_replace($v, [
                'opening_stock_id' => $result->id,
            ]);
        }, $data_items);
        OpeningStockItem::insert($data_items);

        // update inventory stock
        foreach ($result->items as $item) {
            $item->productvariation->update([
                'purchase_price' => $item->purchase_price,
                'qty' => $item->qty
            ]);
        }

        /**accounting */
        $this->post_opening_stock($result);

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param OpeningStock $opening_stock
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(OpeningStock $opening_stock, array $input)
    {
        dd($input);
    }

    /**
     * For deleting the respective model from storage
     *
     * @param OpeningStock $opening_stock
     * @throws GeneralException
     * @return bool
     */
    public function delete(OpeningStock $opening_stock)
    {
        DB::beginTransaction();
        // revert stock state
        foreach ($opening_stock->items as $item) {
            $item->productvariation->update(['purchase_price' => 0]);
            $item->productvariation->decrement('qty', $item->qty);
        }

        $opening_stock->transactions()->delete();
        aggregate_account_transactions();
        $opening_stock->items()->delete();
        $result = $opening_stock->delete();
        if ($result) {
            DB::commit();
            return true;
        }
    }    
}
