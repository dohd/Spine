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
namespace App\Http\Controllers\Focus\payroll;

use App\Models\payroll\payroll;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\payroll\CreateResponse;
use App\Http\Responses\Focus\payroll\EditResponse;
use App\Repositories\Focus\payroll\PayrollRepository;
use Yajra\DataTables\Facades\DataTables;
use App\Models\hrm\Hrm;

/**
 * payrollsController
 */
class PayrollController extends Controller
{
    /**
     * variable to store the repository object
     * @var payrollRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param payrollRepository $repository ;
     */
    public function __construct(PayrollRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\payroll\ManagepayrollRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(Request $request)
    {
        return new ViewResponse('focus.payroll.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreatepayrollRequestNamespace $request
     * @return \App\Http\Responses\Focus\payroll\CreateResponse
     */
    public function create(Request $request)
    {
        return new CreateResponse('focus.payroll.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorepayrollRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;
        //Create the model using repository create method
        $result = $this->repository->create($input);
        //dd($result);
        //return with successfull message
        return new RedirectResponse(route('biller.payroll.page', $result), ['flash_success' => 'Payroll Processed Successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\payroll\payroll $payroll
     * @param EditpayrollRequestNamespace $request
     * @return \App\Http\Responses\Focus\payroll\EditResponse
     */
    public function edit(Payroll $payroll, Request $request)
    {
        return new EditResponse($payroll);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatepayrollRequestNamespace $request
     * @param App\Models\payroll\payroll $payroll
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, Payroll $payroll)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($payroll, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.payroll.index'), ['flash_success' => 'Payroll Processing Updating Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletepayrollRequestNamespace $request
     * @param App\Models\payroll\payroll $payroll
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Payroll $payroll, Request $request)
    {
        //Calling the delete method on repository
        $this->repository->delete($payroll);
        //returning with successfull message
        return new RedirectResponse(route('biller.payroll.index'), ['flash_success' => 'Payroll Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletepayrollRequestNamespace $request
     * @param App\Models\payroll\payroll $payroll
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Payroll $payroll, Request $request)
    {

        //returning with successfull message
        return new ViewResponse('focus.payroll.view', compact('payroll'));
    }

    public function get_employee(Request $request)
    {
        //Date from and date to in request
        $startDate = '2023-03-28';
        $endDate = '2023-04-28';
        $employees = Hrm::whereHas('employees_salary')->where('contract_type', 'permanent')->with(['attendance' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate])
                    ->where('status', 'absent');
        }])->get();
        
        
        
        //return $absent_days;
    }
    public function get_deductions()
    {
        return Datatables::of($employees)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('employee_name', function ($payroll) {
              //  dd($payroll);
               return $payroll->employees_salary ? $payroll->employees_salary->employee_name : '';
            })
            ->addColumn('basic_pay', function ($payroll) {
                return $payroll->employees_salary ? amountFormat($payroll->employees_salary->basic_pay) : '';
            })
            ->addColumn('total_allowances', function ($payroll) {
                return $payroll->employees_salary ? amountFormat($payroll->employees_salary->house_allowance + $payroll->employees_salary->transport_allowance) : '';
            })
            ->addColumn('gross_pay', function ($payroll) {
                return $payroll->employees_salary ? amountFormat($payroll->employees_salary->basic_pay + $payroll->employees_salary->house_allowance + $payroll->employees_salary->transport_allowance) : '';
            })
            ->addColumn('actions', function ($payroll) {
                return $payroll->actions_buttons;
            })
            ->make(true);
    }
    public function page($id)
    {
        //dd($id);
        $payroll = Payroll::find($id);
        $payroll->reference = gen4tid('PYRL-',$payroll->tid);
        $employees = Hrm::with(['employees_salary' => function ($q){
            $q->where('contract_type', 'permanent');
        }])->get();
        return view('focus.payroll.pages.create', compact('payroll', 'employees'));
    }

    public function store_basic(Request $request)
    {
        $data = $request->only([
            'payroll_id','salary_total'
        ]);
        $data_items = $request->only([
            'present_days', 'absent_days','rate_per_day','rate_per_month','basic_pay', 'employee_id'
        ]);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        //dd($data_items);
        // modify and filter items without item_id
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function ($v) { return $v['absent_days']; });

        
        try {
            $result = $this->repository->create_basic(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error creating Basic Salary', $th);
        }
        return redirect()->back();
    }

}
