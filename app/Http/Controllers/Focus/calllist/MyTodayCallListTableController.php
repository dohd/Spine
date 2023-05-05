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

namespace App\Http\Controllers\Focus\calllist;

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
            ->addColumn('title', function ($prospectcalllist) {

                return $prospectcalllist->prospect->title == null ? '-----' : $prospectcalllist->prospect->title;
            })
            ->addColumn('company', function ($prospectcalllist) {
                return $prospectcalllist->prospect->company == null ? '-----' : $prospectcalllist->prospect->company;
            })
            ->addColumn('industry', function ($prospectcalllist) {
                return $prospectcalllist->prospect->industry == null ? '-----' : $prospectcalllist->prospect->industry;
            })
            // ->addColumn('email', function ($prospectcalllist) {
            //     return $prospectcalllist->prospect->email == null ? '-----':$prospectcalllist->prospect->email;
            // })
            ->addColumn('call_prospect', function ($prospectcalllist) {
                $hide = true;
                if($prospectcalllist->prospect->call_status){
                    $hide = false;
                }else{
                    $hide = true;
                }
                return $hide? '<a id="call" href="javascript:void(0)" class="btn btn-primary" data-id="' . $prospectcalllist->prospect_id . '" data-toggle="tooltip"  title="Call" >
                <i  class="fa fa-vcard"></i>
                         </a>':'---';
            })
            ->addColumn('phone', function ($prospectcalllist) {
                return $prospectcalllist->prospect->phone == null ? '-----':$prospectcalllist->prospect->phone;
            })
            
            ->addColumn('region', function ($prospectcalllist) {
                return $prospectcalllist->prospect->region == null ? '-----':$prospectcalllist->prospect->region;
            })
            ->addColumn('call_status', function ($prospectcalllist) {
                
             
                return $prospectcalllist->prospect->call_status == 0 ? 'Not Called':'Called';
            })
            ->addColumn('call_date', function ($prospectcalllist) {
                
                $call_date = $prospectcalllist->prospect->call_date == null ? '-----':$prospectcalllist->prospect->call_date;
               

                return dateFormat( $call_date);
            })
            
            
            // ->addColumn('actions', function ($prospectcalllist) {
            //     return $prospectcalllist->action_buttons;
            // })
            ->make(true);
        
    }

}
