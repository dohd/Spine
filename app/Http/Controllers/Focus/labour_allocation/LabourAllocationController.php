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

namespace App\Http\Controllers\Focus\labour_allocation;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\labour_allocation\LabourAllocation;
use App\Models\labour_allocation\LabourAllocationItem;
use App\Models\project\ProjectMileStone;
use App\Models\quote\Quote;
use App\Repositories\Focus\labour_allocation\LabourAllocationRepository;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use App\Models\Access\User\User;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\misc\Misc;
use App\Models\project\Budget;
use App\Models\project\Project;
use App\Models\project\ProjectQuote;
use App\Models\project\ProjectRelations;
use App\Models\account\Account;
use App\Models\project\BudgetItem;
use App\Models\salary\Salary;

use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\ValidationException;

/**
 *
 */
class LabourAllocationController extends Controller
{
    /**
     * variable to store the repository object
     * @var LabourAllocationRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param LabourAllocationRepository $repository ;
     */
    public function __construct(LabourAllocationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customers = Customer::all(['id', 'company']);
        return new ViewResponse('focus.labour_allocations.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customers = Customer::whereHas('quotes')->get(['id', 'company']);
        $accounts = Account::where('account_type', 'Income')->get(['id', 'holder', 'number']);
        $last_tid = Project::where('ins', auth()->user()->ins)->max('tid');

        $mics = Misc::all();
        $statuses = Misc::where('section', 2)->get();
        $tags = Misc::where('section', 1)->get();

        $employees = Hrm::all();
        $project = new Project();

        return view('focus.labour_allocations.create', compact('customers', 'accounts', 'last_tid', 'project', 'mics', 'employees', 'statuses', 'tags'));
    }


    /**
     *
     * Provides the Labour allocation data
     * Returns number of entries and total man houra from Yesterday and the current month's total
     * @return array[]
     */
    public function getLabourAllocationData(): array
    {
        //FOR THIS MONTH
        $yesterday = (new DateTime('now'))->sub(new DateInterval('P1D'));

        $yesterdayLabourAllocations = LabourAllocation::where('date', $yesterday->format('Y-m-d'))->get();

        $ylaCount = $yesterdayLabourAllocations->count();

        $ylaTotalManHours = 0;

        foreach ($yesterdayLabourAllocations as $yla){

            $ylaTotalManHours += $yla['hrs'];
        }

        $ylaMetrics = [
            'ylaCount' => $ylaCount,
            'ylaTotalManHours' => $ylaTotalManHours
        ];

        //FOR THIS MONTH
        $thisMonthLabourAllocations = LabourAllocation::whereMonth('date', date('m'))->get();

        $tmlaCount = $thisMonthLabourAllocations->count();

        $tmlaTotalManHours = 0;
        $daysInMonth = (new DateTime('now'))->format('t');

        foreach ($thisMonthLabourAllocations as $tmla){

            $tmlaTotalManHours += $tmla['hrs'];
        }

        $tmlaMetrics = [
            'tmlaCount' => $tmlaCount,
            'tmlaTotalManHours' => $tmlaTotalManHours,
            'entriesTarget' => bcmul($daysInMonth, 12),
            'monthHoursTarget' => bcmul($daysInMonth, 72),
        ];


        return $labourAllocationData = [
            'yesterday' => $ylaMetrics,
            'thisMonth' => $tmlaMetrics
        ];
    }


    /**
     * @throws \Exception
     */
    public function get7DaysLabourMetrics(){

        $hoursTotals = array_fill(0, 7, 0);
        $labourDates = array_fill(0, 7, 'N/A');
        for ($i = 1; $i <= 7; $i++){

            $date = (new DateTime('now'))->sub(new DateInterval('P' . $i . 'D'))->format('Y-m-d');

            $labourAllocations = LabourAllocation::where('date', $date)->pluck('hrs');
            foreach ($labourAllocations as $alloc){
                $hoursTotals[$i-1] += $alloc;
            }

            $labourDates[$i-1] = (new DateTime('now'))->sub(new DateInterval('P' . $i . 'D'))->format('jS M');
        }

        $labourDates = array_reverse($labourDates);
        $hoursTotals = array_reverse($hoursTotals);


        $startDate = (new DateTime('now'))->sub(new DateInterval('P7D'))->format('jS F');
        $endDate = (new DateTime('now'))->sub(new DateInterval('P1D'))->format('jS F');


        $chartTitle = 'Daily Labour Hours from ' . $startDate . ' to ' . $endDate . ', ' . (new DateTime('now'))->format('Y');

        return compact('hoursTotals', 'labourDates', 'chartTitle');
    }


    /**
     * @throws Exception
     */
    public function getDailyLabourHours(string $date = 'now')
    {

        $refDate = new DateTime($date);

        $month = $refDate->format('M');

        $week = $refDate->format('W');

        $daysOfTheWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $dailyTotals = array_fill(0, 7, 0);

        $weekHoursTotals = array_combine($daysOfTheWeek, $dailyTotals);

        //SALES TOTALS
        $monthLabourAllocation = LabourAllocation::whereMonth('date', $refDate->format('m'))
            ->whereYear('date', $refDate->format('Y'))->get();

        foreach ($monthLabourAllocation as $allocation) {

            $allocationWeek = (new DateTime($allocation['date']))->format('W');

            if ($allocationWeek === $week) {

                $allocationDay = (new DateTime($allocation['date']))->format('D');

                $weekHoursTotals[$allocationDay] += $allocation['hrs'];

            }

        }

        $chartTitle = "Daily Labour Hours for Week " . $week . " of " . $refDate->format('Y');

        return [
            'chartTitle' => $chartTitle,
            'weekHoursTotals' => $weekHoursTotals,
            'daysOfTheWeek' => $daysOfTheWeek,
        ];

    }


        /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $data = $request->only(['project_id', 'hrs', 'date', 'type', 'ref_type', 'job_card', 'note', 'is_payable', 'project_milestone']);
        $data_items = $request->only(['employee_id']);

        $data['user_id'] = auth()->user()->id;
        $data['ins'] = auth()->user()->ins;
        $data_items = modify_array($data_items);
        try {

            $labourAllocation = LabourAllocation::where('job_card', $request->job_card)->first();

            if (!empty($labourAllocation)){
                return redirect()->back()->with('flash_error', 'Job Card Number is already Allocated. Please Confirm Details');
            }

            $this->repository->create(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Creating LabourAllocation!', $th);
        }
        return new RedirectResponse(route('biller.labour_allocations.index'), ['flash_success' => 'Labour Allocation created successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  LabourAllocation $labour_allocation
     * @return \Illuminate\Http\Response
     */
    public function edit(LabourAllocation $labour_allocation)
    {
        //dd($labour_allocation);
        $customers = Customer::whereHas('quotes')->get(['id', 'company']);
        $accounts = Account::where('account_type', 'Income')->get(['id', 'holder', 'number']);
        $last_tid = Project::where('ins', auth()->user()->ins)->max('tid');

        $mics = Misc::all();
        $statuses = Misc::where('section', 2)->get();
        $tags = Misc::where('section', 1)->get();

        $employees = Hrm::all();
        $project = new Project();

        return view('focus.labour_allocations.edit', compact('labour_allocation', 'customers', 'accounts', 'last_tid', 'project', 'mics', 'employees', 'statuses', 'tags'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  LabourAllocation $labour_allocation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LabourAllocation $labour_allocation)
    {
        $data = $request->only(['hrs', 'date', 'type', 'ref_type', 'job_card', 'id', 'note', 'is_payable', 'project_milestone']);
        $data_items = $request->only(['employee_id', 'id']);

        $data['user_id'] = auth()->user()->id;
        $data['ins'] = auth()->user()->ins;
        $data_items = modify_array($data_items);

        try {
            $this->repository->update($labour_allocation, compact('data', 'data_items'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Updating LabourAllocation!', $th);
        }

        return new RedirectResponse(route('biller.labour_allocations.index'), ['flash_success' => 'Labour Allocation Updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  LabourAllocation $labour_allocation
     * @return \Illuminate\Http\Response
     */
    public function destroy(LabourAllocation $labour_allocation)
    {
        try {
            $this->repository->delete($labour_allocation);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting LabourAllocation!', $th);
        }

        return new RedirectResponse(route('biller.labour_allocations.index'), ['flash_success' => 'LabourAllocation Deleted Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  LabourAllocation $labour_allocation
     * @return \Illuminate\Http\Response
     */
    public function show(LabourAllocation $labour_allocation)
    {
        $project = $labour_allocation->project;
        $customer_branch = '';
        if ($project) {
            $customer = $project->customer ? $project->customer->name : '';
            $branch = $project->branch ? $project->branch->name : '';
            $customer_branch = $customer . ' - ' . $branch;
        }
        $employee = [];
        foreach ($labour_allocation->items as $item) {
            $employee[] = [
                'employee_name' => $item->employee ? $item->employee->first_name . ' ' . $item->employee->last_name : '',
                'id' => $item->id,
                'employee_id' => $item->employee_id,
            ];
        }

        return view('focus.labour_allocations.view', compact('labour_allocation', 'customer_branch', 'employee'));
    }
    
    
    /** Expected Allocation Hrs */
    public function expected_hours(Request $request)
    {
        $result = ['hours' => 0];
        try {
            $project_id = request('project_id');
            $project = Project::find($project_id);
            
            $quote = Quote::find(request('quote_id'));
            if (@$quote->project) $project = $quote->project;
            if ($project) {
                $project_id = $project->id;
                $result['project_id'] = $project->id;
                $result['project_name'] = $project->name;
                $result['project_tid'] = gen4tid('PRJ-', $project->tid);
                $result['quote_tid'] = [];
                foreach ($project->quotes as $quote) {
                    $result['quote_tid'][] = gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid);
                }
                $result['quote_tid'] = implode(',', $result['quote_tid']);
            }
            
            $total_project_hrs = BudgetItem::whereHas('budget', function($q) use($project_id) {
                $q->whereHas('quote', function($q) use($project_id) {
                    $q->whereHas('project', fn($q) => $q->where('projects.id', $project_id));
                });
            })->whereHas('product', function($q) {
                $q->whereHas('product', function($q) {
                    $q->where('stock_type', 'service');
                    // use units since service had indistinguishable data (qty column used for rate)
                    $q->whereHas('units', fn($q) => $q->where('code', 'Mnhr'));
                });
            })->sum('new_qty');

            $total_alloc_hrs = LabourAllocation::where('project_id', request('project_id'))->sum('hrs');
            $result['hours'] = $total_project_hrs - $total_alloc_hrs;
        } catch (\Throwable $th) {
            throw $th;
        }

        return response()->json($result);
    }
    
    /** Employee Labour Rate per Hour */
    public function employee_hourly_rate(Request $request)
    {
        $rate = 0;
        try {
            $salary = Salary::where('employee_id', request('employee_id'))
                ->where('pay_per_hr', '>', 0)
                ->latest()->first();
            if ($salary) $rate = +$salary->pay_per_hr;
        } catch (\Throwable $th) {
            //throw $th;
        }
        
        return response()->json(['rate' => $rate]);
    }
    
    
    
    
    
    
    
    
    public function attach_employee($id, $employee_id)
    {
        $labour = LabourAllocation::find($id);
        $employee = $labour->employee ? $labour->employee->first_name . ' ' . $labour->employee->last_name : '';
        $labour_items = $labour->items()->get();
        return view('focus.labour_allocations.attach_employee', compact('id', 'employee_id', 'labour', 'labour_items', 'employee'));
    }

    public function get_employee_items(Request $request)
    {
        $labour = LabourAllocation::find($request->id);
        $labour_items = $labour->items()->get();
        return DataTables::of($labour_items)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('date', function ($labour_item) {
                return dateFormat($labour_item->date);
            })
            ->addColumn('hrs', function ($labour_item) {
                return numberFormat($labour_item->hrs);
            })
            ->addColumn('type', function ($labour_item) {
                return $labour_item->type;
            })
            ->addColumn('actions', function ($labour_item) {
                return '<a href="' . route('biller.labour_allocations.show', $labour_item->id) . '" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="View"><i  class="fa fa-eye"></i></a>' . '<a href="' . route('biller.labour_allocations.edit_item', $labour_item->id) . '" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="Edit"><i  class="fa fa-pencil "></i></a>' . '<a href="' . route('biller.labour_allocations.delete_item', $labour_item->id) . '" class="btn btn-danger round" data-toggle="tooltip" data-placement="top" title="Delete"><i  class="fa fa-trash"></i></a>';
            })
            ->make(true);
        return response()->json($labour_items);
    }
    
    public function store_labour_items(Request $request)
    {
        $data = $request->only(['date', 'labour_id', 'hrs', 'type', 'is_payable']);
        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        LabourAllocationItem::create($data);
        return redirect()
            ->back()
            ->with('flash_success', 'Employee Data added!!');
    }
    public function delete_item($id)
    {
        //dd($id);
        LabourAllocationItem::find($id)->delete();
        return redirect()
            ->back()
            ->with('flash_success', 'Employee Deleted Successfully!!');
    }
    public function edit_item($id)
    {
        //dd($id);
        $labour_item = LabourAllocationItem::find($id);
        return view('focus.labour_allocations.edit_item', compact('labour_item'));
    }
    public function update_item(Request $request, $labour_item)
    {
        $data = $request->only(['date', 'labour_id', 'hrs', 'type']);
        $item = LabourAllocationItem::find($labour_item);
        $item->update($data);
        $employee_id = $item->labour->employee_id;
        // dd($employee_id);

        return new RedirectResponse(route('biller.labour_allocations.attach_employee', [$labour_item, $employee_id]), ['flash_success' => 'Labour Items Updated Successfully']);
    }
    public function delete_labour($id)
    {
        //dd($id);
        $labour = LabourAllocation::find($id);
        dd($labour);
        $labour->delete();
        $labour->items->each->delete();
        return redirect()
            ->back()
            ->with('flash_success', 'Employee Deleted Successfully!!');
    }
    public function employee_summary()
    {
        $employees = Hrm::all(['id', 'first_name', 'last_name']);
        return view('focus.labour_allocations.employee_summary', compact('employees'));
    }
}
