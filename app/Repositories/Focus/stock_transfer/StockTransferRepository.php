<?php

namespace App\Repositories\Focus\stock_transfer;

use App\Exceptions\GeneralException;
use App\Models\items\StockTransferItem;
use App\Models\product\ProductVariation;
use App\Models\stock_transfer\StockTransfer;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class StockTransferRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = StockTransfer::class;

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
     * @return StockTransfer $stock_transfer
     */
    public function create(array $input)
    {
        // dd($input);
        $data = Arr::only($input, ['tid', 'source_id', 'destination_id', 'note', 'total']);
        $data_items = Arr::only($input, ['product_id', 'qty', 'uom', 'unit_price', 'amount']);

        DB::beginTransaction();

        $data['total'] = numberClean($data['total']);
        $result = StockTransfer::create($data);
        
        $data_items = modify_array($data_items);
        $data_items = array_map(function($v) use($result) {
            return array_replace($v, [
                'qty' => numberClean($v['qty']),
                'unit_price' => numberClean($v['unit_price']),
                'amount' => numberClean($v['amount']),
                'stock_transfer_id' => $result->id,
            ]);
        }, $data_items);    
        StockTransferItem::insert($data_items);

        // update inventory
        foreach ($result->items as $item) {
            $prod_variation = $item->product_variation;
            if ($prod_variation) {
                $similar_prod_variation = ProductVariation::where(['parent_id' => $prod_variation->parent_id, 'warehouse_id' => $result->destination_id])->first();
                if ($similar_prod_variation) {
                    $prod_variation = $similar_prod_variation;
                } else {
                    // new warehouse product variation
                    $new_wh_product = clone $prod_variation;
                    $new_wh_product->fill([
                        'id' => null, 
                        'warehouse_id' => $result->destination_id,
                        'qty' => 0,
                        'price' => $item->unit_price,
                    ]);
                    $new_wh_product->save();
                    $prod_variation = $new_wh_product;
                }


                // apply unit conversion and increment qty
                if (isset($prod_variation->product->units)) {
                    $units = $prod_variation->product->units;
                    foreach ($units as $unit) {
                        if ($unit->code == $item['uom']) {
                            if ($unit->unit_type == 'base') {
                                $prod_variation->increment('qty', $item['qty']);
                            } else {
                                $converted_qty = $item['qty'] * $unit->base_ratio;
                                $prod_variation->increment('qty', $converted_qty);
                            }
                        }
                    }  
                } else throw ValidationException::withMessages(['Please attach units to stock items']);
            }
        }

        if ($result) {
            DB::commit();
            return $result;
        }
            
        throw new GeneralException(trans('exceptions.backend.stock_transfer.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param StockTransfer $stock_transfer
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(StockTransfer $stock_transfer, array $input)
    {   
        dd($stock_transfer);

        throw new GeneralException(trans('exceptions.backend.stock_transfer.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param StockTransfer $stock_transfer
     * @throws GeneralException
     * @return bool
     */
    public function delete(StockTransfer $stock_transfer)
    {
        if ($stock_transfer->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.stock_transfer.delete_error'));
    }
}
