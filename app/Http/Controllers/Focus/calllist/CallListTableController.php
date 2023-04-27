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
class CallListsTableController extends Controller
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
                $client_email = $calllist->email;

                return $client_email;
            })
            ->addColumn('industry', function ($calllist) {
                $client_industry = $calllist->industry;

                return $client_industry;
            })
            ->addColumn('region', function ($calllist) {
                $client_region = $calllist->region;

                return $client_region;
            })
            ->addColumn('company', function ($calllist) {
                $client_company = $calllist->company;

                return $client_company;
            })
            ->addColumn('phone', function ($calllist) {
                $phone = $calllist->phone;

                return $phone;
            })
            ->addColumn('previous_date', function ($calllist) {
                $date =$calllist->remarks()->get()->count() > 1 ?  $calllist->remarks()->skip(1)
                ->take(1)
                ->first()->reminder_date : $calllist->remarks()->first()->reminder_date ;
                //$date =$calllist->remarks()->first()->reminder_date;
               
                return $date;
            })
            ->addColumn('reminder_date', function ($calllist) {
                $date =$calllist->remarks()->first()->reminder_date;
               
                return $date;
            })
            ->addColumn('remarks', function ($calllist) {
                $remark =$calllist->remarks()->first()->remarks;

                return $remark;
            })
            ->addColumn('calllist_status', function ($calllist) {
                $state =$calllist->calllist_status;

                return $state;
            })
            ->addColumn('follow_up', function ($calllist) {
                return '<a id="follow" href="javascript:void(0)" class="btn btn-primary" data-id="' . $calllist->id . '" data-toggle="tooltip"  title="FollowUp" >
                <i  class="fa fa-vcard"></i>
                         </a>';
            })
            ->addColumn('status', function ($calllist) {
                $status = $calllist->status;
                if ($status == 0) {
                    $status = "Open";
                } else {
                    $status = "Closed";
                }

                return $status;
            })
            ->addColumn('created_at', function ($calllist) {
                return dateFormat($calllist->created_at);
            })
            ->addColumn('actions', function ($calllist) {
                return $calllist->action_buttons;
            })
            ->make(true);
    }

}
