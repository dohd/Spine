<?php

namespace App\Http\Controllers\Focus\employeeDailyLog;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\department\Department;
use App\Models\employeeDailyLog\EmployeeDailyLog;
use App\Models\employeeDailyLog\EmployeeTasks;
use App\Models\employeeDailyLog\EmployeeTaskSubcategories;
use App\Models\employeeDailyLog\EdlSubcategoryAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class EmployeeTaskSubcategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (Auth::user()->hasPermission('manage-edl-categories') === false){
            return redirect()->back();
        }

        if ($request->ajax()) {

            $taskSubcategories = EmployeeTaskSubcategories::join('departments', 'employee_task_subcategories.department', '=', 'departments.id')
                ->select(
                    'employee_task_subcategories.id as id',
                    'employee_task_subcategories.name as name',
                    'departments.name as department',
                    'employee_task_subcategories.frequency'
                )
                ->get();

            return Datatables::of($taskSubcategories)
                ->addColumn('action', function ($model) {

                    $route = route('biller.employee-task-subcategories.edit', $model->id);
                    $routeDelete = route('biller.employee-task-subcategories.destroy', $model->id);

                    return '<a href="'.$route.'" class="btn btn-secondary round mr-1">Edit</a>'
                        . '<a href="' .$routeDelete . '" 
                            class="btn btn-danger round" data-method="delete"
                            data-trans-button-cancel="' . trans('buttons.general.cancel') . '"
                            data-trans-button-confirm="' . trans('buttons.general.crud.delete') . '"
                            data-trans-title="' . trans('strings.backend.general.are_you_sure') . '" 
                            data-toggle="tooltip" 
                            data-placement="top" 
                            title="Delete"
                            >
                                <i  class="fa fa-trash"></i>
                            </a>';

                })
                ->rawColumns(['action'])
                ->make(true);

        }


        return new ViewResponse('focus.employeeDailyLog.taskSubcategories.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (Auth::user()->hasPermission('create-edl-categories') === false){
            return redirect()->back();
        }

        $departments = Department::orderBy('name', 'asc')->get()->pluck('name');
        $frequency = ["Daily", "Weekly", "Bi-Weekly", "Monthly", "Quarterly", "Semi-Annually", "Annual"];

        return new ViewResponse('focus.employeeDailyLog.taskSubcategories.create', compact('departments', 'frequency'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Auth::user()->hasPermission('create-edl-categories') === false){
            return redirect()->back();
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'department' => ['required', 'string'],
            'frequency' => ['required', 'string'],
        ]);

        try {
            DB::beginTransaction();

            $taskSubCategory = new EmployeeTaskSubcategories();

            $validated['department'] = Department::where('name', $validated['department'])->first()->id;
            $taskSubCategory->fill($validated);
            $taskSubCategory->save();

            DB::commit();

        } catch (\Exception $e){
            DB::rollBack();
            return redirect()->back()->with('flash_error', $e->getMessage());
        }

        return new RedirectResponse(route('biller.employee-task-subcategories.index'), ['flash_success' => 'Task Subcategory Saved Successfully!']);
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
    public function edit(int $id)
    {
        if (Auth::user()->hasPermission('edit-edl-categories') === false){
            return redirect()->back();
        }

        $taskSubcategory = EmployeeTaskSubcategories::where('id', $id)->first();
        $taskSubcategory->department =  Department::where('id', $taskSubcategory->department)->first()->name;

        $departments = Department::orderBy('name', 'asc')->get()->pluck('name');
        $frequency = ["Daily", "Weekly", "Bi-Weekly", "Monthly", "Quarterly", "Semi-Annually", "Annual"];


        return new ViewResponse('focus.employeeDailyLog.taskSubcategories.edit', compact('taskSubcategory','departments', 'frequency'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        if (Auth::user()->hasPermission('edit-edl-categories') === false){
            return redirect()->back();
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'department' => ['required', 'string'],
            'frequency' => ['required', 'string'],
        ]);

        try {

            DB::beginTransaction();

            $taskSubcategory = EmployeeTaskSubcategories::where('id', $id)->first();

            $validated['department'] = Department::where('name', $validated['department'])->first()->id;
            $taskSubcategory->fill($validated);

            $taskSubcategory->save();

            DB::commit();
        } catch (\Exception $e){

            DB::rollBack();
            return redirect()->back()->with('flash_error', $e->getMessage());
        }

        return new RedirectResponse(route('biller.employee-task-subcategories.index'), ['flash_success' => 'Task Subcategory Updated Successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Auth::user()->hasPermission('delete-edl-categories') === false){
            return redirect()->back();
        }

        $allocations = EdlSubcategoryAllocation::all()->pluck('allocations');

        foreach ($allocations as $alloc) {
            
            $allocArray = json_decode($alloc);

            if (in_array($id, $allocArray)) {
                return redirect()->route('biller.employee-task-subcategories.index')->with('flash_error', 'Cannot Delete This Task Category as it is Currently Allocated');
            }
        }




        if (empty(EmployeeTaskSubcategories::where('id', $id)->employeeTasks)){

            EmployeeTaskSubcategories::where('id', $id)->delete();
        }
        else {
            return redirect()->route('biller.employee-task-subcategories.index')->with('flash_error', 'Cannot Delete This Subcategory as it is Already in Use');
        }

        return new RedirectResponse(route('biller.employee-task-subcategories.index'), ['flash_success' => 'Task Subcategory Deleted Successfully!']);
    }



    private function checkRights($canDoIt){
        if (!$canDoIt){
            return redirect()->back();
        }
    }
}
