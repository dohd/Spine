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

use App\Models\payroll\Payroll;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\payroll\CreateResponse;
use App\Http\Responses\Focus\payroll\EditResponse;
use App\Repositories\Focus\payroll\PayrollRepository;
use Yajra\DataTables\Facades\DataTables;
use App\Models\hrm\Hrm;
use App\Models\deduction\Deduction;

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
        foreach ($payroll->payroll_items as $item) {
            $item->employee_name = $item->employee->first_name;
        }

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
        $total_gross = 0;
        $total_paye = 0;
        $total_nhif = 0;
        $total_nssf = 0;
        $total_tx_deduction = 0;

        foreach ($payroll->payroll_items as $item) {
            $item->employee_name = $item->employee->first_name;
            if($item->total_basic_allowance){
                $item->nssf = $this->calculate_nssf($item->total_basic_allowance);
                $item->gross_pay = $item->total_basic_allowance - ($item->nssf + $item->tx_deductions);
                $total_gross += $item->gross_pay;
                $item->nhif = $this->calculate_nhif($item->gross_pay);
                $nhif_relief = 15/100 * $item->nhif;
                $item->paye = $this->calculate_paye($item->gross_pay) - $nhif_relief;
                $total_paye += $item->paye;
                $total_nhif += $item->nhif;
                $total_nssf += $item->nssf;
                $total_tx_deduction += $item->tx_deductions;
                //dd($nhif_relief);
            }
        }
        return view('focus.payroll.pages.create', compact('payroll', 'employees','total_gross','total_paye','total_nhif','total_nssf','total_tx_deduction'));
    }

    public function approve_payroll(Request $request)
    {
        //dd($request->all());
        $payroll = Payroll::find($request->id);
        $payroll->approval_note = $request->approval_note;
        $payroll->approval_date = date_for_database($request->approval_date);
        $payroll->status = $request->status;
        $payroll->update();
        return redirect()->back();
    }

    public function store_basic(Request $request)
    {
        //dd($request->all());
        $data = $request->only([
            'payroll_id','salary_total','processing_date'
        ]);
        $data_items = $request->only([
            'present_days', 'absent_days','rate_per_day','rate_per_month','basic_pay', 'employee_id'
        ]);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        //dd($data_items);
        // modify and filter items without item_id
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function ($v) { return $v['employee_id']; });

        
        try {
            $result = $this->repository->create_basic(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error creating Basic Salary', $th);
        }
        return redirect()->back();
    }
    public function store_nhif(Request $request)
    {
         //dd($request->all());
         $data = $request->only([
            'payroll_id','total_nhif'
        ]);
        

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        
        try {
            $result = $this->repository->create_nhif(compact('data'));
        } catch (\Throwable $th) {
            return errorHandler('Error creating Taxable Deductions', $th);
        }
        return redirect()->back();
    }

    public function store_allowance(Request $request)
    {
        //dd($request->all());
        $data = $request->only([
            'payroll_id','allowance_total'
        ]);
        $data_items = $request->only([
            'id', 'house_allowance','transport_allowance','other_allowance','total_allowance','total_basic_allowance'
        ]);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        //dd($data_items);
        // modify and filter items without item_id
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function ($v) { return $v['id']; });

        
        try {
            $result = $this->repository->create_allowance(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error creating Taxable Allowances', $th);
        }
        return redirect()->back();
    }
    public function store_deduction(Request $request)
    {
        //dd($request->all());
        $data = $request->only([
            'payroll_id','deduction_total','total_nssf'
        ]);
        $data_items = $request->only([
            'id', 'nssf','nhif','gross_pay','total_sat_deduction','tx_deductions'
        ]);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        //dd($data_items);
        // modify and filter items without item_id
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function ($v) { return $v['id']; });

        
        try {
            $result = $this->repository->create_deduction(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error creating Taxable Deductions', $th);
        }
        return redirect()->back();
    }
    public function store_otherdeduction(Request $request)
    {
        
        $data = $request->only([
            'payroll_id','other_benefits_total','other_deductions_total'
        ]);
        $data_items = $request->only([
            'id', 'total_benefits','total_other_deduction','loan','advance'
        ]);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        
        // modify and filter items without item_id
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function ($v) { return $v['id']; });
        
       
        try {
            $result = $this->repository->create_other_deduction(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error creating Taxable Deductions', $th);
        }
        return redirect()->back();
    }
    public function store_summary(Request $request)
    {
        
        $data = $request->only([
            'payroll_id','total_netpay'
        ]);
        $data_items = $request->only([
            'id', 'netpay'
        ]);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        
        // modify and filter items without item_id
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function ($v) { return $v['id']; });
        
       
       
        try {
            $result = $this->repository->create_summary(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error creating Taxable Deductions', $th);
        }
        return redirect()->back();
    }

    public function store_paye(Request $request)
    {
        //dd($request->all());
        $data = $request->only([
            'payroll_id','paye_total'
        ]);
        $data_items = $request->only([
            'id', 'paye','taxable_gross'
        ]);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        //dd($data_items);
        // modify and filter items without item_id
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function ($v) { return $v['id']; });

        
        try {
            $result = $this->repository->create_paye(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error creating Taxable Deductions', $th);
        }
        return redirect()->back();
    }
    public function calculate_nssf($gross_pay)
    {
        $nssf_brackets = Deduction::where('deduction_id','2')->get();
        $nssf = 0;
        foreach ($nssf_brackets as $i => $bracket) {
            if($i > 0){
                if($gross_pay > $bracket->amount_from){
                    $nssf = $bracket->rate;
                }
            }else{
                $nssf = $bracket->rate/100 * $gross_pay;
            }
        }
        return $nssf;
    }
    public function calculate_nhif($gross_pay)
    {
        $nhif_brackets = Deduction::where('deduction_id','1')->get();
        $nhif = 0;
        foreach ($nhif_brackets as $i => $bracket) {
                if($gross_pay > $bracket->amount_from && $gross_pay <= $bracket->amount_to){
                    $nhif = $bracket->rate;
                }
        }
        return $nhif;
    }

    public function calculate_paye($gross_pay)
    {
         //Get PAYE brackets
         $tax = 0;
         $paye_brackets = Deduction::where('deduction_id','3')->get();
         $first_bracket = Deduction::where('deduction_id','3')->first();
         $personal_relief = $first_bracket->rate/100 * $first_bracket->amount_to;
         $count = count($paye_brackets);
         //dd($count);
            foreach ($paye_brackets as $i => $bracket) {
                if ($i == $count-1) {
                    //dd($bracket->rate);
                    if ($gross_pay > $bracket->amount_from) {
                        $tax += $bracket->rate / 100 * ($gross_pay - $bracket->amount_from);
                       //dd($gross_pay);
                    }
                    //dd($tax);
                }
                else {
                    //dd($gross_pay);
                    if($i == 0){
                        
                        if($gross_pay > $bracket->amount_from){
                            $tax += $bracket->rate/100 * $bracket->amount_to;
                            //dd($tax);
                        }
                        
                        // else{
                        //     $tax += $bracket->rate/100 *$bracket->amount_to;
                        // }
                    }else{
                        
                        if($gross_pay >= $bracket->amount_from && $gross_pay < $bracket->amount_to){
                            $tax += $bracket->rate/100 * ($gross_pay - $bracket->amount_from);
                           // dd($tax);
                        }
                        elseif($gross_pay >= $bracket->amount_from && $gross_pay > $bracket->amount_to){
                            $tax += $bracket->rate/100 * ($bracket->amount_to - $bracket->amount_from);
                        }
                        //dd($bracket->amount_from);
                        // elseif ($i != $count - 1) {
                        //     $tax += $bracket->rate/100 * $bracket->amount_to;
                        // }
                    }
                    //$tax = $bracket->rate / 100 * ($bracket->amount_to);
                }
                // dd($tax);
             }
             if($gross_pay > $first_bracket->amount_to){
                $tax = $tax - $personal_relief;
             }else{
                $tax = $tax - ($first_bracket->rate/100 * $gross_pay);
             }
        // dd($tax);
        return $tax;
    }

}
