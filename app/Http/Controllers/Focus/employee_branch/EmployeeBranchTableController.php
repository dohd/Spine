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
namespace App\Http\Controllers\Focus\employee_branch;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\employee_branch\EmployeeBranchRepository;

/**
 * Class employee_branchsTableController.
 */
class EmployeeBranchTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var employee_branchRepository
     */
    protected $employee_branch;

    /**
     * contructor to initialize repository object
     * @param employee_branchRepository $employee_branch ;
     */
    public function __construct(EmployeeBranchRepository $employee_branch)
    {
        $this->employee_branch = $employee_branch;
    }

    /**
     * This method return the data of the model
     * @param Manageemployee_branchRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        //
        $core = $this->employee_branch->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($employee_branch) {
                  return $employee_branch->name;
            })
            ->addColumn('note', function ($employee_branch) {
                return $employee_branch->note;
            })
            ->addColumn('actions', function ($employee_branch) {
                return  $employee_branch->action_buttons;
            })
            ->make(true);
    }
}
