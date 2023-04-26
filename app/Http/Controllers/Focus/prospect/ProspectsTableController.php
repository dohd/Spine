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

namespace App\Http\Controllers\Focus\prospect;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\prospect\ProspectRepository;

/**
 * Class BranchTableController.
 */
class ProspectsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $prospect;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(ProspectRepository $prospect)
    {

        $this->prospect = $prospect;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->prospect->getForDataTable();
       
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($prospect) {
                return $prospect->name;
            })
            ->addColumn('email', function ($prospect) {
                $client_email = $prospect->email;

                return $client_email;
            })
            ->addColumn('industry', function ($prospect) {
                $client_industry = $prospect->industry;

                return $client_industry;
            })
            ->addColumn('region', function ($prospect) {
                $client_region = $prospect->region;

                return $client_region;
            })
            ->addColumn('company', function ($prospect) {
                $client_company = $prospect->company;

                return $client_company;
            })
            ->addColumn('phone', function ($prospect) {
                $phone = $prospect->phone;

                return $phone;
            })
            ->addColumn('previous_date', function ($prospect) {
                $date =$prospect->remarks()->get()->count() > 1 ?  $prospect->remarks()->skip(1)
                ->take(1)
                ->first()->reminder_date : $prospect->remarks()->first()->reminder_date ;
                //$date =$prospect->remarks()->first()->reminder_date;
               
                return $date;
            })
            ->addColumn('reminder_date', function ($prospect) {
                $date =$prospect->remarks()->first()->reminder_date;
               
                return $date;
            })
            ->addColumn('remarks', function ($prospect) {
                $remark =$prospect->remarks()->first()->remarks;

                return $remark;
            })
            ->addColumn('follow_up', function ($prospect) {
             
                return '<a id="follow" href="javascript:void(0)" class="btn btn-primary" data-id="' . $prospect->id . '" data-toggle="tooltip"  title="FollowUp" >
                <i  class="fa fa-vcard"></i>
                         </a>';
            })
            ->addColumn('status', function ($prospect) {
                $status = $prospect->status;
                if ($status == 0) {
                    $status = "Open";
                } else {
                    $status = "Closed";
                }

                return $status;
            })
            ->addColumn('created_at', function ($prospect) {
                return dateFormat($prospect->created_at);
            })
            ->addColumn('actions', function ($prospect) {
                return $prospect->action_buttons;
            })
            ->make(true);
    }
}
