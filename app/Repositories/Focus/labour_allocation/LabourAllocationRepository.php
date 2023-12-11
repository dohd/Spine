<?php

namespace App\Repositories\Focus\labour_allocation;

use App\Exceptions\GeneralException;
use App\Models\labour_allocation\LabourAllocation;
use App\Models\labour_allocation\LabourAllocationItem;
use App\Models\project\Project;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class LabourAllocationRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = LabourAllocation::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this::query();
         $q->when(request('client_id'), function ($q) {
            $q->whereHas('project', function ($q) {
                $q->whereHas('quotes', function ($q) {
                    $q->where('customer_id', request('client_id'));
                });
            });
        });
        
        return $q->get();
    }
    
    /**
     * Employee Labour Report
     * 
     * */
    public function getForEmployeeSummary()
    {
        $q = LabourAllocationItem::query();
        $q->when(request('start_date') && request('end_date'), function ($q) {
            $q->whereBetween('date', array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]));
        })
        ->when(request('employee_id'), fn($q) => $q->where('employee_id', request('employee_id')))
        ->when(request('labour_month'), function($q) {
            $dates = explode('-', request('labour_month'));
            if (count($dates) == 2 && intval(@$dates[0]) && intval(@$dates[1])) {
                $q->whereMonth('date', $dates[0])->whereYear('date', $dates[1]);
            }
        });
        
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return LabourAllocation $labour_allocation
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();
        
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date']))
                $data[$key] = date_for_database($val);
            if (in_array($key, ['hrs']))
                $data[$key] = numberClean($val);
        }   
        
        // date validation
        if (strtotime($data['date']) > strtotime(date('Y-m-d')))
            throw ValidationException::withMessages(['date' => 'Future date not allowed']);
            
        // $one_week_earlier = strtotime(date('Y-m-d')) - (7 * 24 * 60 * 60);
        // if (strtotime($data['date']) < $one_week_earlier) 
        //     throw ValidationException::withMessages(['date' => 'Date is not within 1 weeks window period']);
            
        // if ($data['hrs'] > 14) throw ValidationException::withMessages(['hrs' => 'Hours beyond 14 hours/day not allowed.']);
        
        $result = LabourAllocation::create($data);
        
        $data_items = $input['data_items'];
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'ref_type' => $result['ref_type'],
                'labour_id' => $result['id'],
                'date' => $result['date'],
                'type' => $result['type'],
                'hrs' => $result['hrs'],
                'note' => $result['note'],
                'is_payable' => $result['is_payable'],
                'user_id' => $result['user_id'], 
                'ins' => $result['ins'],
            ]);
        }, $data_items);
        $result = LabourAllocationItem::insert($data_items);
        
        if ($result) {
            DB::commit();
            return $result; 
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param LabourAllocation $labour_allocation
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(LabourAllocation $labour_allocation, array $input)
    {
        //dd($input);
        DB::beginTransaction();
        
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date']))
                $data[$key] = date_for_database($val);
            if (in_array($key, ['hrs']))
                $data[$key] = numberClean($val);
        }   
        
        // if ($data['hrs'] > 14) throw ValidationException::withMessages(['hrs' => 'Hours beyond 14 hours/day not allowed.']);
        
        $labour_allocation->update($data);
        
         $data_items = $input['data_items'];
        // delete omitted items
        $item_ids = array_map(fn($v) => $v['id'], $data_items);
        $labour_allocation->items()->whereNotIn('id', $item_ids)->delete();
        // update or create new items
        foreach ($data_items as $item) {
            $item = array_replace($item, [
                'ref_type' => $labour_allocation['ref_type'],
                'labour_id' => $labour_allocation['id'],
                'date' => $labour_allocation['date'],
                'type' => $labour_allocation['type'],
                'hrs' => $labour_allocation['hrs'],
                'note' => $labour_allocation['note'],
                'is_payable' => $labour_allocation['is_payable'],
                'user_id' => $labour_allocation['user_id'], 
                'ins' => $labour_allocation['ins'],
            ]);
            $data_item = LabourAllocationItem::firstOrNew(['id' => $item['id']]);
            $data_item->fill($item);
            if (!$data_item->id) unset($data_item->id);
            $data_item->save();                
        }
        
        if ($labour_allocation) {
            DB::commit();
            return true;
        }        
    }

    /**
     * For deleting the respective model from storage
     *
     * @param LabourAllocation $labour_allocation
     * @throws GeneralException
     * @return bool
     */
    public function delete(LabourAllocation $labour_allocation)
    {   
        if ($labour_allocation->delete() && $labour_allocation->items->each->delete()) 
            return true;
    }
}
