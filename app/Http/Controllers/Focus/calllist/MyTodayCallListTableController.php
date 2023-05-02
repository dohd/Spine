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
use App\Repositories\Focus\calllist\CallListRepository;

/**
 * Class BranchTableController.
 */
class MyTodayCallListTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $calllist;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(CallListRepository $calllist)
    {

        $this->calllist = $calllist;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->calllist->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($calllist) {
                return $calllist->name;
            })
            ->addColumn('email', function ($calllist) {
                $email = $calllist->email;

                return $email;
            })
            ->addColumn('phone', function ($calllist) {
                $phone = $calllist->phone;

                return $phone;
            })
            ->addColumn('company', function ($calllist) {
                $company = $calllist->company;

                return $company;
            })
            ->addColumn('industry', function ($calllist) {
                $industry = $calllist->industry;

                return $industry;
            })
            ->addColumn('region', function ($calllist) {
                $region = $calllist->region;

                return $region;
            })
            ->addColumn('call_status', function ($calllist) {
                $call_status = $calllist->call_status;
                if ($call_status == 0) {
                    $call_status = "Not Called";
                } else {
                    $call_status = "Called";
                }

                return $call_status;
            })
            ->addColumn('call_prospect', function ($calllist) {
                return '<a id="call" href="javascript:void(0)" class="btn btn-primary" data-id="' . $calllist->id . '" data-toggle="tooltip"  title="Call" >
                <i  class="fa fa-vcard"></i>
                         </a>';
            })
            
            ->addColumn('actions', function ($calllist) {
                return $calllist->action_buttons;
            })
            ->make(true);
    }

}
