<?php

namespace App\Repositories\Focus\lead;

use DB;
use Carbon\Carbon;
use App\Models\lead\Lead;
use App\Models\Access\User\User;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\Rose;

/**
 * Class ProductcategoryRepository.
 */
class LeadRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Lead::class;

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

         //$employees = @$input['employees'];
       // unset($input['employees']);
        $input['date_of_request'] = date_for_database($input['date_of_request']);
       
      
       $result = Lead::create($input);

        if ($result) {
            
            $message = array('title' =>'Leads  - ' . $result->title, 'icon' => 'fa-bullhorn', 'background' => 'bg-success', 'data' => $input['note']);

            if ($input['employee_id']) {
                $users = User::where('id', $input['employee_id'])->get();
                \Illuminate\Support\Facades\Notification::send($users, new Rose('', $message));
            } else {
                $notification = new Rose(auth()->user(), $message);
                auth()->user()->notify($notification);
            }
            return $result;
        }

        throw new GeneralException('Error Creating Branch');




    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Branch $branch, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($branch->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete(Branch $branch)
    {
        if ($branch->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}
