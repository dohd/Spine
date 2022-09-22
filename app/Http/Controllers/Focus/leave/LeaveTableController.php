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
namespace App\Http\Controllers\Focus\leave;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\leave\LeaveRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class LeaveTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var LeaveRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param LeaveRepository $repository ;
     */
    public function __construct(LeaveRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->repository->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->addColumn('employee', function ($leave) {
                $employee = $leave->employee;
                if ($employee) 
                return $employee->first_name . ' ' . $employee->last_name;
            })
            ->addColumn('leave_category', function ($leave) {
                $category = $leave->leave_category;
                if ($category) 
                return $category->title;
            })
            ->addColumn('start_date', function ($leave) {
                return dateFormat($leave->start_date);
            })
            ->addColumn('end_date', function ($leave) {
                return dateFormat($leave->end_date);
            })
            ->addColumn('actions', function ($leave) {
                return $leave->action_buttons;
            })
            ->make(true);
    }
}
