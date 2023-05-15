<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

namespace App\Http\Controllers\Focus\prospectcallresolved;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\prospectcallresolved\ProspectCallResolvedRepository;

/**
 * Class BranchTableController.
 */
class ProspectsCallResolvedTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $prospectcallresolved;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(ProspectCallResolvedRepository $prospectcallresolved)
    {

        $this->prospectcallresolved = $prospectcallresolved;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->prospectcallresolved->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('title', function ($prospectcallresolved) {

                $title = $prospectcallresolved->prospect->title == null ? '-----' : $prospectcallresolved->prospect->title;
                return $title;
            })
            ->addColumn('company', function ($prospectcallresolved) {

                $company = $prospectcallresolved->prospect->company == null ? '-----' : $prospectcallresolved->prospect->company;
                return $company;
            })

            ->addColumn('industry', function ($prospectcallresolved) {
                $client_industry = $prospectcallresolved->prospect->industry == null ? '-----' : $prospectcallresolved->prospect->industry;

                return $client_industry;
            })
            // ->addColumn('name', function ($prospectcallresolved) {
            //     $name = $prospectcallresolved->prospect->contact_person == null ? '-----' : $prospectcallresolved->prospect->contact_person;

            //     return $name;
            // })
            // ->addColumn('phone', function ($prospectcallresolved) {
            //     $phone = $prospectcallresolved->prospect->phone == null ? '-----' : $prospectcallresolved->prospect->phone;

            //     return $phone;
            // })
            ->addColumn('region', function ($prospectcallresolved) {
                $client_region = $prospectcallresolved->prospect->region == null ? '-----' : $prospectcallresolved->prospect->region;

                return $client_region;
            })
            ->addColumn('follow_up', function ($prospectcallresolved) {
              
                $status = $prospectcallresolved->prospect->call_status;
                $show = true;
                if($status == 'called'){
                    $show = true;
                }
                else if ($status == 'callednotavailable'){
                    $show = true;
                }
                else{
                    $show = false;
                }

                $text = "";
                $openstatus = $prospectcallresolved->prospect->status;
                if($openstatus == 'open'){
                    $text = "Follow up";
                }else{
                    $text = "Show details";
                }
                
                return $show? '<a id="follow" href="javascript:void(0)" class="btn btn-primary follow" data-id="' . $prospectcallresolved->prospect->id . '" >
                         '.$text.'
                         </a>': '<a id="call" href="javascript:void(0)" class="btn btn-primary" data-id="' . $prospectcallresolved->prospect_id . '" call-id="'.$prospectcallresolved->call_id.'" data-toggle="tooltip"  title="Call" >
                         <i  class="fa fa-vcard"></i>
                                  </a>';
            })
            ->addColumn('reminder_date', function ($prospectcallresolved) {
                $date = $prospectcallresolved->reminder_date;
               

                return $date;
            })
            ->addColumn('temperate', function ($prospectcallresolved) {
                $status = $prospectcallresolved->prospect->temperate;
               

                return $status;
            })
            ->addColumn('call_status', function ($prospectcallresolved) {
                $status = $prospectcallresolved->prospect->call_status;
                if ($status == 'notcalled') {
                    $status = "Not called";
                } 
                else if ($status == 'callednotpicked'){
                    $status = "Called Not Picked";
                }
                else if($status == 'calledrescheduled') {
                    $status = "Call Rescheduled";
                }
                else if($status == 'callednotavailable') {
                    $status = "Called Not Available";
                }
                else  {
                    $status = "Called";
                }

                return $status;
            })
            ->addColumn('status', function ($prospectcallresolved) {
                $status = $prospectcallresolved->prospect->status;
                if ($status == 'open') {
                    $status = "Open";
                } else if ($status == 'won') {
                    $status = "Closed - Won";
                }else{
                    $status = "Closed - Lost";
                }

                return $status;
            })
            ->addColumn('reason', function ($prospectcallresolved) {
                $reason = $prospectcallresolved->prospect->reason == null ? '-----': $prospectcallresolved->prospect->reason;

                return $reason;
            })
            ->addColumn('actions', function ($prospectcallresolved) {
                return $prospectcallresolved->action_buttons;
            })
            ->make(true);
    }

}
