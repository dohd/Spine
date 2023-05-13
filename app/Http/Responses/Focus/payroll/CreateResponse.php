<?php

namespace App\Http\Responses\Focus\payroll;

use Illuminate\Contracts\Support\Responsable;
use App\models\hrm\Hrm;
use App\Models\deduction\Deduction;

class CreateResponse implements Responsable
{
    /**
     * To Response
     *
     * @param \App\Http\Requests\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function toResponse($request)
    {
        // $employees = Hrm::with(['employees_salary' => function ($q){
        //     $q->where('status', 'ongoing');
        // }])->get();
        
        $startDate = '2023-05-01';
        $endDate = '2023-05-30';
        $employees = Hrm::with(['employees_salary' => function ($q){
            $q->where('contract_type', 'permanent');
        }])->with(['attendance' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate])
                    ->where('status', 'absent');
                    
        }])->with(['advance_payment' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('approve_date', [$startDate, $endDate])
                    ->where('status', 'approved');
                    
        }])
        ->get();
        //dd($employees);
        foreach ($employees as $employee) {
           //Counting Absent Days
            $employee->absent_days = $employee->attendance->count();

            if($employee->employees_salary){
                //Gross pay from basic and Allowances
                $employee->gross_pay = $employee->employees_salary->basic_pay + $employee->employees_salary->house_allowance + $employee->employees_salary->transport_allowance;
                //Calculate Attendance amount to be deducted
                $employee->attendance_rate = $employee->gross_pay / 30 * $employee->absent_days;
                //Gross less attendance
                $employee->gross_after_attendance = $employee->gross_pay - $employee->attendance_rate;
                $employee->nssf = $this->calculate_nssf($employee->gross_after_attendance);
                //gross less nssf
                $gross_less_nssf = $employee->gross_after_attendance - $employee->nssf;
                $employee->paye = $this->calculate_paye($gross_less_nssf);
                $employee->nhif = $this->calculate_nhif($employee->gross_after_attendance);
                $saturtory = $employee->paye + $employee->nhif;
                $employee->gross_less_deductions = $gross_less_nssf - $saturtory;
                $employee->gross_less_advance = 0;
                if($employee->advance_payment){
                    $employee->advance = $employee->advance_payment->approve_amount;
                    $advance = $employee->gross_less_deductions - $employee->advance;
                    $employee->gross_less_advance = $advance;
                }else{
                    $employee->gross_less_advance = $employee->gross_less_deductions;
                }
            }
            
           // dd($employee->employee);
            
        }
        return view('focus.payroll.create', compact('employees'));
    }

    public function calculate_paye($gross_pay)
    {
         //Get PAYE brackets
         $tax = 0;
         $paye_brackets = Deduction::where('deduction_id','3')->get();
         
         if($gross_pay > 24000){
            foreach ($paye_brackets as $i => $bracket) {
                if ($i > 0) {
                    if ($gross_pay > $bracket->amount_from) {
                        $tax += $bracket->rate / 100 * ($gross_pay - $bracket->amount_from);
                        
                    }
                }else {
                    if($gross_pay > $bracket->amount_to){
                        $tax += 25/100 * ($bracket->amount_to - $bracket->amount_from);
                    }
                    $tax += $bracket->rate / 100 * ($bracket->amount_from);
                }
             }
         }
         if($tax > 0)
            return $tax - 2655;
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
}