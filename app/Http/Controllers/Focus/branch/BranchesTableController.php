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

namespace App\Http\Controllers\Focus\branch;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\branch\BranchRepository;
use App\Http\Requests\Focus\branch\ManageBranchRequest;

/**
 * Class BranchTableController.
 */
class BranchesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $branch;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(BranchRepository $branch)
    {

        $this->branch = $branch;
    }

    /**
     * This method return the data of the model
     * @param ManageProductcategoryRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageBranchRequest $request)
    {
        $core = $this->branch->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('customer', function ($branch) {
                if (isset($branch->customer)) return $branch->customer->company;
                return;
            })
            ->addColumn('name', function ($branch) {
                return '<a class="font-weight-bold" href="' . route('biller.products.index') . '?rel_type=' . $branch->id . '&rel_id=' . $branch->id . '">' . $branch->name . '</a>';
            })

            // ->addColumn('location', function ($branch) {
            //return $branch->location;
            //  })

            ->addColumn('created_at', function ($branch) {
                return dateFormat($branch->created_at);
            })
            ->addColumn('actions', function ($branch) {
                return $branch->action_buttons;
                // return '<a class="btn btn-purple round" href="' . route('biller.branches.index') . '?rel_type=' . $branch->id . '&rel_id=' . $branch->id . '" title="List"><i class="fa fa-list"></i></a>' . $branch->action_buttons;
            })
            ->make(true);
    }
}
