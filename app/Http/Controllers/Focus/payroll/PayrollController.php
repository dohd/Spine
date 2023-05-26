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
        //dd($request->payroll_id);
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
                $employee_name = $payroll_items->employee->first_name;
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
                //dd($item->nhif);
                if($item->paye < 0){
                    $item->paye = 0;
                }
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
        $users = $payroll->payroll_items()->get();

        foreach ($users as $user) {
            // Generate the payslip for the user
             //$payslip =
             $data = [
                'payroll_items' => $user,
            ];
        
            //$pdf = \PDF::loadView('payslip', $data);
            
            $html = view('focus.bill.payslip', $data)->render();
            $pdf = new \Mpdf\Mpdf(config('pdf') + ['margin_left' => 4, 'margin_right' => 4]);
            $pdf->WriteHTML($html);
            $pdfFilePath = 'C:\LaravelApps\Spine\storage\app\public\files\payslip.pdf';
            $input=array();
            $input['text']='test Message';
            $input['subject']='Invoice';
            $input['mail_to']='robertmwenja4@gmail.com';
            $input['customer_name']='Robert Mwenja';
    
            $mailer = new RosemailerRepository;
            $result= $mailer->send($input['text'], $input);
           // $pdf->save($pdfFilePath);
            // Save the payslip as a PDF file
            // $pdfFilePath = '/path/to/payslips/' . $user->id . '_payslip.pdf';
            // $payslip->saveAsPdf($pdfFilePath);

            Mail::to($user->employee->email)
                ->send(new SendPayslipEmail($pdfFilePath, $user->employee->first_name));
        }
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
                $tax = $tax - ($first_bracket->rate/100 * $first_bracket->amount_to);
             }
         //dd($tax);
        return $tax;
    }

}
