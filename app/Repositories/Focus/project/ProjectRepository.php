<?php

namespace App\Repositories\Focus\project;

use App\Models\project\Project;
use App\Exceptions\GeneralException;
use App\Models\project\Budget;
use App\Models\project\BudgetItem;
use App\Models\project\BudgetSkillset;
use App\Models\project\ProjectQuote;
use App\Models\quote\Quote;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Class ProjectRepository.
 */
class ProjectRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Project::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        return $this->query()->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @return bool
     * @throws GeneralException
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data_items = $input['data_items'];
        $project_quote_exists = ProjectQuote::whereIn('quote_id', $data_items)->count();
        if ($project_quote_exists) throw ValidationException::withMessages(['Tagged Quote / PI already attached to a project']);

        $data = $input['data'];
        $tid = Project::where('ins', auth()->user()->ins)->max('tid');
        if ($data['tid'] <= $tid) $data['tid'] = $tid+1;
        $data['main_quote_id'] = $data_items[0];
        
        $result = Project::create($data);

        // create project_quote and update related foreign key in quote
        foreach ($data_items as $quote_id) {
            $project_quote_id = ProjectQuote::insertGetId(['quote_id' => $quote_id, 'project_id' => $result->id]);
            Quote::find($quote_id)->update(compact('project_quote_id'));
        }
        
        if ($result) {
            DB::commit();
            return $result;
        }

        throw new GeneralException(trans('exceptions.backend.projects.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Project $project
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($project, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        if (isset($data['status'])) {
            if ($data['status'] == 'closed') {
                $data = array_replace($data, [
                    'end_date' => date('Y-m-d'),
                    'ended_by' => auth()->user()->id,
                ]);
            } else {
                $data = array_replace($data, [
                    'end_date' => null,
                    'ended_by' => null,
                ]);
            }
        }

        $data_items = array_filter($input['data_items'], fn($v) => $v);
        if ($data_items) $data['main_quote_id'] = $data_items[0];

        // dd($data, $data_items);
        $result = $project->update($data);

        // create or update project quotes
        ProjectQuote::where('project_id', $project->id)->whereNotIn('quote_id', $data_items)->delete();
        foreach($data_items as $quote_id) {
            $item = ProjectQuote::firstOrNew(['project_id' => $project->id, 'quote_id' => $quote_id]);
            Quote::find($quote_id)->update(['project_quote_id' => $item->id]);
            $item->save();
        }

        if ($result) {
            DB::commit();
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.projects.update_error'));
    }

    /**
     * For delete respective model from storage
     * 
     *  @param \App\Models\project\Project $project 
     */
    public function delete($project)
    {  
        $budgeted_quotes = $project->quotes()->whereHas('budget')->get();
        if ($budgeted_quotes->count())
            throw ValidationException::withMessages(['Project has been budgeted on!']);
        if ($project->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.projects.delete_error'));
    }    

    /**
     * store a newly created Project Quote Budget
     * @param Request request
     */
    public function create_budget($input)
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
    }
    
    /**
     * Update a newly created Project Quote Budget
     * @param Request request
     */
    public function update_budget($budget, $input)
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
    }   
}
