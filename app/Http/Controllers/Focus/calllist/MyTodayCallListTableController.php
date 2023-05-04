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

namespace App\Http\Controllers\Focus\prospectcalllist;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\prospect_call_list\ProspectCallListRepository;

/**
 * Class BranchTableController.
 */
class MyTodayCallListTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $prospectcalllist;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(ProspectCallListRepository $prospectcalllist)
    {

        $this->prospectcalllist = $prospectcalllist;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->prospectcalllist->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('prospect_id', function ($prospectcalllist) {
                return $prospectcalllist->prospect_id;
            })
            ->addColumn('call_date', function ($prospectcalllist) {
                $call_date = $prospectcalllist->call_date;
               

                return $call_date;
            })
            ->addColumn('call_prospect', function ($prospectcalllist) {
                return '<a id="call" href="javascript:void(0)" class="btn btn-primary" data-id="' . $prospectcalllist->prospect_id . '" data-toggle="tooltip"  title="Call" >
                <i  class="fa fa-vcard"></i>
                         </a>';
            })
            
            ->addColumn('actions', function ($prospectcalllist) {
                return $prospectcalllist->action_buttons;
            })
            ->make(true);
        // return Datatables::of($core)
        //     ->escapeColumns(['id'])
        //     ->addIndexColumn()
        //     ->addColumn('name', function ($prospectcalllist) {
        //         return $prospectcalllist->name;
        //     })
        //     ->addColumn('email', function ($prospectcalllist) {
        //         $email = $prospectcalllist->email;

        //         return $email;
        //     })
        //     ->addColumn('phone', function ($prospectcalllist) {
        //         $phone = $prospectcalllist->phone;

        //         return $phone;
        //     })
        //     ->addColumn('company', function ($prospectcalllist) {
        //         $company = $prospectcalllist->company;

        //         return $company;
        //     })
        //     ->addColumn('industry', function ($prospectcalllist) {
        //         $industry = $prospectcalllist->industry;

        //         return $industry;
        //     })
        //     ->addColumn('region', function ($prospectcalllist) {
        //         $region = $prospectcalllist->region;

        //         return $region;
        //     })
        //     ->addColumn('call_status', function ($prospectcalllist) {
        //         $call_status = $prospectcalllist->call_status;
        //         if ($call_status == 0) {
        //             $call_status = "Not Called";
        //         } else {
        //             $call_status = "Called";
        //         }

        //         return $call_status;
        //     })
        //     ->addColumn('call_prospect', function ($prospectcalllist) {
        //         return '<a id="call" href="javascript:void(0)" class="btn btn-primary" data-id="' . $prospectcalllist->id . '" data-toggle="tooltip"  title="Call" >
        //         <i  class="fa fa-vcard"></i>
        //                  </a>';
        //     })
            
        //     ->addColumn('actions', function ($prospectcalllist) {
        //         return $prospectcalllist->action_buttons;
        //     })
        //     ->make(true);
    }

}
