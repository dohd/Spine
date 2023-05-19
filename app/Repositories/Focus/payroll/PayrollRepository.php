<?php

namespace App\Repositories\Focus\payroll;

use DB;
use Carbon\Carbon;
use App\Models\payroll\Payroll;
use App\Models\payroll\PayrollItem;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

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

        return $this->query()
            ->get();
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
                'deduction_total'
            ];
        }
        $result = Payroll::find($data['payroll_id']);
        $result->deduction_total = $data['deduction_total'];
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
}
