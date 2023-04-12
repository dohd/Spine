<?php

namespace App\Repositories\Focus\prospect;

use App\Models\prospect\Prospect;
use App\Exceptions\GeneralException;

use App\Repositories\BaseRepository;
use DB;


/**
 * Class ProductcategoryRepository.
 */
class ProspectRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Prospect::class;

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
    public function create(array $data)
    {
        $data['reminder_date'] = date_for_database($data['reminder_date']);
        $result = Prospect::create($data);
        return $result;

        throw new GeneralException('Error Creating Prospect');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param \App\Models\Prospect $prospect
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Prospect $prospect, array $data)
    {
        DB::beginTransaction();
        $data['reminder_date'] = date_for_database($data['reminder_date']);
        $result = $prospect->update($data);
      
       

        if ($result) {
            DB::commit();
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\prospect\Prospect $prospect
     * @throws GeneralException
     * @return bool
     */
    public function delete(Prospect $prospect)
    {
       
     
            
        if ($prospect->delete()) return true;
        
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}