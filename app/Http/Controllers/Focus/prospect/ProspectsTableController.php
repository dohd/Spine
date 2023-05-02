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

                $name = $prospect->name == null ? '_____________' : $prospect->name;
                return $name;
            })
            ->addColumn('email', function ($prospect) {
                $client_email = $prospect->email == null ? '_____________' : $prospect->email ;

                return $client_email;
            })
            ->addColumn('industry', function ($prospect) {
                $client_industry = $prospect->industry == null ? '_____________' : $prospect->industry;

                return $client_industry;
            })
            ->addColumn('region', function ($prospect) {
                $client_region = $prospect->region == null ? '_____________': $prospect->region;

                return $client_region;
            })
            ->addColumn('company', function ($prospect) {
                $client_company = $prospect->company == null ? '_____________' : $prospect->company;

                return $client_company;
            })
            ->addColumn('phone', function ($prospect) {
                $phone = $prospect->phone == null ? '_____________' : $prospect->phone ;

                return $phone;
            })
            // ->addColumn('category', function ($prospect) {
            //     $category = $prospect->category;

            //     return $category;
            // })
            
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
