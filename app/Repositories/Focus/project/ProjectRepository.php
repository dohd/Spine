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
        // dd($input);
        DB::beginTransaction();
        
        $data = $input['data'];
        $keys = array('quote_total', 'budget_total', 'labour_total');
        foreach ($data as $key => $val) {
            if (in_array($key, $keys, 1)) {
                $input['budget'][$key] = numberClean($val);
            }
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
            $item['budget_id'] = $result->id;
            $new_item = BudgetSkillset::firstOrNew(['quote_id' => $result->quote_id]);
            foreach ($item as $key => $val) {
                if ($key == 'charge') $item[$key] = numberClean($val);
                $new_item[$key] = $item[$key];
            }
            $new_item->save();
        }

        DB::commit();
        if ($result) return $result; 
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
            if (in_array($key, $keys, 1)) {
                $input['budget'][$key] = numberClean($val);
            }
        }   
        $result = $budget->update($data);

        $data_items = $input['data_items'];
        foreach($data_items as $item) {
            $item['price'] = numberClean($item['price']);
            $new_item = BudgetItem::firstOrNew([
                'id' => $item['item_id'],
                'budget_id' => $budget->id,
            ]);
            foreach($item as $key => $value) {
                $new_item[$key] = $value;
            }
            if ($new_item->id) unset($new_item['id']);
            unset($new_item->item_id);
            $new_item->save();
        }

        $data_skillset = $input['data_skillset'];
        foreach($data_skillset as $item) {
            $item['charge'] = numberClean($item['charge']);
            $new_item = BudgetSkillset::firstOrNew([
                'id' => $item['skillitem_id'],
                'budget_id' => $budget->id,
            ]);
            foreach($item as $key => $value) {
                $new_item[$key] = $value;
            }
            if (!$new_item->id) unset($new_item->id);
            unset($new_item->skillitem_id);
            $new_item->save();
        }
        
        DB::commit();
        if ($result) return $result;
    }             
}
