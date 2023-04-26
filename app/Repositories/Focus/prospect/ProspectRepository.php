<?php

namespace App\Repositories\Focus\prospect;

use App\Models\prospect\Prospect;
use App\Exceptions\GeneralException;
use App\Repositories\Focus\remark\RemarkRepository;
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


    //Remark repository

    private $remark;

    public function __construct(RemarkRepository $remark)
    {
        $this->remark = $remark;
    }
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
    public function create(array $data, array $remark)
    {
        
       
        $result = Prospect::create($data);
        
        $remark['reminder_date'] = date_for_database($remark['reminder_date']);
        $remark['recepient'] =$result->name ;
        $remark['prospect_id'] = $result->id;
        unset($remark['name']);
       
        $this->remark->create($remark);
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
    public function update(Prospect $prospect, array $data,array $remark)
    {
       
    //     DB::beginTransaction();
    //    // $data['reminder_date'] = date_for_database($data['reminder_date']);
    //     $result = $prospect->update($data);

       // dd($prospect);
        $remark['reminder_date'] = date_for_database($remark['reminder_date']);
        unset($remark['name']);
       // $this->remark->update(,$remark);

        // if ($result) {
        //     DB::commit();
        //     return true;
        // }

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