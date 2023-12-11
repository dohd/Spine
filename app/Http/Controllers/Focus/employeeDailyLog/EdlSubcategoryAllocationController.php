<?php

namespace App\Http\Controllers\Focus\employeeDailyLog;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\Access\User\User;
use App\Models\department\Department;
use App\Models\employeeDailyLog\EdlSubcategoryAllocation;
use App\Models\employeeDailyLog\EmployeeTaskSubcategories;
use App\Models\hrm\HrmMeta;
use App\Models\reconciliation\Reconciliation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class EdlSubcategoryAllocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (Auth::user()->hasPermission('allocate-edl-categories') === false){
            return redirect()->back();
        }

        $employees = User::select(
            'id',
            DB::raw('CONCAT(first_name, " ", last_name) AS employee_name'),
        )->get();

        if ($request->ajax()) {

            $edlSubcategoryAllocations = EdlSubcategoryAllocation::
            join('users', 'edl_subcategory_allocations.employee', '=', 'users.id')
                ->select(
                    'users.id as employeeid',
                    DB::raw('CONCAT(first_name, " ", last_name) AS employee'),
                    'allocations'
                );

            return Datatables::of($edlSubcategoryAllocations->get())
                ->addColumn('allocations', function ($edlCatAlloc){

                    $allocations = json_decode($edlCatAlloc->allocations);

                    $allocationString = '';

                    foreach ($allocations as $alloc){
                        $allocName = EmployeeTaskSubcategories::where('id', $alloc)->first()->name;
                        $allocFrequency = EmployeeTaskSubcategories::where('id', $alloc)->first()->frequency;

                        $allocationLine = '<p>' . $allocName . " | " . $allocFrequency . '</p> ';

                        $allocationString .= $allocationLine;
                    }

                    return $allocationString;

                })
                ->addColumn('action', function ($edlCatAlloc){

                    $route = route('biller.edl-subcategory-allocations.create',$edlCatAlloc->employeeid);

                    return '<a href="'.$route.'" class="btn btn-secondary mr-1 round">View</a>';

                })
                ->rawColumns(['allocations', 'action'])
                ->make(true);

        }


        return new ViewResponse('focus.employeeDailyLog.assignSubcategories.index', compact('employees'));
    }


    public function employeeIndex(Request $request)
    {
        if (Auth::user()->hasPermission('create-daily-logs') === false){
            return redirect()->back();
        }

        $employees = User::select(
            'id',
            DB::raw('CONCAT(first_name, " ", last_name) AS employee_name'),
        )->get();

        if ($request->ajax()) {

            $edlSubcategoryAllocations = EdlSubcategoryAllocation::where('employee', Auth::user()->id)
                ->join('users', 'edl_subcategory_allocations.employee', '=', 'users.id')
                ->select(
                    'users.id as employeeid',
                    DB::raw('CONCAT(first_name, " ", last_name) AS employee'),
                    'allocations'
                );

            return Datatables::of($edlSubcategoryAllocations->get())
                ->addColumn('allocations', function ($edlCatAlloc){

                    $allocations = json_decode($edlCatAlloc->allocations);

                    $allocationString = '';

                    foreach ($allocations as $alloc){
                        $allocName = EmployeeTaskSubcategories::where('id', $alloc)->first()->name;
                        $allocFrequency = EmployeeTaskSubcategories::where('id', $alloc)->first()->frequency;

                        $allocationLine = '<p>' . $allocName . " | " . $allocFrequency . '</p> ';

                        $allocationString .= $allocationLine;
                    }

                    return $allocationString;

                })
                ->rawColumns(['allocations'])
                ->make(true);

        }


        return new ViewResponse('focus.employeeDailyLog.assignSubcategories.employeeIndex', compact('employees'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $employeeId = 333333)
    {

        if (Auth::user()->hasPermission('allocate-edl-categories') === false){
            return redirect()->back();
        }



        $employeeId = $employeeId === 333333 ? request('employee') : $employeeId;

        $edlSubcategoryAllocation = EdlSubcategoryAllocation::where('employee', $employeeId)->first();
        $allocations = [];

        if(!empty($edlSubcategoryAllocation)){
            $allocations = json_decode($edlSubcategoryAllocation->allocations);
        }

        $departments = Department::orderBy('name', 'asc')->select('name', 'id')->get();

        $edlSubcats = [];

        foreach ($departments as $dept){

            $subcats = Department::where('name', $dept['name'])->first()->edlTaskSubcategories->pluck('name', 'id')->toArray();
            sort($subcats);

            $edlSubcatDetails = [];
            foreach ($subcats as $scat){

                $subcategoryId = EmployeeTaskSubcategories::where('name', $scat)->where('department', Department::where('name', $dept['name'])->first()->id)->first()->id;
                $details = [
                    'name' => $scat,
                    'id' => $subcategoryId
                ];

                array_push($edlSubcatDetails, $details);
            }

            array_push($edlSubcats, $edlSubcatDetails);
        }

        $deptNames = Department::orderBy('name', 'asc')->pluck('name');
        $deptEdlSubcategories = array_combine($deptNames->toArray(), $edlSubcats);

        $employee = [
            'details' => User::find($employeeId),
            'department' => Department::where('id', HrmMeta::where('user_id', $employeeId)->first()->department_id)->first()->name,
        ];

        return new ViewResponse('focus.employeeDailyLog.assignSubcategories.create', compact( 'departments','deptEdlSubcategories', 'employee', 'allocations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {

        if (Auth::user()->hasPermission('allocate-edl-categories') === false){
            return redirect()->back();
        }

        $allocations = array_values($request->except(['_token', 'name', 'employeeId', 'department']));

        try {
            DB::beginTransaction();

            $edlSubcategoryAllocation = EdlSubcategoryAllocation::where('employee', request('employeeId'))->first();

            if(empty($edlSubcategoryAllocation)){
                $edlSubcategoryAllocation = new EdlSubcategoryAllocation();
            }

            $edlSubcategoryAllocation->employee = request('employeeId');
            $edlSubcategoryAllocation->allocations = json_encode($allocations);

            $edlSubcategoryAllocation->save();

            DB::commit();
        }
        catch (Exception $e){
            DB::rollBack();
            return redirect()->back()->with('flash_error', 'SQL ERROR : ' . $e->getMessage());
        }

        return new RedirectResponse(route('biller.edl-subcategory-allocations.index'), ['flash_success' => 'Task Allocations Updated Successfully!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($employeeId)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }



}
