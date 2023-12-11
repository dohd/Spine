<?php

namespace App\Http\Controllers\Focus\employeeDailyLog;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\Access\Permission\Permission;
use App\Models\Access\User\User;
use App\Models\department\Department;
use App\Models\employeeDailyLog\EdlSubcategoryAllocation;
use App\Models\employeeDailyLog\EmployeeDailyLog;
use App\Models\employeeDailyLog\EmployeeTaskSubcategories;
use App\Models\employeeDailyLog\EmployeeTasks;
use App\Models\hrm\HrmMeta;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class EmployeeDailyLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws Exception
     */
    public function index(Request $request)
    {

        if (Auth::user()->hasPermission('manage-daily-logs') === false){
            return redirect()->back();
        }

        $edlEmployeeIds = EmployeeDailyLog::all()->pluck('employee')->unique()->values();
        $edlDates = Auth::user()->hasPermission('manage-daily-logs') ?
            EmployeeDailyLog::all()->pluck('date')->unique()->values() :
            EmployeeDailyLog::where('employee', Auth::user()->id)->pluck('date')->unique()->values();

        $edlDates = $edlDates->toArray();

        usort($edlDates, function ($a, $b) {
            return strtotime($b) - strtotime($a);
        });

        $edlYears = [];
        foreach ($edlDates as $yr){

            $yearValue = (new DateTime($yr))->format('Y');
            array_push($edlYears, $yearValue);
        }
        $edlYears = array_unique($edlYears);

        $employees = [];
        foreach ($edlEmployeeIds as $id){

            $employeeDetails = User::where('id' ,$id)
                ->select(
                    'id',
                    DB::raw('CONCAT(first_name, " ", last_name) as full_name')
                )
                ->get();

            array_push($employees, $employeeDetails[0]->toArray());
        }

        $months = [
            ['label' => 'January', 'value' => 1],
            ['label' => 'February', 'value' => 2],
            ['label' => 'March', 'value' => 3],
            ['label' => 'April', 'value' => 4],
            ['label' => 'May', 'value' => 5],
            ['label' => 'June', 'value' => 6],
            ['label' => 'July', 'value' => 7],
            ['label' => 'August', 'value' => 8],
            ['label' => 'September', 'value' => 9],
            ['label' => 'October', 'value' => 10],
            ['label' => 'November', 'value' => 11],
            ['label' => 'December', 'value' => 12],
        ];
        

        if ($request->ajax()){

            if (Auth::user()->hasPermission('review-daily-logs')){

                $employeeDailyLog = EmployeeDailyLog::
                    join('users', 'employee_daily_logs.employee', '=', 'users.id')
                    ->select(
                        'employee_daily_logs.employee as employee_id',
                        'edl_number',
                        'date',
                        DB::raw('CONCAT(first_name, " ", last_name) AS employee'),
                        'rating'
                    );

                $employeeDailyLog->when(!empty($request->employee), function ($query) use ($request) {
                    $query->where('employee', request('employee'));
                });

            }
            else {

                $employeeDailyLog = EmployeeDailyLog::where('employee', Auth::user()->id)
                    ->join('users', 'employee_daily_logs.employee', '=', 'users.id')
                    ->select(
                        'employee_daily_logs.employee as employee_id',
                        'edl_number',
                        'date',
                        DB::raw('CONCAT(first_name, " ", last_name) AS employee'),
                        'rating'
                    );



            }

            $employeeDailyLog->when(!empty($request->date), function ($query) use ($request) {
                $query->whereDate('date', (new DateTime(request('date')))->format('Y-m-d') );
            });

            $employeeDailyLog->when(!empty($request->month), function ($query) use ($request) {
                $query->whereMonth('date', request('month'));
            });

            $employeeDailyLog->when(!empty($request->year), function ($query) use ($request) {
                $query->whereYear('date', request('year'));
            });



            return Datatables::of($employeeDailyLog->get())
                ->addIndexColumn()
                ->editColumn('employee', function ($edl) {

                    $position = HrmMeta::where('user_id', $edl->employee_id)->first()->position;

                    return '<p>' . $edl->employee . '</p> ' .
                        '<small>' . $position . '</small> ';

                })
                ->addColumn('action', function ($model) {

                    $view = ' <a href="' . route('biller.employee-daily-log.show',$model->edl_number) . '" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="View"><i  class="fa fa-eye"></i></a> ';
                    $edit = ' <a href="' . route('biller.employee-daily-log.edit',$model->edl_number) . '" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="Edit"><i  class="fa fa-pencil"></i></a> ';
                    $delete = '<a href="' . route('biller.employee-daily-log.destroy',$model->edl_number) . '" 
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

                    $edit = Auth::user()->id === $model->employee_id ? $edit : '';
                    $delete = Auth::user()->id === $model->employee_id ? $delete : '';


                    if (Auth::user()->hasPermission('review-daily-logs') === true){

                        $review = '<a href="'. route('biller.edl-remark',$model->edl_number) .'" class="btn btn-bitbucket mr-1 round">Review</a>';

                        return $review . $view . $edit . $delete;
                    }
                    else{

                        return $view . $edit . $delete;
                    }


                })
                ->addColumn('tasks', function ($model) {
                    $taskCount = count($model->tasks);

                    return $taskCount;
                })
                ->addColumn('hours', function ($model) {

                    $employeeTasks = $model->tasks;
                    $hours = 0;

                    foreach ($employeeTasks as $task){
                        $hours += $task['hours'];
                    }

                    return $hours;
                })
                ->rawColumns(['action', 'employee'])
                ->make(true);


        }


        $isReviewer = Auth::user()->hasPermission('review-daily-logs');

        $edlMetrics = $this->edlDashboard();

        return new ViewResponse('focus.employeeDailyLog.index', compact('isReviewer', 'employees', 'edlDates', 'months', 'edlYears', 'edlMetrics'));
    }


    public function edlDashboard(){

        $yesterday = (new DateTime('now'))->sub(new DateInterval('P1D'))->format('Y-m-d');
        //Logs Filled Today
        $filledYesterday = EmployeeDailyLog::where('date', $yesterday)->get()->count();

       //Logs not Filled Today
       $employees = User::all();
       $noOfLoggers = 0;
       foreach ($employees as $emp){

           $user = User::where('id', $emp['id'])->first();

           if ($user->hasPermission('create-daily-logs')){
               $noOfLoggers++;
           }

       }
       $notFilledYesterday = $noOfLoggers - $filledYesterday;

       //Hours Logged today
        $tasksLoggedYesterday = 0;
        $hoursLoggedYesterday = 0;
       $yesterdayLogs = EmployeeDailyLog::where('date', $yesterday)->get();
       foreach ($yesterdayLogs as $log){

           $edlTasks = EmployeeDailyLog::where('edl_number', $log['edl_number'])->first()->tasks;

           $tasksLoggedYesterday += $edlTasks->count();

           foreach ($edlTasks as $task){

               $hoursLoggedYesterday += $task['hours'];
           }
       }

       $yesterdayLogs = EmployeeDailyLog::where('date', $yesterday)->get();
       $yesterdayUnreviewedLogs = 0;

       foreach ($yesterdayLogs as $log){

           if (empty($log['rating']) && empty($log['remarks'])){
               $yesterdayUnreviewedLogs++;
           }
       }


        return compact('filledYesterday', 'notFilledYesterday', 'tasksLoggedYesterday', 'hoursLoggedYesterday', 'yesterdayUnreviewedLogs');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return ViewResponse
     */
    public function create()
    {
        if (Auth::user()->hasPermission('create-daily-logs') === false){
            return redirect()->back();
        }

        $departmentId = HrmMeta::where('user_id', Auth::user()->id)->first()->department_id;

        $taskCats = EmployeeTaskSubcategories::where('department', $departmentId)->get();

        $taskCategories = $this->getTaskCategories();

//        return $taskCategories;

        return new ViewResponse('focus.employeeDailyLog.create', compact('taskCategories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws Exception
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => [
                'required',
                'after_or_equal:' . (new DateTime('now'))->sub(new DateInterval('P7D'))->format('Y-m-d'),
                'before_or_equal:' . (new DateTime('now'))->format('Y-m-d'),
            ],
            'subcategory0' => ['required', 'string', 'max:1000'],
            'hours0' => ['required', 'numeric'],
            'description0' => ['required', 'string', 'max:1000'],
        ]);



        if (Auth::user()->hasPermission('create-daily-logs') === false){
            return redirect()->back();
        }

//        $today = new DateTime('now');
//        $logDate = new DateTime($validated['date']);
//
//        $dateDiff = ($today->diff($logDate))->days;
//
//        if ($dateDiff > 7) {
//            return redirect()->back()->with('flash_error', 'You can Create Logs for only the Past 1 week');
//        }

        if (!empty(EmployeeDailyLog::where('employee', Auth::user()->id)->where('date', (new DateTime($validated['date']))->format('Y-m-d') )->first())){

            return redirect()->back()->with('flash_error', 'You Already Created an EDL for the Date: ' . (new DateTime($validated['date']))->format('D, d M Y'));
        }


        try {
            DB::beginTransaction();

            $employeeDailyLog = new EmployeeDailyLog;

            $employeeDailyLog->edl_number ='EDL-' . Auth::user()->id . '-' . strtoupper(Str::random(4));
            $employeeDailyLog->fill($validated);

            $employeeDailyLog->date = (new DateTime($validated['date']))->format('Y-m-d');

            $employeeDailyLog->employee =  Auth::user()->id;

            $employeeDailyLog->save();


//            return ['egg' => EmployeeTaskSubcategories::where('name', $request['category0'])->first()->id];

            for ($i = 0; $i < 20; $i++) {

                if (!empty($request['subcategory' . $i]) && !empty($request['hours' . $i]) && !empty($request['description' . $i])) {

                    $employeeTask = new EmployeeTasks();

                    $employeeTask->et_number = uniqid('ET' . Auth::user()->id . '-');

                    $employeeTask->edl_number = $employeeDailyLog->edl_number;
                    $employeeTask->category = HrmMeta::where('user_id', Auth::user()->id)->first()->department_id;
                    $employeeTask->subcategory = $request['subcategory' . $i];

                    $employeeTask->hours = $request['hours' . $i];
                    $employeeTask->description = $request['description' . $i];

                    $employeeTask->save();

                }

            }

            $empTasks = $employeeDailyLog->tasks;
            $hours = 0;

            foreach ($empTasks as $task){
                $hours += $task['hours'];
            }

            if ($hours > 15){
                DB::rollBack();
                return redirect()->back()->with('flash_error', 'Total Hours For Your Daily Log Cannot Exceed 14 Hours.');
            } else{
                DB::commit();
            }

        } catch (Exception $e){
            DB::rollBack();
            return redirect()->back()->with('flash_error', 'SQL ERROR : ' . $e->getMessage());
        }

        return new RedirectResponse(route('biller.employee-daily-log.index'), ['flash_success' => 'Daily Log Saved Successfully!']);

    }

    /**
     * Displays the EDL Review page
     * @param $edlNumber
     * @return ViewResponse|\Illuminate\Http\RedirectResponse
     */
    public function makeLogRemark($edlNumber){

        if (Auth::user()->hasPermission('review-daily-logs') === false){
            return redirect()->back();
        }

        $edl = EmployeeDailyLog::where('edl_number', $edlNumber)
            ->join('users', 'employee_daily_logs.employee', '=', 'users.id')
            ->select(
                'edl_number',
                'date',
                DB::raw('CONCAT(first_name, " ", last_name) AS employee'),
                'rating',
                'remarks',
            )
            ->get();


        $edlTasks = EmployeeTasks::where('edl_number', $edlNumber)
            ->join('departments', 'employee_tasks.category', '=', 'departments.id')
            ->join('employee_task_subcategories', 'employee_tasks.subcategory', '=', 'employee_task_subcategories.id')
            ->select(
                'et_number',
                'employee_task_subcategories.name as subcategory',
                'employee_task_subcategories.frequency as frequency',
                'hours',
                'frequency',
                'description'
            )
            ->get();

        $data = [
            "edl" => $edl,
            "edlTasks" => $edlTasks,
        ];

        $ratings = [
            '0 - Rework/ Repetition/ No Attempt',
            '1 - Minimal Attempt',
            '2 - Partially done /Few worked hours',
            '3 - Done but not to Expectations/Longer time',
            '4 - Well done and within perceived timelines',
            '5 - Exceeds Expectations/Extra mile',
        ];

//        return compact('data');

        return new ViewResponse('focus.employeeDailyLog.logRemark', compact('data', 'edlNumber', 'ratings'));
    }


    /**
     * Saves the rating and remark of the EDL
     * @param Request $request
     * @param $edlNumber
     * @return RedirectResponse|\Illuminate\Http\RedirectResponse
     */
    public function storeLogRemark(Request $request, $edlNumber){

        if (Auth::user()->hasPermission('review-daily-logs') === false){
            return redirect()->back();
        }

        $validated = $request->validate([
            'rating' => ['required', 'string'],
            'remarks' => ['required', 'string', 'max:1000'],
        ]);

        try {
            DB::beginTransaction();

            $edl = EmployeeDailyLog::where('edl_number', $edlNumber)->first();

            $edl->fill($validated);
            
            $edl->reviewer = Auth::user()->id;
            $edl->reviewed_at = (new DateTime('now'))->format('D, d M Y, H:i:s');

            $edl->save();


            DB::commit();

        } catch (Exception $e){
            DB::rollBack();
            return redirect()->back()->with('flash_error', $e->getMessage());
        }

        return new RedirectResponse(route('biller.employee-daily-log.index'), ['flash_success' => 'Daily Log Review Saved Successfully!']);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($edlNumber)
    {
        $this->checkRights(Auth::user()->can('create-daily-logs'));

        $sql = "
                SELECT rose_employee_daily_logs.edl_number, rose_employee_daily_logs.date,
                       CONCAT(loggers.first_name, ' ', loggers.last_name) AS employee,
                       rose_employee_daily_logs.rating, rose_employee_daily_logs.remarks,
                       CONCAT(reviewers.first_name, ' ', reviewers.last_name) AS reviewer,
                       rose_employee_daily_logs.reviewed_at
                FROM rose_employee_daily_logs
                JOIN rose_users AS loggers ON rose_employee_daily_logs.employee = loggers.id
                LEFT JOIN rose_users AS reviewers ON rose_employee_daily_logs.reviewer = reviewers.id
                WHERE rose_employee_daily_logs.edl_number = :edlNumber
            ";

        $edl = DB::select($sql, ['edlNumber' => $edlNumber]);
        //Converting from stdClass to Array
        $edl = json_decode(json_encode($edl[0], true), true);

        $edlTasks = EmployeeTasks::where('edl_number', $edlNumber)
            ->join('departments', 'employee_tasks.category', '=', 'departments.id')
            ->join('employee_task_subcategories', 'employee_tasks.subcategory', '=', 'employee_task_subcategories.id')
            ->select(
                'et_number',
                'departments.name as category',
                'employee_task_subcategories.name as subcategory',
                'employee_task_subcategories.frequency as frequency',
                'hours',
                'description'
            )
            ->get();


////        $data = array_combine(["edl", "edlTasks"], [$edl, $edlTasks]);
//
//         $data = [
//            "edl" => $edl,
//            "edlTasks" => $edlTasks,
//        ];


//        return compact('edl', 'edlTasks', 'edlNumber');

        return new ViewResponse('focus.employeeDailyLog.show', compact('edl', 'edlTasks', 'edlNumber'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return ViewResponse
     * @throws Exception
     */
    public function edit($edlNumber)
    {

        if (Auth::user()->hasPermission('edit-daily-logs') === false){
            return redirect()->back();
        }

        $edl = EmployeeDailyLog::where('edl_number', $edlNumber)->first();

        if ($edl->employee !== Auth::user()->id){
            return redirect()->back();
        }

        if (!empty($edl->rating) || !empty($edl->remarks)){
            return redirect()->back()->with('flash_error', 'Cannot edit this Daily Log as it has already been reviewed');
        }


//        $today = new DateTime('now');
//        $logDate = new DateTime($edl->created_at);
//        $dateDiff = ($today->diff($logDate))->days;
//
//        if ($dateDiff > 1 && !Auth::user()->hasRole('Directorate')) {
//            return redirect()->back()->with('flash_error', 'You can Edit Logs for Only One Day After the Initial Posting');
//        }


        $edl = EmployeeDailyLog::where('edl_number', $edlNumber)
            ->join('users', 'employee_daily_logs.employee', '=', 'users.id')
            ->select(
                'employee_daily_logs.employee as employee_id',
                'edl_number',
                'date',
                DB::raw('CONCAT(first_name, " ", last_name) AS employee'),
            )
            ->get();


        $edlTasks = EmployeeTasks::where('edl_number', $edlNumber)
            ->join('departments', 'employee_tasks.category', '=', 'departments.id')
            ->join('employee_task_subcategories', 'employee_tasks.subcategory', '=', 'employee_task_subcategories.id')
            ->select(
                'et_number',
                'subcategory',
                'hours',
                'description'
            )
            ->get();

        $data = [
            "edl" => $edl,
            "edlTasks" => $edlTasks,
        ];

        $employeeId =
        $taskCategories = Auth::user()->hasRole('Directorate') ? $this->getTaskCategories($edl[0]['employee_id']) : $this->getTaskCategories();

        return new ViewResponse('focus.employeeDailyLog.edit', compact('data', 'edlNumber', 'taskCategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $edlNumber)
    {
        if (Auth::user()->hasPermission('edit-daily-logs') === false){
            return redirect()->back();
        }

        $edl = EmployeeDailyLog::where('edl_number', $edlNumber)->first();
        if ($edl->employee !== Auth::user()->id){
            return redirect()->back();
        }


        try {
            DB::beginTransaction();

//            $edl->date = (new DateTime($request->date))->format('Y-m-d');
//
//            $edl->save();

            $etNumbers = EmployeeTasks::where('edl_number', $edlNumber)->pluck('et_number');


            foreach ($etNumbers as $etNo){

                $et = EmployeeTasks::where('et_number', $etNo)->first();

                if (empty($request['subcategory' . $etNo]) && empty($request['hours' . $etNo]) && empty($request['description' . $etNo])){

                    $et->delete();
                } else {

                    $et->subcategory = $request['subcategory' . $etNo];
                    $et->hours = $request['hours' . $etNo];
                    $et->description = $request['description' . $etNo];

                    $et->save();
                }

            }

            for ($i = 0; $i < 20; $i++) {

                if (!empty($request['subcategory' . $i])) {

                    $employeeTask = new EmployeeTasks();

                    $employeeTask->et_number = uniqid('ET' . Auth::user()->id . '-');

                    $employeeTask->edl_number = $edl->edl_number;
                    $employeeTask->category = HrmMeta::where('user_id', Auth::user()->id)->first()->department_id;
                    $employeeTask->subcategory = $request['subcategory' . $i];

                    $employeeTask->hours = $request['hours' . $i];
                    $employeeTask->description = $request['description' . $i];

                    $employeeTask->save();

                }

            }

            $empTasks = $edl->tasks;
            $hours = 0;

            foreach ($empTasks as $task){
                $hours += $task['hours'];
            }

            if ($hours > 15){
                DB::rollBack();
                return redirect()->back()->with('flash_error', 'Total Hours For Your Daily Log Cannot Exceed 14 Hours.');
            } else{
                DB::commit();
            }

        } catch (Exception $e){
            DB::rollBack();
            return redirect()->back()->with('flash_error', $e->getMessage());
        }


        return new RedirectResponse(route('biller.employee-daily-log.show', $edlNumber), ['flash_success' => 'Daily Log Review Updated Successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($edlNumber)
    {
        if (Auth::user()->hasPermission('delete-daily-logs') === false){
            return redirect()->back();
        }

        $edl = EmployeeDailyLog::where('edl_number', $edlNumber)->first();

        if ($edl->employee !== Auth::user()->id){
            return redirect()->back();
        }

        try {
            DB::beginTransaction();


            if (( empty($edl->remark) && empty($edl->rating) ) || Auth::user()->hasRole('Directorate')){

                $edlTasks = $edl->tasks;

                foreach ($edlTasks as $task){

                    $et = EmployeeTasks::where('et_number', $task['et_number'])->first();
                    $et->delete();

                }

                $edl->delete();

            }
            else {
                return redirect()->back()->with('flash_error', 'Cannot Delete this EDL as it has already been reviewed...');
            }

            DB::commit();

        } catch (Exception $e){
            DB::rollBack();
            return redirect()->back()->with('flash_error', $e->getMessage());
        }


        return new RedirectResponse(route('biller.employee-daily-log.index'), ['flash_success' => 'Daily Log Deleted Successfully!']);
    }


    /**
     * Checks if the user owns the resource
     * @param EmployeeDailyLog $edl
     * @return \Illuminate\Http\RedirectResponse|void
     */
    private function checkOwnership(EmployeeDailyLog $edl){
        if ($edl->employee !== Auth::user()->id){
            return redirect()->back();
        }
    }

    /**
     * Returna an array of task categories
     * @return array
     */
    private function getTaskCategories($employeeId = 'NA') : array{

        $employeeId = $employeeId === 'NA' ? Auth::user()->id : $employeeId;

        $departmentId = HrmMeta::where('user_id', $employeeId)->first()->department_id;
        $taskCategories = [];

        $edlSubcategoryAllocation = EdlSubcategoryAllocation::where('employee', $employeeId)->first();
        $allocations = [];

        if(!empty($edlSubcategoryAllocation)) {
            $allocations = json_decode($edlSubcategoryAllocation->allocations);
        }

        foreach ($allocations as $alloc){

            $cat = EmployeeTaskSubcategories::where('id', $alloc)->first();

            $taskCategory = [
                'label' => $cat->name,
                'value' => $cat->id,
                'frequency' => $cat->frequency,
            ];

            array_push($taskCategories, $taskCategory);
        }

        return $taskCategories;
    }


    public function createPerms() {

//        Permission::create([
//            'name' => 'manage-daily-logs',
//            'display_name' => 'EDL Manage Permission',
//        ]);
//
//        Permission::create([
//            'name' => 'create-daily-logs',
//            'display_name' => 'EDL Create Permission',
//        ]);
//
//        Permission::create([
//            'name' => 'edit-daily-logs',
//            'display_name' => 'EDL Edit Permission',
//        ]);
//
//        Permission::create([
//            'name' => 'delete-daily-logs',
//            'display_name' => 'EDL Delete Permission',
//        ]);
//
//        Permission::create([
//            'name' => 'review-daily-logs',
//            'display_name' => 'EDL Review Permission',
//        ]);
//
//        Permission::create([
//            'name' => 'manage-edl-categories',
//            'display_name' => 'EDL Categories Manage Permission',
//        ]);
//
//        Permission::create([
//            'name' => 'create-edl-categories',
//            'display_name' => 'EDL Categories Create Permission',
//        ]);
//
//         Permission::create([
//            'name' => 'edit-edl-categories',
//            'display_name' => 'EDL Categories Edit Permission',
//        ]);
//
//        Permission::create([
//            'name' => 'delete-edl-categories',
//            'display_name' => 'EDL Categories Delete Permission',
//        ]);
//
//        Permission::create([
//            'name' => 'allocate-edl-categories',
//            'display_name' => 'EDL Categories Allocate Permission',
//        ]);


        Permission::create([
            'name' => 'finance-management',
            'display_name' => 'Finance Management Permission',
        ]);

        Permission::create([
            'name' => 'procurement-management',
            'display_name' => 'Procurement Management Permission',
        ]);

        Permission::create([
            'name' => 'banking-management',
            'display_name' => 'Banking Management Permission',
        ]);


    }


    private function checkRights($canDoIt){

        if ($canDoIt === false){
            return redirect()->back();
        }
    }


}
