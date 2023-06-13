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
use Illuminate\Support\Facades\Mail;
use App\Mail\SendPayslipEmail;
use Illuminate\Support\Facades\View;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Jobs\SendEmailJob;
use App\Models\salary\Salary;
use App\Models\account\Account;
use App\Models\payroll\PayrollItem;
use Carbon\Carbon;
use DB;
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
        $payroll->processing_month = Carbon::parse($payroll->processing_month)->format('Y-m');
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
        return new RedirectResponse(route('biller.payroll.page', $payroll->id), ['flash_success' => 'Payroll Processing Updating Successfully!!']);
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
            $item->employee_name = $item->employee ? $item->employee->first_name : '';
        }
        $accounts = Account::whereNull('system')
            ->whereHas('accountType', fn($q) =>  $q->where('system', 'bank'))
            ->get(['id', 'holder']);

        //returning with successfull message
        return new ViewResponse('focus.payroll.view', compact('payroll','accounts'));
    }

    public function get_employee(Request $request)
    {
        
        $payroll = Payroll::find($request->payroll_id);
        $payroll_items = $payroll->payroll_items;
        return Datatables::of($payroll_items)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('employee_id', function ($payroll_items) {
                $employee_id = gen4tid('EMP-', $payroll_items->employee_id);
                return $employee_id;
             })
            ->addColumn('employee_name', function ($payroll_items) {
                $employee_name = $payroll_items->employee ? $payroll_items->employee->first_name : '';
               return $employee_name;
            })
            ->addColumn('basic_pay', function ($payroll_items) {
                return amountFormat($payroll_items->basic_pay);
            })
            ->addColumn('absent_days', function ($payroll_items) {
                return $payroll_items->absent_days;
            })
            ->addColumn('house_allowance', function ($payroll_items) {
                return amountFormat($payroll_items->house_allowance);
            })
            ->addColumn('transport_allowance', function ($payroll_items) {
                return amountFormat($payroll_items->transport_allowance);
            })
            ->addColumn('other_allowance', function ($payroll_items) {
                return amountFormat($payroll_items->other_allowance);
            })
            ->addColumn('gross_pay', function ($payroll_items) {
                return amountFormat($payroll_items->gross_pay -$payroll_items->tx_deductions);
            })
            ->addColumn('nssf', function ($payroll_items) {
                return amountFormat($payroll_items->nssf);
            })
            ->addColumn('tx_deductions', function ($payroll_items) {
                return amountFormat($payroll_items->tx_deductions);
            })
            ->addColumn('paye', function ($payroll_items) {
                return amountFormat($payroll_items->paye);
            })
            ->addColumn('taxable_gross', function ($payroll_items) {
                return amountFormat($payroll_items->taxable_gross);
            })
            ->addColumn('total_other_allowances', function ($payroll_items) {
                return amountFormat($payroll_items->total_other_allowances);
            })
            ->addColumn('total_benefits', function ($payroll_items) {
                return amountFormat($payroll_items->total_benefits);
            })
            ->addColumn('loan', function ($payroll_items) {
                return amountFormat($payroll_items->loan);
            })
            ->addColumn('advance', function ($payroll_items) {
                return amountFormat($payroll_items->advance);
            })
            ->addColumn('total_other_deductions', function ($payroll_items) {
                return amountFormat($payroll_items->total_other_deductions);
            })
            ->addColumn('netpay', function ($payroll_items) {
                return amountFormat($payroll_items->netpay);
            })
            ->make(true);
    }
    public function get_deductions()
    {
        return Datatables::of($employees)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('employee_name', function ($payroll) {
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
        $expired_contracts = Salary::where('status', 'expired')->count();
        $payroll = Payroll::find($id);
        $payroll->reference = gen4tid('PYRL-',$payroll->tid);
        $employees = Hrm::with(['employees_salary' => function ($q){
            $q->where('contract_type', 'permanent')->where('status', 'ongoing');
        }])->get();
        $total_gross = 0;
        $total_paye = 0;
        $total_nhif = 0;
        $total_nssf = 0;
        $total_tx_deduction = 0;

        foreach ($payroll->payroll_items as $item) {
            $item->employee_name = $item->employee ? $item->employee->first_name : '';
            if($item->total_basic_allowance){
                $item->nssf = $this->calculate_nssf($item->total_basic_allowance);
                $item->gross_pay = $item->total_basic_allowance - ($item->nssf + $item->tx_deductions);
                $total_gross += $item->gross_pay;
                $item->nhif = $this->calculate_nhif($item->gross_pay);
                $nhif_relief = 15/100 * $item->nhif;
                $item->paye = $this->calculate_paye($item->gross_pay) - $nhif_relief;
                if($item->paye < 0){
                    $item->paye = 0;
                }
                $total_paye += $item->paye;
                $total_nhif += $item->nhif;
                $total_nssf += $item->nssf;
                $total_tx_deduction += $item->tx_deductions;
               
            }
        }
        return view('focus.payroll.pages.create', compact('payroll', 'employees','total_gross','total_paye','total_nhif','total_nssf','total_tx_deduction','expired_contracts'));
    }

    public function approve_payroll(Request $request)
    {
        //dd($request->all());
        $payroll = Payroll::find($request->id);
        $payroll->approval_note = $request->approval_note;
        $payroll->approval_date = date_for_database($request->approval_date);
        $payroll->status = $request->status;
       // $payroll['account'] = $request->account_id;
       // $payroll->update();
        $this->repository->approve_payroll(compact('payroll'));
        return redirect()->back();
    }

    public function send_mail(Request $request)
    {
        //dd($request->all());
        $payroll = Payroll::find($request->id);
        $users = $payroll->payroll_items()->get();
        $input=array();
        $input['text']='Payslip Received';
        $input['subject']='Payslip';
        $input['customer_name']='Your name';
        SendEmailJob::dispatch($users, $input);
        return redirect()->back();
    }

    public function store_basic(Request $request)
    {
        //dd($request->all());
        $data = $request->only([
            'payroll_id','salary_total','processing_date'
        ]);
        $data_items = $request->only([
            'absent_rate', 'absent_days','rate_per_day','rate_per_month','basic_pay', 'employee_id','basic_salary'
        ]);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
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

    public function update_basic(Request $request)
    {
        //dd($request->all());
        $payroll_items = PayrollItem::find($request->id);
        $new_house_allowance = 0;
        $new_transport_allowance = 0;
        $new_other_allowance = 0;
        if ($request->absent_days > 0 && $payroll_items->absent_days > 0) {
            //New House allowance
            $house_allowance = ($request->month * $payroll_items->house_allowance) / $payroll_items->absent_days;
            $new_house_allowance = $house_allowance - ($request->absent_days / $request->month);
            //New Transport allowance
            $transport_allowance = ($request->month * $payroll_items->transport_allowance) / $payroll_items->absent_days;
            $new_transport_allowance = $transport_allowance - ($request->absent_days / $request->month);
            //New other allowance
            $other_allowance = ($request->month * $payroll_items->other_allowance) / $payroll_items->absent_days;
            $new_other_allowance = $other_allowance - ($request->absent_days / $request->month);
           // dd($new_house_allowance, 1);
        }
        else if ($request->absent_days > 0 && $payroll_items->absent_days == 0) {
            //New House allowance
            $house_allowance = ($payroll_items->house_allowance * $request->absent_days) / $request->month;
            $new_house_allowance = $payroll_items->house_allowance - $house_allowance;
            //New Transport allowance
            $transport_allowance = ($payroll_items->transport_allowance * $request->absent_days) / $request->month;
            $new_transport_allowance = $payroll_items->transport_allowance - $transport_allowance;
            //New other allowance
            $other_allowance = ($payroll_items->other_allowance * $request->absent_days) / $request->month;
            $new_other_allowance = $payroll_items->other_allowance - $other_allowance;
            //dd($new_house_allowance, 2);
        }
        else if ($request->absent_days == 0 && $payroll_items->absent_days > 0) {
            //New House allowance
            $house_allowance = ($payroll_items->house_allowance * $request->month) / ($request->month - $payroll_items->absent_days);
            $new_house_allowance = $house_allowance;
            //New Transport allowance
            $transport_allowance = ($payroll_items->transport_allowance * $request->month) / ($request->month - $payroll_items->absent_days);
            $new_transport_allowance = $transport_allowance;
            //New other allowance
            $other_allowance = ($payroll_items->other_allowance * $request->month) / ($request->month - $payroll_items->absent_days);
            $new_other_allowance = $other_allowance;
            //dd($other_allowance);
           // dd($new_house_allowance, 3);
        }
        else if ($request->absent_days == 0 && $payroll_items->absent_days == 0) {
            //New House allowance
            $new_house_allowance = $payroll_items->house_allowance;
            //New Transport allowance
            $new_transport_allowance = $payroll_items->transport_allowance;
            //New other allowance
            $new_other_allowance = $payroll_items->other_allowance;
            //dd($other_allowance);
           // dd($new_house_allowance, 4);
        }
        //dd($new_house_allowance);
        //Updating Payroll Items
        $payroll_items->absent_days = $request->absent_days;
        $payroll_items->absent_rate = $request->absent_rate;
        $payroll_items->basic_pay = $request->basic_pay;
        $payroll_items->house_allowance = $new_house_allowance;
        $payroll_items->transport_allowance = $new_transport_allowance;
        $payroll_items->other_allowance = $new_other_allowance;
        $allowance = $new_other_allowance + $new_transport_allowance +$new_house_allowance;
        $total_basic_allowance = $request->basic_pay + $allowance;
        $payroll_items->total_allowance = $allowance;
        $payroll_items->total_basic_allowance = $total_basic_allowance;
        if($payroll_items->total_basic_allowance){
            $payroll_items->nssf = $this->calculate_nssf($payroll_items->total_basic_allowance);
            $payroll_items->gross_pay = $payroll_items->total_basic_allowance - ($payroll_items->nssf + $payroll_items->tx_deductions);
            // $total_gross += $payroll_items->gross_pay;
            $payroll_items->nhif = $this->calculate_nhif($payroll_items->gross_pay);
            $nhif_relief = 15/100 * $payroll_items->nhif;
            $payroll_items->paye = $this->calculate_paye($payroll_items->gross_pay) - $nhif_relief;

            if($payroll_items->paye < 0){
                $payroll_items->paye = 0;
            }
            $advance_loan = $payroll_items->advance + $payroll_items->loan;
            $payroll_items->netpay = $payroll_items->gross_pay -($payroll_items->paye + $payroll_items->nhif) + $payroll_items->total_other_allowances + $payroll_items->total_benefits 
            - ($advance_loan + $payroll_items->total_other_deduction);
           
        }
        $payroll_items->update();
        //Get payroll and update 
        $payroll = $payroll_items->payroll->first();
        $total_allowance = $payroll->payroll_items()->sum('total_allowance');
        $basic_pay = $payroll->payroll_items()->sum('basic_pay');
        $nhif = $payroll->payroll_items()->sum('nhif');
        $nssf = $payroll->payroll_items()->sum('nssf');
        $paye = $payroll->payroll_items()->sum('paye');
        $netpay = $payroll->payroll_items()->sum('netpay');
        $payroll->allowance_total = $total_allowance;
        $payroll->salary_total = $basic_pay;
        $payroll->paye_total = $paye;
        $payroll->total_nhif = $nhif;
        $payroll->total_nssf = $nssf;
        $payroll->total_netpay = $netpay;
        $payroll->update();
        // foreach ($payroll->payroll_items as $item) {
        //     $total_allowance += $item->total_allowance;
        // }
        //dd($payroll->payroll_items()->sum('total_allowance'));
        return redirect()->back()->with('flash_success', 'Basic Pay Updated Successfully');
    }

    public function update_allowance(Request $request)
    {
        //dd($request->all());
        $payroll_items = PayrollItem::find($request->id);
        //Total tx allowance

        $total_allowance = $request->house_allowance + $request->transport_allowance + $request->other_allowance;
        $payroll_items->house_allowance = $request->house_allowance;
        $payroll_items->transport_allowance = $request->transport_allowance;
        $payroll_items->other_allowance = $request->other_allowance;
        $payroll_items->total_allowance = $total_allowance;
        $total_basic_allowance = $payroll_items->basic_pay + $total_allowance;
        $payroll_items->total_basic_allowance = $total_basic_allowance;
        if($payroll_items->total_basic_allowance){
            $payroll_items->nssf = $this->calculate_nssf($total_basic_allowance);
            $payroll_items->gross_pay = $total_basic_allowance - ($payroll_items->nssf + $payroll_items->tx_deductions);
            // $total_gross += $payroll_items->gross_pay;
            $payroll_items->nhif = $this->calculate_nhif($payroll_items->gross_pay);
            $nhif_relief = 15/100 * $payroll_items->nhif;
            $payroll_items->paye = $this->calculate_paye($payroll_items->gross_pay) - $nhif_relief;

            if($payroll_items->paye < 0){
                $payroll_items->paye = 0;
            }
            $advance_loan = $payroll_items->advance + $payroll_items->loan;
            $payroll_items->netpay = $payroll_items->gross_pay -($payroll_items->paye + $payroll_items->nhif) + $payroll_items->total_other_allowances + $payroll_items->total_benefits 
            - ($advance_loan + $payroll_items->total_other_deduction);
           
        }
        $payroll_items->update();
        //Get payroll and update 
        $payroll = $payroll_items->payroll->first();
        $total_allowances = $payroll->payroll_items()->sum('total_allowance');
        $nhif = $payroll->payroll_items()->sum('nhif');
        $nssf = $payroll->payroll_items()->sum('nssf');
        $paye = $payroll->payroll_items()->sum('paye');
        $netpay = $payroll->payroll_items()->sum('netpay');
        $payroll->allowance_total = $total_allowances;
        $payroll->paye_total = $paye;
        $payroll->total_nhif = $nhif;
        $payroll->total_nssf = $nssf;
        $payroll->total_netpay = $netpay;
        $payroll->update();
        return redirect()->back()->with('flash_success', 'Taxable Allowances Updated Successfully');
    }

    public function update_deduction(Request $request)
    {
        //dd($request->all());
        $payroll_items = PayrollItem::find($request->id);
        $payroll_items->tx_deductions = $request->tx_deductions;
        if($payroll_items->total_basic_allowance){
            $payroll_items->nssf = $this->calculate_nssf($payroll_items->total_basic_allowance);
            $payroll_items->gross_pay = $payroll_items->total_basic_allowance - ($payroll_items->nssf + $request->tx_deductions);
            // $total_gross += $payroll_items->gross_pay;
            $payroll_items->nhif = $this->calculate_nhif($payroll_items->gross_pay);
            $nhif_relief = 15/100 * $payroll_items->nhif;
            $payroll_items->paye = $this->calculate_paye($payroll_items->gross_pay) - $nhif_relief;

            if($payroll_items->paye < 0){
                $payroll_items->paye = 0;
            }
            $advance_loan = $payroll_items->advance + $payroll_items->loan;
            $payroll_items->netpay = $payroll_items->gross_pay -($payroll_items->paye + $payroll_items->nhif) + $payroll_items->total_other_allowances + $payroll_items->total_benefits 
            - ($advance_loan + $payroll_items->total_other_deduction);
           
        }
        $payroll_items->update();
        //Get payroll and update 
        $payroll = $payroll_items->payroll->first();
        $nhif = $payroll->payroll_items()->sum('nhif');
        $nssf = $payroll->payroll_items()->sum('nssf');
        $paye = $payroll->payroll_items()->sum('paye');
        $netpay = $payroll->payroll_items()->sum('netpay');
        $payroll->paye_total = $paye;
        $payroll->total_nhif = $nhif;
        $payroll->total_nssf = $nssf;
        $payroll->total_netpay = $netpay;
        $payroll->update();
        return redirect()->back()->with('flash_success', 'Taxable Deductions Updated Successfully');
    }
    public function update_other(Request $request)
    {
        //dd($request->all());
        $payroll_items = PayrollItem::find($request->id);
        $payroll_items->total_other_allowances = $request->total_other_allowances;
        $payroll_items->total_benefits = $request->total_benefits;
        $payroll_items->loan = $request->loan;
        $payroll_items->advance = $request->advance;
        $payroll_items->total_other_deduction = $request->total_other_deduction;
        $advance_loan = $request->advance + $request->loan;
        $payroll_items->netpay = $payroll_items->gross_pay -($payroll_items->paye + $payroll_items->nhif) + $request->total_other_allowances + $request->total_benefits 
            - ($advance_loan + $request->total_other_deduction);
        $payroll_items->update();
        //Get payroll and update 
        $payroll = $payroll_items->payroll->first();
        $total_other_allowances = $payroll->payroll_items()->sum('total_other_allowances');
        $total_benefits = $payroll->payroll_items()->sum('total_benefits');
        $total_other_deduction = $payroll->payroll_items()->sum('total_other_deduction');
        $netpay = $payroll->payroll_items()->sum('netpay');
        $payroll->other_allowances_total = $total_other_allowances;
        $payroll->other_deductions_total = $total_other_deduction;
        $payroll->other_benefits_total = $total_benefits;
        $payroll->total_netpay = $netpay;
        $payroll->update();
        return redirect()->back()->with('flash_success', 'Other Benefits and Deductions Updated Successfully');
    }
    public function store_nhif(Request $request)
    {
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
        
        $data = $request->only([
            'payroll_id','allowance_total'
        ]);
        $data_items = $request->only([
            'id', 'house_allowance','transport_allowance','other_allowance','total_allowance','total_basic_allowance'
        ]);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
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
        
        $data = $request->only([
            'payroll_id','deduction_total','total_nssf'
        ]);
        $data_items = $request->only([
            'id', 'nssf','nhif','gross_pay','total_sat_deduction','tx_deductions'
        ]);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        
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
            'payroll_id','other_benefits_total','other_deductions_total','other_allowances_total'
        ]);
        $data_items = $request->only([
            'id', 'total_benefits','total_other_deduction','loan','advance','total_other_allowances'
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
        
        $data = $request->only([
            'payroll_id','paye_total'
        ]);
        $data_items = $request->only([
            'id', 'paye','taxable_gross'
        ]);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        
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
                    
                    if ($gross_pay > $bracket->amount_from) {
                        $tax += $bracket->rate / 100 * ($gross_pay - $bracket->amount_from);
                       
                    }
                    
                }
                else {
                    
                    if($i == 0){
                        
                        if($gross_pay > $bracket->amount_from){
                            $tax += $bracket->rate/100 * $bracket->amount_to;
                        }
                       
                    }else{
                        
                        if($gross_pay >= $bracket->amount_from && $gross_pay < $bracket->amount_to){
                            $tax += $bracket->rate/100 * ($gross_pay - $bracket->amount_from);
                           // dd($tax);
                        }
                        elseif($gross_pay >= $bracket->amount_from && $gross_pay > $bracket->amount_to){
                            $tax += $bracket->rate/100 * ($bracket->amount_to - $bracket->amount_from);
                        }
                        
                    }
                    
                }
             }
             if($gross_pay > $first_bracket->amount_to){
                $tax = $tax - $personal_relief;
             }else{
                $tax = $tax - ($first_bracket->rate/100 * $first_bracket->amount_to);
             }
         
        return $tax;
    }

    public function reports($payroll){
        return view('focus.payroll.pages.reports',compact('payroll'));
    }

    public function get_reports(Request $request)
    {
        $payroll = Payroll::find($request->payroll_id);
        $payroll_items = $payroll->payroll_items()->get();
        // aggregate
        $nssf_total = 0;
        $nssf_total = $payroll->payroll_items->sum(DB::raw('nssf')) * 2;
        $nhif_total = $payroll->payroll_items->sum(DB::raw('nhif'));
        $paye_total = $payroll->payroll_items->sum(DB::raw('paye'));
        $netpay_total = $payroll->payroll_items->sum(DB::raw('netpay'));
        $nssf_total = amountFormat($nssf_total);
        $nhif_total = amountFormat($nhif_total);
        $paye_total = amountFormat($paye_total);
        $netpay_total = amountFormat($netpay_total);
        $aggregate = compact('nssf_total', 'nhif_total', 'paye_total', 'netpay_total');
        //dd($payroll_items);
        return Datatables::of($payroll_items)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('employee_id', function ($payroll_items) {
                $employee_id = gen4tid('EMP-', $payroll_items->employee_id);
                return $employee_id;
             })
             ->addColumn('payroll_id', function ($payroll_items) {
                $payroll_id = gen4tid('PYRLL-', $payroll_items->payroll_id);
                return $payroll_id;
             })
            ->addColumn('employee_name', function ($payroll_items) {
                $employee_name = $payroll_items->employee ? $payroll_items->employee->first_name. ' '.$payroll_items->employee->last_name : '';
               return $employee_name;
            })
            ->addColumn('nssf_no', function ($payroll_items) {
                $nssf_no = $payroll_items->hrmmetas ? $payroll_items->hrmmetas->nssf : '';
               return $nssf_no;
            })
            ->addColumn('kra_pin', function ($payroll_items) {
                $kra_pin = $payroll_items->hrmmetas ? $payroll_items->hrmmetas->kra_pin : '';
               return $kra_pin;
            })
            ->addColumn('nhif_no', function ($payroll_items) {
                $nhif_no = $payroll_items->hrmmetas ? $payroll_items->hrmmetas->nhif : '';
               return $nhif_no;
            })
            ->addColumn('basic_pay', function ($payroll_items) {
                return amountFormat($payroll_items->basic_pay);
            })
            ->addColumn('absent_days', function ($payroll_items) {
                return $payroll_items->absent_days;
            })
            ->addColumn('house_allowance', function ($payroll_items) {
                return amountFormat($payroll_items->house_allowance);
            })
            ->addColumn('total_allowance', function ($payroll_items) {
                return amountFormat($payroll_items->total_allowance);
            })
            ->addColumn('transport_allowance', function ($payroll_items) {
                return amountFormat($payroll_items->transport_allowance);
            })
            ->addColumn('other_allowance', function ($payroll_items) {
                return amountFormat($payroll_items->other_allowance);
            })
            ->addColumn('gross_pay', function ($payroll_items) {
                return amountFormat($payroll_items->gross_pay -$payroll_items->tx_deductions);
            })
            ->addColumn('nssf', function ($payroll_items) {
                return amountFormat($payroll_items->nssf * 2);
            })
            ->addColumn('tx_deductions', function ($payroll_items) {
                return amountFormat($payroll_items->tx_deductions);
            })
            ->addColumn('paye', function ($payroll_items) {
                return amountFormat($payroll_items->paye);
            })
            ->addColumn('taxable_gross', function ($payroll_items) {
                return amountFormat($payroll_items->taxable_gross);
            })
            ->addColumn('total_other_allowances', function ($payroll_items) {
                return amountFormat($payroll_items->total_other_allowances);
            })
            ->addColumn('total_benefits', function ($payroll_items) {
                return amountFormat($payroll_items->total_benefits);
            })
            ->addColumn('loan', function ($payroll_items) {
                return amountFormat($payroll_items->loan);
            })
            ->addColumn('advance', function ($payroll_items) {
                return amountFormat($payroll_items->advance);
            })
            ->addColumn('total_other_deductions', function ($payroll_items) {
                return amountFormat($payroll_items->total_other_deductions);
            })
            ->addColumn('netpay', function ($payroll_items) {
                return amountFormat($payroll_items->netpay);
            })
            ->addColumn('aggregate', function () use($aggregate) {
                return $aggregate;
            })
            ->make(true);
    }

}
