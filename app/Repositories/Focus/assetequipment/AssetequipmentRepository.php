<?php

namespace App\Repositories\Focus\assetequipment;

use App\Models\assetequipment\Assetequipment;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;

/**
 * Class ProductcategoryRepository.
 */
class AssetequipmentRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Assetequipment::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        
       $q=$this->query();
      // $q->when(!request('rel_type'), function ($q) {
           // return $q->where('c_type', '=',request('rel_type',0));
        //});
       //$q->when(request('rel_type'), function ($q) {
           // return $q->where('rel_id', '=',request('rel_id',0));
       // });

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
        // format values
        foreach ($input as $key => $value) {
            if (array_search($key, ['purchase_date', 'warranty_expiry_date'])) {
                $input[$key] = date_for_database($value);
            } elseif (array_search($key, ['cost', 'qty'])) {
                $input[$key] = (float) $value;
            }
        }

        $eqmnt = Assetequipment::create($input);        
        if ($eqmnt->id) return $eqmnt->id;

        throw new GeneralException('Error Creating Assetequipment');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Assetequipment $assetequipment, array $input)
    {
        $input['purchase_date'] = date_for_database($input['purchase_date']);
        $input['warranty_expiry_date'] = date_for_database($input['warranty_expiry_date']);
        $input['cost'] = numberClean($input['cost']);
        $input['qty'] = numberClean($input['qty']);
        $input = array_map( 'strip_tags', $input);
    	if ($assetequipment->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.assetequipments.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete(Assetequipment $assetequipment)
    {
        if ($assetequipment->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.assetequipments.delete_error'));
    }
}
