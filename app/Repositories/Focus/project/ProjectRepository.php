<?php

namespace App\Repositories\Focus\project;

use App\Models\event\EventRelation;
use App\Models\project\Project;
use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\invoice\Invoice;
use App\Models\project\Budget;
use App\Models\project\BudgetItem;
use App\Models\project\BudgetSkillset;
use App\Models\project\ProjectQuote;
use App\Models\quote\Quote;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
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
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        $data_items = $input['data_items'];

        $tid = Project::max('tid');
        if ($data['tid'] <= $tid) $data['tid'] = $tid + 1;
        $data['main_quote_id'] = $data_items[0];
        $result = Project::create($data);

        // create project quote and update related foreign key
        foreach ($data_items as $val) {
            $obj = ['quote_id' => $val, 'project_id' => $result->id]; 
            $id = ProjectQuote::insertGetId($obj);
            Quote::find($val)->update(['project_quote_id' => $id]);
        }

        DB::commit();
        if ($result) return $result;

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
        $data_items = $input['data_items'];
        $data['main_quote_id'] = $data_items[0];
        $result = $project->update($data);

        // create or update project quotes
        ProjectQuote::where('project_id', $project->id)->whereNotIn('quote_id', $data_items)->delete();
        foreach($data_items as $val) {
            $item = ProjectQuote::firstOrNew(['project_id' => $project->id, 'quote_id' => $val]);
            Quote::find($val)->update(['project_quote_id' => $item->id]);
            $item->save();
        }

        DB::commit();
        if ($result) return true;

        throw new GeneralException(trans('exceptions.backend.projects.update_error'));
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
