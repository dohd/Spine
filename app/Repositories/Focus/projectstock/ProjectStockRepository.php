<?php

namespace App\Repositories\Focus\projectstock;

use App\Exceptions\GeneralException;
use App\Models\items\ProjectstockItem;
use App\Models\projectstock\Projectstock;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use App\Repositories\Focus\product\ProductRepository;
use DB;
use Illuminate\Support\Arr;

/**
 * Class ProductcategoryRepository.
 */
class ProjectStockRepository extends BaseRepository
{
    use Accounting;

    /**
     * Associated Repository Model.
     */
    const MODEL = Projectstock::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    { 
        
        return $this->query();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return \App\Models\projectstock\Projectstock $projectstock
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();
        // sanitize
        foreach ($input as $key => $val) {
            if ($key == 'date') $input[$key] = date_for_database($val);
            if ($key == 'qty') $input[$key] = array_map(function ($v) { 
                return numberClean($v); 
            }, $val);
        }

        $result = Projectstock::create($input);

        $data_items = Arr::only($input, ['budget_item_id', 'product_id', 'unit', 'warehouse_id', 'qty']);
        $data_items = array_filter(modify_array($data_items), function ($v) { return $v['qty'] > 0; });
        foreach ($data_items as $i => $item) {
            $data_items[$i] = array_replace($item, ['project_stock_id' => $result->id]);
        }
        ProjectstockItem::insert($data_items);

        $product_repository = new ProductRepository;
        foreach ($result->items as $issue_item) {
            // increase budget item issuance qty
            $budget_item = $issue_item->budget_item;
            $budget_item->increment('issue_qty', $issue_item->qty);

            $prod_variation = $issue_item->productvariation;
            // skip service product
            if ($prod_variation->product->stock_type == 'service') continue;
                
            // apply unit conversion
            $units = $prod_variation->product->units;
            foreach ($units as $unit) {
                if ($unit->code == $issue_item->unit) {
                    if ($unit->unit_type == 'base') {
                        $prod_variation->decrement('qty', $issue_item->qty);
                    } else {
                        $converted_qty = $issue_item->qty * $unit->base_ratio;
                        $prod_variation->decrement('qty', $converted_qty);
                    }
                }
            }   

            // update stock worth based on last in first out purchase price
            $purchase_price = $product_repository->eval_purchase_price(
                $prod_variation->id, $prod_variation->qty, $prod_variation->purchase_price
            );
            $subtotal = $issue_item->qty * $purchase_price;
            $result->subtotal += $subtotal;
            $result->total += $subtotal;
        }
        $result->save();

        /** accounting */
        $this->post_projectstock_issuance($result);

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param \App\Models\projectstock\Projectstock $projectstock
     * @param  array $input
     * @throws GeneralException
     * @return \App\Models\projectstock\Projectstock $projectstock
     */
    public function update(Projectstock $projectstock, array $input)
    {
        dd($input);
    }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\projectstock\Projectstock $projectstock
     * @throws GeneralException
     * @return bool
     */
    public function delete(Projectstock $projectstock)
    {     
        DB::beginTransaction();

        foreach ($projectstock->items as $issue_item) {
            // decrease budget item issuance qty
            $budget_item = $issue_item->budget_item;
            $budget_item->decrement('issue_qty', $issue_item->qty);

            $prod_variation = $issue_item->productvariation;

            // apply unit conversion
            $units = $prod_variation->product->units;
            foreach ($units as $unit) {
                if ($unit->code == $issue_item->unit) {
                    if ($unit->unit_type == 'base') {
                        $prod_variation->decrement('qty', $issue_item->qty);
                    } else {
                        $converted_qty = $issue_item->qty * $unit->base_ratio;
                        $prod_variation->decrement('qty', $converted_qty);
                    }
                }
            }   
        }

        $projectstock->transactions()->delete();
        $projectstock->items()->delete();
        $result = $projectstock->delete();
        if ($result) {
            DB::commit(); 
            return true;
        }
    }    
}