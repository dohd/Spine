<?php

namespace App\Repositories\Focus\payroll;

use DB;
use Carbon\Carbon;
use App\Models\payroll\Payroll;
use App\Models\payroll\PayrollItem;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use App\Models\account\Account;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;

/**
 * Class payrollRepository.
 */
class PayrollRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Payroll::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
        
        $q->when(request('month'), function ($q) {            
            //dd(request('month'));
            $year = Carbon::createFromFormat('Y-m', request('month'))->format('Y');
            $month = Carbon::createFromFormat('Y-m', request('month'))->format('m');
             $q->whereYear('payroll_month', $year)->whereMonth('payroll_month', $month);
        });
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function create(array $input)
    {
        $year = Carbon::createFromFormat('Y-m', $input['payroll_month'])->format('Y');
        $month = Carbon::createFromFormat('Y-m', $input['payroll_month'])->format('m');
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = Carbon::createFromDate($year, $month, $startDate->daysInMonth);
        //$working_days = $startDate->diffInWeekdays($endDate);
        $working_days = $startDate->diffInDaysFiltered(function (Carbon $date) {
            return $date->isWeekday() || $date->isSaturday();
        }, $endDate);
        $total_month_days = $startDate->daysInMonth;
        //dd();
        $input['working_days'] = $working_days;
        $input['total_month_days'] = $total_month_days;
        $input['total_month_days'] = $total_month_days;
        $input['payroll_month'] = Carbon::createFromFormat('Y-m', $input['payroll_month'])->format('Y-m-d');
        //dd($input);
        $input = array_map( 'strip_tags', $input);
        $res = Payroll::create($input);
        if ($res) {
            return $res->id;
        }
        throw new GeneralException(trans('exceptions.backend.payrolls.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param payroll $payroll
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Payroll $payroll, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($payroll->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.payrolls.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param payroll $payroll
     * @throws GeneralException
     * @return bool
     */
    public function delete(Payroll $payroll)
    {
        if ($payroll->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.payrolls.delete_error'));
    }
    public function approve_payroll(array $input)
    {
        $payroll = $input['payroll'];
        if($payroll->status == 'approved'){
            $this->post_transaction($payroll);
            
        }
        //unset($payroll['account']);
        $payroll->update();
        
        throw new GeneralException('Approval Payroll Failed');
    }
    public function create_basic(array $input)
    {
         
        DB::beginTransaction();
       // dd($input);
        $data = $input['data'];
        foreach ($data as $key => $val) {
            $rate_keys = [
                'salary_total'
            ];
        }
        $result = Payroll::find($data['payroll_id']);
        $result->salary_total = $data['salary_total'];
        $result->processing_date = date_for_database($data['processing_date']);
        $result->update();

        //dd($result);
        $data_items = $input['data_items'];
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'ins' => auth()->user()->ins,
                'user_id' => auth()->user()->id,
                'payroll_id' => $result->id,
            ]);
        }, $data_items);
        //dd($data_items);
        PayrollItem::insert($data_items);
        
        
        if ($result) {
            DB::commit();
            return $result;   
        }

        DB::rollBack();
        throw new GeneralException(trans('exceptions.backend.purchasedatas.create_error'));
    }
    public function create_allowance(array $input)
    {
         
        DB::beginTransaction();
       // dd($input);
        $data = $input['data'];
        foreach ($data as $key => $val) {
            $rate_keys = [
                'allowance_total'
            ];
        }
        $result = Payroll::find($data['payroll_id']);
        $result->allowance_total = $data['allowance_total'];
        $result->update();

        //dd($result);
        $data_items = $input['data_items'];
        foreach ($data_items as $item) {
            $item = array_replace($item, [
                'ins' => $result->ins,
                'user_id' => $result->id,
            ]);
           // dd($item);
            $data_item = PayrollItem::firstOrNew(['id'=> $item['id']]);
            $data_item->fill($item);
            if (!$data_item->id) unset($data_item->id);
            $data_item->save();
        }
        
        
        
        
        if ($result) {
            DB::commit();
            return $result;   
        }

        DB::rollBack();
        throw new GeneralException(trans('exceptions.backend.purchasedatas.create_error'));
    }
    public function create_deduction(array $input)
    {
         
        DB::beginTransaction();
       // dd($input);
        $data = $input['data'];
        foreach ($data as $key => $val) {
            $rate_keys = [
                'deduction_total','total_nssf'
            ];
        }
        $result = Payroll::find($data['payroll_id']);
        $result->deduction_total = $data['deduction_total'];
        $result->total_nssf = $data['total_nssf'];
        $result->update();

        //dd($result);
        $data_items = $input['data_items'];
        foreach ($data_items as $item) {
            $item = array_replace($item, [
                'ins' => $result->ins,
                'user_id' => $result->id,
            ]);
           // dd($item);
            $data_item = PayrollItem::firstOrNew(['id'=> $item['id']]);
            $data_item->fill($item);
            if (!$data_item->id) unset($data_item->id);
            $data_item->save();
        }
        
        
        
        
        if ($result) {
            DB::commit();
            return $result;   
        }

        DB::rollBack();
        throw new GeneralException(trans('exceptions.backend.payroll.create_error'));
    }

    public function create_nhif(array $input)
    {
         
        DB::beginTransaction();
       // dd($input);
        $data = $input['data'];
        foreach ($data as $key => $val) {
            $rate_keys = [
                'total_nhif'
            ];
        }
        $result = Payroll::find($data['payroll_id']);
        $result->total_nhif = $data['total_nhif'];
        $result->update();

        
        if ($result) {
            DB::commit();
            return $result;   
        }

        DB::rollBack();
        throw new GeneralException(trans('exceptions.backend.payroll.create_error'));
    }
   
    public function create_paye(array $input)
    {
         
        DB::beginTransaction();
       // dd($input);
        $data = $input['data'];
        foreach ($data as $key => $val) {
            $rate_keys = [
                'paye_total'
            ];
        }
        $result = Payroll::find($data['payroll_id']);
        $result->paye_total = $data['paye_total'];
        $result->update();

        //dd($result);
        $data_items = $input['data_items'];
        foreach ($data_items as $item) {
            $item = array_replace($item, [
                'ins' => $result->ins,
                'user_id' => $result->id,
            ]);
           // dd($item);
            $data_item = PayrollItem::firstOrNew(['id'=> $item['id']]);
            $data_item->fill($item);
            if (!$data_item->id) unset($data_item->id);
            $data_item->save();
        }
        
        
        
        
        if ($result) {
            DB::commit();
            return $result;   
        }

        DB::rollBack();
        throw new GeneralException(trans('exceptions.backend.payroll.create_error'));
    }
    public function create_other_deduction(array $input)
    {
         
        DB::beginTransaction();
       // dd($input);
        $data = $input['data'];
        foreach ($data as $key => $val) {
            $rate_keys = [
                'other_benefits_total',
                'other_deductions_total',
                'other_allowances_total'
            ];
        }
        $result = Payroll::find($data['payroll_id']);
        $result->other_benefits_total = $data['other_benefits_total'];
        $result->other_deductions_total = $data['other_deductions_total'];
        $result->other_allowances_total = $data['other_allowances_total'];
        $result->update();

        //dd($result);
        $data_items = $input['data_items'];
        foreach ($data_items as $item) {
            $item = array_replace($item, [
                'ins' => $result->ins,
                'user_id' => $result->id,
            ]);
           // dd($item);
            $data_item = PayrollItem::firstOrNew(['id'=> $item['id']]);
            $data_item->fill($item);
            if (!$data_item->id) unset($data_item->id);
            $data_item->save();
        }
        
        
        
        
        if ($result) {
            DB::commit();
            return $result;   
        }

        DB::rollBack();
        throw new GeneralException(trans('exceptions.backend.payroll.create_error'));
    }
    public function create_summary(array $input)
    {
         
        DB::beginTransaction();
       // dd($input);
        $data = $input['data'];
        foreach ($data as $key => $val) {
            $rate_keys = [
                'total_netpay',
                
            ];
        }
        $result = Payroll::find($data['payroll_id']);
        $result->total_netpay = $data['total_netpay'];
        $result->update();

        //dd($result);
        $data_items = $input['data_items'];
        foreach ($data_items as $item) {
            $item = array_replace($item, [
                'ins' => $result->ins,
                'user_id' => $result->id,
            ]);
           // dd($item);
            $data_item = PayrollItem::firstOrNew(['id'=> $item['id']]);
            $data_item->fill($item);
            if (!$data_item->id) unset($data_item->id);
            $data_item->save();
        }
        
        
        
        
        if ($result) {
            DB::commit();
            return $result;   
        }

        DB::rollBack();
        throw new GeneralException(trans('exceptions.backend.payroll.create_error'));
    }

    public function post_transaction($payroll)
    {
       // dd($payroll['account']);
        // credit salary payable (liability)
        $account = Account::where('system', 'salary_payable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'salaries')->first(['id', 'code']);
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $payroll->total_netpay,
            'tr_date' => $payroll->processing_date,
            'due_date' => $payroll->approval_date,
            'user_id' => $payroll->user_id,
            'note' => $payroll->approval_note,
            'ins' => $payroll->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $payroll->id,
            'user_type' => 'company',
            'is_primary' => 1
        ];
        Transaction::create($cr_data); 
        
        // credit paye
        unset($cr_data['credit'], $cr_data['is_primary']);
        
        $paye_account = Account::where('system', 'paye_payable')->first(['id']);
        $dr_paye = array_replace($cr_data, [
            'account_id' => $paye_account->id,
            'credit' => $payroll->paye_total,
        ]); 
        Transaction::create($dr_paye);
        //credit nhif
        $nhif_account = Account::where('system', 'nhif_payable')->first(['id']);
        $dr_nhif = array_replace($cr_data, [
            'account_id' => $nhif_account->id,
            'credit' => $payroll->total_nhif,
        ]);
         Transaction::create($dr_nhif);
         //credit nssf payable *2
        $nssf_account = Account::where('system', 'nssf_payable')->first(['id']);
        $dr_nssf = array_replace($cr_data, [
            'account_id' => $nssf_account->id,
            'credit' => $payroll->total_nssf * 2,
        ]);
         Transaction::create($dr_nssf);
          //debit salary expense
        $gross_account = Account::where('system', 'salary')->first(['id']);
        $dr_gross = array_replace($cr_data, [
            'account_id' => $gross_account->id,
            'debit' => $payroll->total_netpay,
        ]);
         Transaction::create($dr_gross);
        $nssf_expense = Account::where('system', 'nssf')->first(['id']);
        $dr_nssf = array_replace($cr_data, [
            'account_id' => $nssf_expense->id,
            'debit' => $payroll->total_nssf,
        ]);
          //credit payables
        $payable_account = Account::where('system', 'payable')->first(['id']);
        $total = $payroll->salary_total + $payroll->allowance_total + $payroll->other_allowances_total + $payroll->other_benefits_total - $payroll->other_deductions_total;
        $cr_payable = array_replace($cr_data, [
            'account_id' => $payable_account->id,
            'credit' => $total,
        ]);
        Transaction::create($cr_payable);
        
        
        aggregate_account_transactions();
    }
    public function generate_payroll($payroll)
    {
        //Debit payables
        $payable_account = Account::where('system', 'payable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'salaries')->first(['id', 'code']);
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $payroll->total_netpay,
            'tr_date' => $payroll->processing_date,
            'due_date' => $payroll->approval_date,
            'user_id' => $payroll->user_id,
            'note' => $payroll->approval_note,
            'ins' => $payroll->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $payroll->id,
            'user_type' => 'company',
            'is_primary' => 1
        ];
        
        // debit paye
        unset($cr_data['credit'], $cr_data['is_primary']);
         $dr_payable = array_replace($cr_data, [
             'account_id' => $payable_account->id,
             'debit' => $payroll->total_netpay,
         ]);
         Transaction::create($dr_payable);
         //credit bank
         $gross_pay = $payroll->salary_total + $payroll->allowance_total - $payroll->deduction_total;
         //$payable_account = Account::where('system', 'bank')->first(['id']);
         $cr_bank = array_replace($cr_data, [
             'account_id' => $payroll['account'],
             'credit' => $gross_pay,
         ]);
         //dd($cr_bank);
         Transaction::create($cr_bank);
    }

}
