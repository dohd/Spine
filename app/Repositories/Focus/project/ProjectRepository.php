<?php

namespace App\Repositories\Focus\project;

use App\Models\project\Project;
use App\Exceptions\GeneralException;
use App\Models\Access\User\User;
use App\Models\event\Event;
use App\Models\event\EventRelation;
use App\Models\project\Budget;
use App\Models\project\BudgetItem;
use App\Models\project\BudgetSkillset;
use App\Models\project\ProjectLog;
use App\Models\project\ProjectRelations;
use App\Notifications\Rose;
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
     * @return Project $project
     * @throws GeneralException
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();
        
        $employees = @$input['employees'];
        $tags = @$input['tags'];
        $calender = @$input['link_to_calender'];
        $color = @$input['color'];
        $customer = @$input['customer'];

        $input = array_diff_key($input, array_flip(['tags', 'employees', 'customer', 'link_to_calender', 'color']));
        $input['worth'] = numberClean($input['worth']);
        $input['start_date'] = datetime_for_database("{$input['start_date']} {$input['time_from']}");
        $input['end_date'] = datetime_for_database("{$input['end_date']} {$input['time_to']}");
        unset($input['time_from'], $input['time_to']);

        $tid = Project::max('tid');
        if (@$input['tid'] <= $tid) $input['tid'] = $tid+1;
        $result = Project::create($input);

        $tag_group = [];
        if (is_array($tags)) {
            foreach ($tags as $row) {
                $tag_group[] = ['project_id' => $result->id, 'related' => 1, 'rid' => $row];
            }
        }

        $employee_group = [];
        if (is_array($employees)) {
            foreach ($employees as $row) {
                $tag_group[] = ['project_id' => $result->id, 'related' => 2, 'rid' => $row];
                $employee_group[] = $row;
            }
        }

        if ($customer > 0) $tag_group[] = ['project_id' => $result->id, 'related' => 8, 'rid' => $customer];
        $tag_group[] = ['project_id' => $result->id, 'related' => 3, 'rid' => $result->user_id];
        ProjectRelations::insert($tag_group);

        $data = ['project_id' => $result->id, 'value' => '[' . trans('general.create') . '] ' . $result->name, 'user_id' => $result->user_id];
        ProjectLog::create($data);
        if ($calender) {
            $data = [
                'title' => trans('projects.project') . ' - ' . $input['name'], 
                'description' => $input['short_desc'], 
                'start' => $input['start_date'], 
                'end' => $input['end_date'], 
                'color' => $color, 
                'user_id' => $result->user_id, 
                'ins' => $result['ins']
            ];
            $event = Event::create($data);
            EventRelation::create(['event_id' => $event->id, 'related' => 1, 'r_id' => $result->id]);
        }
        $message = array('title' => trans('projects.project') . ' - ' . $result->name, 'icon' => 'fa-bullhorn', 'background' => 'bg-success', 'data' => $input['short_desc']);

        if (is_array(@$employee_group)) {
            $users = User::whereIn('id', $employee_group)->get();
            \Illuminate\Support\Facades\Notification::send($users, new Rose('', $message));
        } else {
            $notification = new Rose(auth()->user(), $message);
            auth()->user()->notify($notification);
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

        $employees = @$input['employees'];
        $tags = @$input['tags'];
        $calender = @$input['link_to_calender'];
        $color = @$input['color'];
        $customer = @$input['customer'];

        $input = array_diff_key($input, array_flip(['tags', 'employees', 'customer', 'link_to_calender', 'color']));
        $input['worth'] = numberClean($input['worth']);
        $input['start_date'] = datetime_for_database("{$input['start_date']} {$input['time_from']}");
        $input['end_date'] = datetime_for_database("{$input['end_date']} {$input['time_to']}");
        unset($input['time_from'], $input['time_to']);

        $result = $project->update($input);

        ProjectRelations::whereIn('related', range(1,3))->where('project_id', $project->id)->delete();
        $event_rel = EventRelation::where(['related' => 1, 'r_id' => $project->id])->first();
        if ($event_rel) {
            $event_rel->event->delete();
            $event_rel->delete();
        }

        $tag_group = [];
        if (is_array($tags)) {
            foreach ($tags as $row) {
                $tag_group[] = ['project_id' => $project->id, 'related' => 1, 'rid' => $row];
            }
        }
        if (is_array($employees)) {
            foreach ($employees as $row) {
                $tag_group[] = ['project_id' => $project->id, 'related' => 2, 'rid' => $row];
            }
        }
        if ($customer > 0) $tag_group[] = ['project_id' => $project->id, 'related' => 8, 'rid' => $customer];
            
        $tag_group[] = ['project_id' => $project->id, 'related' => 3, 'rid' => $project->user_id];
        ProjectRelations::insert($tag_group);

        $data = ['project_id' => $project->id, 'value' => '[' . trans('general.edit') . '] ' . $project->name, 'user_id' => $project->user_id];
        ProjectLog::create($data);
        if ($calender) {
            $data = ['title' => trans('projects.project') . ' - ' . $input['name'], 'description' => $input['short_desc'], 'start' => $input['start_date'], 'end' => $input['end_date'], 'color' => $color, 'user_id' => $project->user_id, 'ins' => $project->ins];
            $event = Event::create($data);
            EventRelation::create(['event_id' => $event->id, 'related' => 1, 'r_id' => $project->id]);
        }
        
        if ($result) {
            DB::commit();
            return $result;
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
        DB::beginTransaction();

        $data = ['project_id' => $project->id, 'value' => '[' . trans('general.delete') . '] ' . $project->name, 'user_id' => $project->user_id];
        ProjectLog::create($data);

        ProjectRelations::where('project_id', $project->id)->delete();
        $event_rel = EventRelation::where(['related' => 1, 'r_id' => $project->id])->first();
        if ($event_rel) {
            $event_rel->event->delete();
            $event_rel->delete();
        }

        if ($project->purchase_items->count())
            throw ValidationException::withMessages(['Not allowed! Project has expense']);
        foreach ($project->quotes as $quote) {
            if ($quote->projectstock->count())
                throw ValidationException::withMessages(['Not allowed! Project has issued stock items']);
        }    
        if ($project->budget) $project->budget->delete();

        if ($project->delete()) {
            DB::commit();
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.projects.delete_error'));
    }
}
