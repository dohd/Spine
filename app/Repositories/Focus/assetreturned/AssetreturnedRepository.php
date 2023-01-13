<?php

namespace App\Repositories\Focus\assetreturned;

use App\Models\assetreturned\AssetreturnedItems;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Models\assetissuance\Assetissuance;
use App\Models\assetreturned\Assetreturned;
use App\Models\product\ProductVariation;
use App\Models\assetissuance\AssetissuanceItems;


/**
 * Class ProductcategoryRepository.
 */
class AssetreturnedRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Assetreturned::class;

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
        $assetreturned = $input['assetreturned'];
        //dd($assetreturned);
        //if (is_array($assetreturned) || is_object($assetreturned)){
            foreach ($assetreturned as $key => $val) {
                $rate_keys = [
                    'employee_id','employee_name','issue_date','return_date','note','acquisition_number'
                ];
                if (in_array($key, ['issue_date', 'return_date'], 1))
                    $assetreturned[$key] = date_for_database($val);
                
            }
            
       // }
        
        $result = Assetreturned::create($assetreturned);

        $assetreturned_items = $input['assetreturned_items'];
        $assetreturned_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'ins' => $result->ins,
                'user_id' => $result->user_id,
                'asset_returned_id' => $result->id,
            ]);
        }, $assetreturned_items);
       // dd($assetreturned_items);
        foreach ($assetreturned_items as $assetreturned_items) {
            $variations = ProductVariation::where('id',$assetreturned_items['item_id'])->get()->first();
            //Total purchase price of returned items
            $assetreturned_items['purchase_price'] = (int)$assetreturned_items['returned_item'] * (int)$variations->purchase_price;
           // $assetreturns = Assetreturned::where('id',$assetreturned_items['asset_returned_id'])->first();
            //$requisition_number = $assetreturns->acquisition_number;
            dd($assetreturned_items);
            //Show each item the total price of items returned
            $assetissuance = Assetissuance::where('acquisition_number', $assetreturned['acquisition_number'])->first();
            $issuance_id =$assetissuance->id;
            $itemprice = AssetissuanceItems::where('asset_issuance_id',$issuance_id)->where('item_id',$assetreturned_items['item_id'])
                            ->where('serial_number',$assetreturned_items['serial_number'])->first();
            $itemprice->purchase_price = $itemprice->purchase_price - $assetreturned_items['purchase_price'];
            $itemprice->update();
            AssetreturnedItems::insert($assetreturned_items);
            
        }
        $returns = Assetreturned::where('acquisition_number', $result['acquisition_number'])->first();
        $returns->item()->sum('purchase_price');
        $issuance = Assetissuance::where('acquisition_number', $result['acquisition_number'])->first();
        $issuance->total_cost = $issuance->total_cost - $returns->item()->sum('purchase_price');
        $issuance->update();

        DB::commit();
        if ($result) return $result;   

        throw new GeneralException(trans('exceptions.backend.assetreturned.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Assetreturned $assetreturned, array $input)
    {
        DB::beginTransaction();

        $data = $input['data'];
        foreach ($assetreturned as $key => $val) {
            $rate_keys = [
                'employee_id','employee_name','issue_date','return_date','note'
            ];
            if (in_array($key, ['issue_date', 'return_date'], 1))
                $assetreturned[$key] = date_for_database($val);
        }

        $prev_note = $assetreturned->note;
        $result = $assetreturned->update($data);

        $data_items = $input['data_items'];
        //dd($data_items);
        // delete omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        //dd($item_ids);
        $assetreturned->items()->whereNotIn('id', $item_ids)->delete();
        // create or update assetreturned item
        foreach ($data_items as $item) {         
            $assetreturned_item = AssetreturnedItems::firstOrNew(['id' => $item['id']]);

            // update product stock
            // if ($item->exists()) {
            //     $prod_variation = $assetreturned_item->product;
            //     if ($prod_variation) $prod_variation->decrement('qty', $assetreturned_item->qty);
            //     else $prod_variation = ProductVariation::find($item['item_id']);
            //     // apply unit conversion
            // } 
            //dd($item['returned_item']);
            if($assetreturned_item['returned_item'] > (int)$item['returned_item']){
                $variations = ProductVariation::where('id',$item['item_id'])->get()->first();
                $x = $assetreturned_item['returned_item'] - (int)$item['returned_item'];
                $price = $x * $variations->purchase_price;
                $issuance_item = Assetissuance::where('acquisition_number',$assetreturned['acquisition_number'])->first();
                $issuance_item->total_cost = $issuance_item->total_cost + $price;
                $issuance_item->update();
            }else{
                $variations = ProductVariation::where('id',$item['item_id'])->get()->first();
                $y = (int)$item['returned_item'] - $assetreturned_item['returned_item'];
                $purchase = $y * $variations->purchase_price;
                $issuance_update = Assetissuance::where('acquisition_number',$assetreturned['acquisition_number'])->first();
                $issuance_update->total_cost = $issuance_update->total_cost - $purchase;
                $issuance_update->update();
            }
           
            //dd($issuance_item);   

            $item = array_replace($item, [
                'ins' => $assetreturned->ins,
                'user_id' => $assetreturned->user_id,
                'asset_returned_id' => $assetreturned->id,
            ]);   
            $assetreturned_item->fill($item);
            if (!$assetreturned_item->id) unset($assetreturned_item->id);
            $assetreturned_item->save();
        }

        if ($result) {
            DB::commit();
            return $assetreturned;
        }

        throw new GeneralException(trans('exceptions.backend.assetreturnedorders.update_error'));

    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete($assetreturned)
    {
        $assetreturned_items = AssetreturnedItems::where('asset_returned_id', $assetreturned->id)->get();
        if ($assetreturned->delete() && $assetreturned_items->each->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.assetreturned.delete_error'));
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
                'asset_returned_id' => $budget->id,
            ]);
            $new_item = AssetreturnedItems::firstOrNew(['id' => $item['id']]);
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
