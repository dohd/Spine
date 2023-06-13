<?php

namespace App\Repositories\Focus\surcharge;

use App\Models\surcharge\SurchargeItems;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Models\surcharge\Surcharge;
use App\Models\product\ProductVariation;


/**
 * Class ProductcategoryRepository.
 */
class SurchargeRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Surcharge::class;

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
        $surcharge = $input['surcharge'];
            foreach ($surcharge as $key => $val) {
                $rate_keys = [
                    'employee_id','employee_name','date',
                    'issue_type',
                    'months',
                    'cost',
                ];
                if (in_array($key, ['date'], 1))
                    $surcharge[$key] = date_for_database($val);
                
            }
            
        $result = Surcharge::create($surcharge);

        $surcharge_items = $input['surcharge_items'];
    
        $surcharge_items = array_map(function ($v) use($result) {
            
            return array_replace($v, [
                'ins' => $result->ins,
                'user_id' => $result->user_id,
                'surcharge_id' => $result->id,
            ]);
        }, $surcharge_items);
        
       
        SurchargeItems::insert($surcharge_items);
        

        DB::commit();
        if ($result) return $result;   

        throw new GeneralException(trans('exceptions.backend.surcharge.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Surcharge $surcharge, array $input)
    {
        DB::beginTransaction();

        $data = $input['data'];
        foreach ($surcharge as $key => $val) {
            $rate_keys = [
                'employee_id',
                'employee_name',
                'date',
                'issue_type',
                'months',
                'cost',
            ];
            if (in_array($key, ['date'], 1))
                $surcharge[$key] = date_for_database($val);
        }

        $result = $surcharge->update($data);

        $data_items = $input['data_items'];
        //dd($data_items);
        // delete omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        //dd($item_ids);
        $surcharge->item()->whereNotIn('id', $item_ids)->delete();
        // create or update surcharge item
        foreach ($data_items as $item) {         
            $surcharge_item = SurchargeItems::firstOrNew(['id' => $item['id']]);   

            $item = array_replace($item, [
                'ins' => $surcharge->ins,
                'user_id' => $surcharge->user_id,
                'surcharge_id' => $surcharge->id,
            ]);
            $surcharge_item->fill($item);
            if (!$surcharge_item->id) unset($surcharge_item->id);
            $surcharge_item->save(); 
        }
       // dd($surcharge_item);
        if ($result) {
            DB::commit();
            return $surcharge;
        }

        throw new GeneralException(trans('exceptions.backend.surchargeorders.update_error'));

    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete($surcharge)
    {
        
        $surcharge_items = surchargeItems::where('asset_issuance_id', $surcharge->id)->get();
        
        foreach ($surcharge_items as $key => $value) {
            $variations = ProductVariation::where('id',$value->item_id)->get()->first();
            $variations->qty = $variations->qty + $value->qty_issued;
            $variations->update();
            //dd($value->qty_issued);
        }
        
        if ($surcharge->delete() && $surcharge_items->each->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.surcharge.delete_error'));
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
            $new_item = surchargeItems::firstOrNew(['id' => $item['id']]);
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
