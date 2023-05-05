<?php

namespace App\Repositories\Focus\prospectcallresolved;


use App\Exceptions\GeneralException;
use App\Models\prospect\Prospect;
use App\Models\prospectcallresolved\ProspectCallResolved;
use App\Repositories\BaseRepository;
use DB;


/**
 * Class ProductcategoryRepository.
 */
class ProspectCallResolvedRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = ProspectCallResolved::class;

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
        $data['demo_date'] = date_for_database($data['demo_date']);


        //determine if prospect is hot,warm or cold

        $temperate = '';
        $haserp = $data['erp'];
        $haschallenges = $data['erp_challenges'];
        $wantsdemo = $data['erp_demo'];
        if($haserp){
            if($haschallenges){
                if($wantsdemo){
                    $temperate = 'hot';
                }else{
                    $temperate = 'warm';
                }
            }else{
                if($wantsdemo){
                    $temperate = 'warm';
                }else{
                    $temperate = 'cold';
                }
            }
            
        }else{
            if($wantsdemo){
                $temperate = 'hot';
            }else{
                $temperate = 'cold';
            }
        }

        
        $result = ProspectCallResolved::create($data);
        if($result){
            $id = $data['prospect_id'];
            $prospect = Prospect::find($id);
            if($prospect){
                $prospect->update([
                    'call_status' => 1,
                    'temperate' => $temperate,
                ]);
            }
        }
        return $result;

        throw new GeneralException('Error Creating Prospect');
    }
    public function notpickedcreate(array $data)
    {
        
        $result = ProspectCallResolved::create($data);
        if($result){
            $id = $data['prospect_id'];
            $prospect = Prospect::find($id);
            if($prospect){
                $prospect->update([
                    'call_status' => 'callednotpicked',
                    'temperate' => 'cold',
                ]);
            }
        }
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
    public function update($prospectcallresolved, array $input)
    {
       
        DB::beginTransaction();
        $result = $prospectcallresolved->update($input['data']);
        
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
    public function delete(ProspectCallResolved $prospectcallresolved)
    {   
        if ($prospectcallresolved->delete()) return true;
        
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}