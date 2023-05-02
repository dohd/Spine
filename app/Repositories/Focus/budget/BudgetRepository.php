<?php

namespace App\Repositories\Focus\budget;

use App\Exceptions\GeneralException;
use App\Models\project\Budget;
use App\Models\project\BudgetItem;
use App\Models\project\BudgetSkillset;
use App\Repositories\BaseRepository;
use DB;

class BudgetRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Budget::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        // project quote filter
        $q->when(request('project_id'), function($q) {
            if (request('quote_ids')) $q->whereIn('quote_id', explode(',', request('quote_ids')));
            else $q->whereIn('id', [0]);
        });
            
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return Budget $budget
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();
        
        $data = $input['data'];
        $keys = array('quote_total', 'budget_total', 'labour_total');
        foreach ($data as $key => $val) {
            if (in_array($key, $keys, 1)) 
                $data[$key] = numberClean($val);
        }                
        $result = Budget::create($data);

        $data_items = $input['data_items'];
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'budget_id' => $result->id,
                'price' => numberClean($v['price'])
            ]);
        }, $data_items); 
        BudgetItem::insert($data_items);

        $data_skillset = $input['data_skillset'];
        foreach ($data_skillset as $item) {
            $item = array_replace($item, [
                'charge' => numberClean($item['charge']),
                'budget_id' => $result->id,
                'quote_id' => $result->quote_id
            ]);
            $skillset = BudgetSkillset::firstOrNew(['id' => $item['skillitem_id']]);
            $skillset->fill($item);
            if (!$skillset->id) unset($skillset->id);
            unset($skillset->skillitem_id);
            $skillset->save();
        }
        
        if ($result) {
            DB::commit();
            return $result; 
        }
            
        throw new GeneralException(trans('exceptions.backend.leave_category.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Budget $budget
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(Budget $budget, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        $keys = array('quote_total', 'budget_total', 'labour_total');
        foreach ($data as $key => $val) {
            if (in_array($key, $keys)) 
                $data[$key] = numberClean($val);
        }   
        $result = $budget->update($data);

        $data_items = $input['data_items'];
        // delete omitted line items
        $budget->items()->whereNotIn('id', array_map(fn($v) => $v['item_id'], $data_items))->delete();
        // new or update item
        foreach($data_items as $item) {
            $item = array_replace($item, [
                'price' => numberClean($item['price']),
                'new_qty' => numberClean($item['new_qty']),
                'budget_id' => $budget->id,
            ]);
            $new_item = BudgetItem::firstOrNew(['id' => $item['item_id']]);
            $new_item->fill($item);
            if (!$new_item->id) unset($new_item->id);
            unset($new_item->item_id);
            $new_item->save();
        }

        $data_skillset = $input['data_skillset'];
        // delete omitted labour items
        $budget->skillsets()->whereNotIn('id', array_map(fn($v) => $v['skillitem_id'], $data_skillset))->delete();
        // create or update items
        foreach($data_skillset as $item) {
            $item['charge'] = numberClean($item['charge']);
            $new_item = BudgetSkillset::firstOrNew([
                'id' => $item['skillitem_id'],
                'budget_id' => $budget->id,
            ]);
            $new_item->fill($item);
            if (!$new_item->id) unset($new_item->id);
            unset($new_item->skillitem_id);
            $new_item->save();
        }
        
        if ($result) {
            DB::commit();
            return $result;
        }        

        throw new GeneralException(trans('exceptions.backend.leave_category.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Budget $budget
     * @throws GeneralException
     * @return bool
     */
    public function delete(Budget $budget)
    {
        if ($budget->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.leave_category.delete_error'));
    }
}
