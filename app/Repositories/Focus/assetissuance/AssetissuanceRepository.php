<?php

namespace App\Repositories\Focus\assetissuance;

use App\Models\assetissuance\AssetissuanceItems;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Models\assetissuance\Assetissuance;


/**
 * Class ProductcategoryRepository.
 */
class AssetissuanceRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Assetissuance::class;

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
        $assetissuance = $input['assetissuance'];
        //dd($assetissuance);
        //if (is_array($assetissuance) || is_object($assetissuance)){
            foreach ($assetissuance as $key => $val) {
                $rate_keys = [
                    'employee_id','employee_name','issue_date','return_date','note'
                ];
                if (in_array($key, ['issue_date', 'return_date'], 1))
                    $assetissuance[$key] = date_for_database($val);
                
            }
            
       // }
        
        $result = Assetissuance::create($assetissuance);

        $assetissuance_items = $input['assetissuance_items'];
        $assetissuance_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'ins' => $result->ins,
                'user_id' => $result->user_id,
                'asset_issuance_id' => $result->id,
            ]);
        }, $assetissuance_items);
        AssetissuanceItems::insert($assetissuance_items);

        DB::commit();
        if ($result) return $result;   

        throw new GeneralException(trans('exceptions.backend.assetissuance.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Assetissuance $assetissuance, array $input)
    {
        DB::beginTransaction();

        $data = $input['data'];
        foreach ($assetissuance as $key => $val) {
            $rate_keys = [
                'employee_id','employee_name','issue_date','return_date','note'
            ];
            if (in_array($key, ['issue_date', 'return_date'], 1))
                $assetissuance[$key] = date_for_database($val);
        }

        $prev_note = $assetissuance->note;
        $result = $assetissuance->update($data);

        $data_items = $input['data_items'];
        //dd($data_items);
        // delete omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        //dd($item_ids);
        $assetissuance->items()->whereNotIn('id', $item_ids)->delete();
        // create or update assetissuance item
        foreach ($data_items as $item) {         
            $assetissuance_item = AssetissuanceItems::firstOrNew(['id' => $item['id']]);

            // update product stock
            // if ($item->exists()) {
            //     $prod_variation = $assetissuance_item->product;
            //     if ($prod_variation) $prod_variation->decrement('qty', $assetissuance_item->qty);
            //     else $prod_variation = ProductVariation::find($item['item_id']);
            //     // apply unit conversion
                 
            // }    

            $item = array_replace($item, [
                'ins' => $assetissuance->ins,
                'user_id' => $assetissuance->user_id,
                'asset_issuance_id' => $assetissuance->id,
            ]);   
            $assetissuance_item->fill($item);
            if (!$assetissuance_item->id) unset($assetissuance_item->id);
            $assetissuance_item->save();
        }

        // direct assetissuance bill 
        // $this->generate_bill($assetissuance);

        // /** accounting */
        // $assetissuance->transactions()->where('note', $prev_note)->delete();
        // $this->post_transaction($assetissuance);

        if ($result) {
            DB::commit();
            return $assetissuance;
        }

        throw new GeneralException(trans('exceptions.backend.assetissuanceorders.update_error'));

    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete($assetissuance)
    {
        if ($assetissuance->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.assetissuance.delete_error'));
    }
    public function update_asset($budget, $input)
    {   
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        $keys = array('employee_id','employee_name','issue_date','return_date','note');
        foreach ($data as $key => $val) {
            if (in_array($key, $keys)) 
                $data[$key] = numberClean($val);
        }   
        $result = $budget->update($data);

        $data_items = $input['data_items'];
        // delete omitted line items
        $budget->item()->whereNotIn('id', array_map(fn($v) => $v['item_id'], $data_items))->delete();
        // new or update item
        //dd($data_items);
        foreach($data_items as $item) {
            $item = array_replace($item, [
                'ins' => $budget->ins,
                'user_id' => $budget->user_id,
                'asset_issuance_id' => $budget->id,
            ]);
            $new_item = AssetissuanceItems::firstOrNew(['id' => $item['id']]);
            $new_item->fill($item);
            //dd($item);
            if (!$new_item->id) unset($new_item->id);
            $new_item->save();
           // dd($new_item);
            // $new_item->fill($item);
            // dd($new_item);
            // if (!$new_item->id) unset($new_item->id);
            // unset($new_item->item_id);
            // $new_item->save();
        }

        
        if ($result) {
            DB::commit();
            return $result;
        }
    }   

}
