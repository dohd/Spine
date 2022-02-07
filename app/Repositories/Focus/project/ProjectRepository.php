<?php

namespace App\Repositories\Focus\project;

use App\Models\event\EventRelation;
use App\Models\project\Project;
use App\Exceptions\GeneralException;
use App\Models\project\Budget;
use App\Models\project\BudgetItem;
use App\Models\project\BudgetSkillset;
use App\Models\project\ProjectQuote;
use App\Models\quote\Quote;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

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
    public function getForDataTable($c = true)
    {
        $q = $this->query()->withoutGlobalScopes();

        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @return bool
     * @throws GeneralException
     */
    public function create(array $data)
    {
        DB::beginTransaction();

        $project = $data['project'];
        $project['ins'] = auth()->user()->ins;
        $project['status'] = 1;

        $main_quote = $data['project_quotes']['main_quote'];
        $project['main_quote_id'] = $main_quote;
        $ref = Project::orderBy('project_number', 'desc')->first('project_number');
        if ($ref && $project['project_number'] <= $ref->project_number) {
            $project['project_number'] = $ref->project_number + 1;
        }
        $result = Project::create($project);

        // project quotes
        $proj_quotes[] = array('project_id' => $result['id'], 'quote_id' => $main_quote);
        if (isset($data['project_quotes']['other_quote'])) {
            $other_quote = $data['project_quotes']['other_quote'];
            foreach ($other_quote as $value) {
                $proj_quotes[] = array('project_id' => $result['id'], 'quote_id' => $value);
            }            
        }
        // create project quote and update related foreign key
        foreach($proj_quotes as $value) {
            $id = ProjectQuote::insertGetId($value);
            Quote::find($value['quote_id'])->update(['project_quote_id' => $id]);
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
        DB::beginTransaction();
        // update project
        $quotes = $input['quotes'];
        $data = array_merge($input['data'], ['main_quote_id' => $quotes['main_quote']]);
        $result = $project->update($data);

        // project quotes
        $proj_quotes[] = array('project_id' => $project->id, 'quote_id' => $quotes['main_quote']);
        if (isset($input['quotes']['other_quote'])) {
            $other_quote = $input['quotes']['other_quote'];
            foreach ($other_quote as $value) {
                $proj_quotes[] = array('project_id' => $project->id, 'quote_id' => $value);
            }         
        }
        // create or update project quotes
        foreach($proj_quotes as $value) {
            $quote = ProjectQuote::firstOrNew($value);
            $quote->save();
            Quote::find($value['quote_id'])->update(['project_quote_id' => $quote->id]);
        }

        if ($result) {
            DB::commit();
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.projects.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Project $project
     * @return bool
     * @throws GeneralException
     */
    public function delete($project)
    {
        // $valid_project_creator = isset($project->creator) && $project->creator->id == auth()->user()->id;
        if (true) {
            if ($project->delete()) {
                $event_rel = EventRelation::where(['related' => 1, 'r_id' => $project->id])->first();
                if (isset($event_rel)) {
                    $event_rel->event->delete();
                    $event_rel->delete();
                }

                return true;
            }
        }

        throw new GeneralException(trans('exceptions.backend.projects.delete_error'));
    }

    /**
     * store a newly created Project Quote Budget
     * @param Request request
     */
    public function store_budget($input)
    {                
        DB::beginTransaction();
        // convert strings to float
        $keys = array('quote_total', 'budget_total', 'labour_total');
        foreach ($input['budget'] as $key => $val) {
            if (in_array($key, $keys)) {
                $input['budget'][$key] = numberClean($val);
            }
        }
                       
        $budget = Budget::create($input['budget']);

        // budget items
        $budget_items = array();
        $item = $input['budget_items'];
        for ($i = 0; $i < count($item['product_name']); $i++) {
            $row = array('budget_id' => $budget->id);
            foreach (array_keys($item) as $key) {
                if (isset($item[$key][$i])) {
                    $val = $item[$key][$i];
                    $row[$key] = ($key == 'price') ? numberClean($val) : $val;
                }
                else $row[$key] = NULL;
            }
            $budget_items[] = $row;
        }
        BudgetItem::insert($budget_items);

        // budget skillset
        $budget_skillset = array();
        $item = $input['budget_skillset'];
        for ($i = 0; $i < count($item['skill']); $i++) {
            $row = array('budget_id' => $budget->id);
            foreach (array_keys($item) as $key) {
                if (isset($item[$key][$i])) {
                    $row[$key] = $item[$key][$i];
                }
            }
            $budget_skillset[] = $row;
        }
        BudgetSkillset::insert($budget_skillset);

        if ($budget) return DB::commit();
    }
    
    /**
     * Update a newly created Project Quote Budget
     * @param Request request
     */
    public function update_budget($budget, $input)
    {   
        DB::beginTransaction();
        // convert strings to float
        $keys = array('quote_total', 'budget_total', 'labour_total');
        foreach ($input['budget'] as $key => $val) {
            if (in_array($key, $keys)) {
                $input['budget'][$key] = numberClean($val);
            }
        }
        $budget->update($input['budget']);

        // budget items
        $budget_items = array();
        $item = $input['budget_items'];
        for ($i = 0; $i < count($item['product_name']); $i++) {
            $row = array('budget_id' => $budget->id);
            foreach (array_keys($item) as $key) {
                if (isset($item[$key][$i])) {
                    $val = $item[$key][$i];
                    $row[$key] = ($key == 'price') ? numberClean($val) : $val;
                }
                else $row[$key] = NULL;
            }
            $budget_items[] = $row;
        }
        // update or create new budget_item
        foreach($budget_items as $item) {
            $budget_item = BudgetItem::firstOrNew([
                'id' => $item['item_id'],
                'budget_id' => $item['budget_id'],
            ]);
            // assign properties to the item
            foreach($item as $key => $value) {
                $budget_item[$key] = $value;
            }
            // remove stale attributes and save
            unset($budget_item['item_id']);
            if ($budget_item['id'] == 0) unset($budget_item['id']);
            $budget_item->save();
        }

        // budget skillset
        $budget_skillset = array();
        $item = $input['budget_skillset'];
        for ($i = 0; $i < count($item['skill']); $i++) {
            $row = array('budget_id' => $budget->id);
            foreach (array_keys($item) as $key) {
                $row[$key] = $item[$key][$i];
            }
            $budget_skillset[] = $row;
        }
        // update or create new budget_skillset
        foreach($budget_skillset as $item) {
            $skillset = BudgetSkillset::firstOrNew([
                'id' => $item['skillitem_id'],
                'budget_id' => $item['budget_id'],
            ]);
            // assign properties to the item
            foreach($item as $key => $value) {
                $skillset[$key] = $value;
            }
            // remove stale attributes and save
            unset($skillset['skillitem_id']);
            if ($skillset['id'] == 0) unset($skillset['id']);
            $skillset->save();
        }
        
        if ($budget) return DB::commit();
    }             
}
