<?php

namespace App\Repositories\Focus\project;

use App\Models\project\Project;
use App\Exceptions\GeneralException;
use App\Models\Access\User\User;
use App\Models\event\Event;
use App\Models\event\EventRelation;
use App\Models\project\ProjectLog;
use App\Models\project\ProjectQuote;
use App\Models\project\ProjectRelations;
use App\Notifications\Rose;
use App\Repositories\BaseRepository;
use Illuminate\Support\Arr;
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
        $q = $this->query();

        // customer user filter
        $customer_id = auth()->user()->customer_id;
        $q->when($customer_id, fn($q) => $q->where('customer_id', $customer_id));
        
        // customer filter
        if(request('customer_id')) {
            $q->where('customer_id', request('customer_id'));
            if (request('branch_id')) $q->where('branch_id', request('branch_id'));
        } 
        else $q->limit(500);
        
        return $q;
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @return Project $project
     * @throws GeneralException
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();
        
        // project
        $project_input = Arr::only($input, ['customer_id', 'branch_id', 'name', 'status', 'priority', 'short_desc', 'note', 'start_date', 'end_date', 'time_from', 'time_to', 'worth', 'project_share', 'tid']);
        $project_input = array_replace($project_input, [
            'worth' => numberClean($project_input['worth']),
            'start_date' => datetime_for_database("{$project_input['start_date']} {$project_input['time_from']}"),
            'end_date' => datetime_for_database("{$project_input['end_date']} {$project_input['time_to']}"),
        ]);
        unset($project_input['time_from'], $project_input['time_to']);
        $tid = Project::max('tid');
        if (@$project_input['tid'] <= $tid) $project_input['tid'] = $tid+1;
        $result = Project::create($project_input);
        
        // log
        $data = ['project_id' => $result->id, 'value' => '[' . trans('general.create') . '] ' . $result->name, 'user_id' => $result->user_id];
        ProjectLog::create($data);

        // tags
        $tags = @$input['tags'] ?: [];
        $tag_group = array_map(fn($v) => ['misc_id' => $v, 'project_id' => $result->id], $tags);
        ProjectRelations::insert($tag_group);

        // attach quotes
        $quotes = @$input['quotes'] ?: [];
        // if (!$quotes) throw ValidationException::withMessages(['Quote or Proforma Invoice required']);
        if ($quotes) {
            $quote_group = array_map(fn($v) => ['quote_id' => $v, 'project_id' => $result->id], $quotes);
            $result->update(['main_quote_id' => $quotes[0]]);
            ProjectQuote::insert($quote_group);
            foreach ($result->quotes as $quote) {
                if ($quote->project_quote) 
                    $quote->update(['project_quote_id' => $quote->project_quote->id]);
            }
        }
        
        // project users
        $employees = @$input['employees'] ?: [];
        $employees_group = array_map(fn($v) => ['user_id' => $v, 'project_id' => $result->id], $employees);
        ProjectRelations::insert($employees_group);

        // calendar link
        if (@$input['link_to_calender']) {
            $data = [
                'title' => trans('projects.project') . ' - ' . $input['name'], 
                'description' => $input['short_desc'], 
                'start' => $input['start_date'], 
                'end' => $input['end_date'], 
                'color' => @$input['color'], 
                'user_id' => $result->user_id, 
                'ins' => $result['ins']
            ];
            $event = Event::create($data);
            EventRelation::create(['event_id' => $event->id, 'related' => 1, 'r_id' => $result->id]);
        }
        
        if ($result) {
            DB::commit();
            // employee notifiation
            $message = ['title' => trans('projects.project') . ' - ' . $result->name, 'icon' => 'fa-bullhorn', 'background' => 'bg-success', 'data' => $input['short_desc']];
            if ($employees) {
                $users = User::whereIn('id', $employees)->get();
                \Illuminate\Support\Facades\Notification::send($users, new Rose('', $message));
            } else {
                $notification = new Rose(auth()->user(), $message);
                auth()->user()->notify($notification);
            }

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
        
        // project
        $project_input = array_diff_key($input, array_flip(['tags', 'employees', 'link_to_calender', 'color']));
        $project_input = array_replace($project_input, [
            'worth' => numberClean($project_input['worth']),
            'start_date' => datetime_for_database("{$project_input['start_date']} {$project_input['time_from']}"),
            'end_date' => datetime_for_database("{$project_input['end_date']} {$project_input['time_to']}"),
        ]);
        unset($project_input['time_from'], $project_input['time_to']);
        $project->update($project_input);

        // log
        $data = ['project_id' => $project->id, 'value' => '[' . trans('general.update') . '] ' . $project->name, 'user_id' => auth()->user()->id];
        ProjectLog::create($data);

        // tags
        $tags = @$input['tags'] ?: [];
        ProjectRelations::whereNotIn('misc_id', $tags)->where('project_id', $project->id)->whereNotNull('misc_id')->delete();
        foreach ($tags as $tag) {
            ProjectRelations::updateOrCreate(
                ['misc_id' => $tag, 'project_id' => $project->id],
                ['misc_id' => $tag, 'project_id' => $project->id]
            );
        }

        // project users
        $employees = @$input['employees'] ?: [];
        ProjectRelations::whereNotIn('user_id', $employees)->where('project_id', $project->id)
            ->whereNotNull('user_id')->whereNull('task_id')->delete();
        foreach ($employees as $id) {
            ProjectRelations::updateOrCreate(
                ['user_id' => $id, 'project_id' => $project->id],
                ['user_id' => $id, 'project_id' => $project->id]
            );
        }

        // calendar link
        if (@$input['link_to_calender']) {
            $data = [
                'title' => trans('projects.project') . ' - ' . $input['name'], 
                'description' => $input['short_desc'], 
                'start' => $input['start_date'], 
                'end' => $input['end_date'], 
                'color' => @$input['color'], 
                'user_id' => auth()->user()->id, 
                'ins' => $project->ins,
            ];
            $event_relation = EventRelation::where('r_id', $project->id)->first();
            if (@$event_relation->event) $event_relation->event->update($data);
        }

        if ($project) {
            DB::commit();
            return $project;
        }
    }

    /**
     * For delete respective model from storage
     * 
     *  @param \App\Models\project\Project $project 
     */
    public function delete($project)
    {  
        DB::beginTransaction();

        $is_expensed = false;
        if ($project->purchase_items->count()) $is_expensed = true;
        foreach ($project->quotes as $quote) {
            if ($quote->projectstock->count()) $is_expensed = true;
        }    
        if ($is_expensed) throw ValidationException::withMessages(['Not allowed! Project has been expensed']);

        // log
        $data = ['project_id' => $project->id, 'value' => '[' . trans('general.delete') . '] ' . $project->name, 'user_id' => auth()->user()->id];
        ProjectLog::create($data);

        // calendar link
        $event_rel = EventRelation::where(['related' => 1, 'r_id' => $project->id])->first();
        if ($event_rel) {
            $event_rel->event->delete();
            $event_rel->delete();
        }

        // users and tags
        ProjectRelations::where('project_id', $project->id)->delete();
        
        if ($project->delete()) {
            DB::commit();
            return true;
        }
    }
}
