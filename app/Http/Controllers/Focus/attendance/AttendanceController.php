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

namespace App\Http\Controllers\Focus\attendance;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\Access\User\User;
use App\Models\attendance\Attendance;
use App\Models\Company\Company;
use App\Repositories\Focus\attendance\AttendanceRepository;
use DateTime;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * variable to store the repository object
     * @var AttendanceRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param AttendanceRepository $repository ;
     */
    public function __construct(AttendanceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $employees = User::get(['id', 'first_name', 'last_name']);
        
        return new ViewResponse('focus.attendances.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $employees = User::get(['id', 'first_name', 'last_name']);
        $company = Company::find(auth()->user()->ins);

        return view('focus.attendances.create', compact('employees', 'company'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->repository->create($request->except('_token'));

        return new RedirectResponse(route('biller.attendances.index'), ['flash_success' => 'Attendance Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Attendance $attendance
     * @return \Illuminate\Http\Response
     */
    public function edit(Attendance $attendance)
    {
        $company = Company::find(auth()->user()->ins);

        return view('focus.attendances.edit', compact('employees', 'company'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Attendance $attendance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Attendance $attendance)
    {
        $this->repository->update($attendance, $request->except('_token'));

        return new RedirectResponse(route('biller.attendances.index'), ['flash_success' => 'Attendance Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Attendance $attendance
     * @return \Illuminate\Http\Response
     */
    public function destroy(Attendance $attendance)
    {
        $this->repository->delete($attendance);

        return new RedirectResponse(route('biller.attendances.index'), ['flash_success' => 'Attendance Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  Attendance $attendance
     * @return \Illuminate\Http\Response
     */
    public function show(Attendance $attendance)
    {
        return view('focus.attendances.view', compact('attendance'));
    }

    /**
     * Day Attendance Count
     */
    public function day_attendance(Request $request)
    {
        $attendances = Attendance::whereMonth('date', $request->month)
            ->get(['employee_id', 'date'])->toArray();

        $day_employee_group = array_reduce($attendances, function ($init, $curr) {
            $d = (new DateTime($curr['date']))->format('j');
            $key_exists = in_array($d, array_keys($init));
            if (!$key_exists) $init[$d] = array();
            $init[$d][] = $curr['employee_id'];
            
            return $init;
        }, []);

        $day_attendance = array();
        foreach ($day_employee_group as $key => $val) {
            $day_attendance[] = array(
                'day' => $key,
                'count' => count(array_unique($val))
            );
        }

        $employee_count = User::count();

        return response()->json(compact('employee_count', 'day_attendance'));
    }

    /**
     * Attendance employees
     */
    public function employees_attendance(Request $request)
    {
        $attendances = Attendance::whereMonth('date', $request->month)
            ->whereDay('date', $request->day)
            ->with(['employee' => function ($q) {
                $q->select('id', 'first_name', 'last_name');
            }])
            ->get();

        return response()->json($attendances);
    }
}
