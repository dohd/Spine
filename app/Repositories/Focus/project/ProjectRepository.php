<?php

namespace App\Repositories\Focus\project;

use App\Models\event\EventRelation;
use App\Models\project\Project;
use App\Exceptions\GeneralException;
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
        // if ($c) {
        //     $q->WhereHas('creator', function ($s) {
        //         return $s->where('rid', '=', auth()->user()->id);
        //     });
        //     $q->orWhereHas('users', function ($s) {
        //         return $s->where('rid', '=', auth()->user()->id);
        //     });
        // } else {
        //     $q->where('project_share', 4);
        //     $q->orWhere('project_share', 6);
        //     $q->whereHas('customer', function ($s) {
        //         return $s->where('rid', auth('crm')->user()->id);
        //     });
        // }
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
        if (isset($ref) && $project['project_number'] <= $ref->project_number) {
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
        return true;
    }    
}
