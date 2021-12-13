<?php

namespace App\Repositories\Focus\project;

use App\Models\Access\User\User;
use App\Models\event\Event;
use App\Models\event\EventRelation;
use App\Models\project\ProjectLog;
use App\Models\project\ProjectRelations;
use App\Notifications\Rose;
use App\Models\project\Project;
use App\Exceptions\GeneralException;
use App\Models\project\ProjectQuote;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

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
        if ($c) {
            $q->whereHas('creator', function ($s) {
            // $q->WhereHas('creator', function ($s) {
            //     return $s->where('rid', '=', auth()->user()->id);
            // });
            // $q->orWhereHas('users', function ($s) {
            //     return $s->where('rid', '=', auth()->user()->id);
            // });
            $q->where('project_share','=',4);
             $q->orWhere('project_share','=',6);
            $q->whereHas('customer', function ($s) {
                return $s->where('rid', '=', auth('crm')->user()->id);
            });
        }
        return $q->get(['id', 'name', 'status', 'project_number', 'priority', 'started_status', 'progress', 'end_date', 'created_at']);
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
        $main_quote = $data['project_quotes']['main_quote'];

        $project['ins'] = auth()->user()->ins;
        $project['main_quote_id'] = $main_quote;
        $project['start_date'] = datetime_for_database($project['start_date'] . ' ' . $data['rest']['time_from']);
        $project['end_date'] = datetime_for_database($project['end_date'] . ' ' . $data['rest']['time_to']);
        $result = Project::create($project);

        // project quotes
        $quotes[] = array('project_id' => $result['id'], 'quote_id' => $main_quote, 'main' => 1);
        if (isset($data['project_quotes']['other_quote'])) {
            $other_quote = $data['project_quotes']['other_quote'];
            foreach ($other_quote as $value) {
                $quotes[] = array('project_id' => $result['id'], 'quote_id' => $value, 'main' => 0);
            }            
        }
        ProjectQuote::insert($quotes);

        // project relations tags
        $rel_tags = array(
            ['project_id' => $result['id'], 'related' => 3, 'rid' => auth()->user()->id],
            ['project_id' => $result['id'], 'related' => 8, 'rid' => $result['customer_id']]
        );
        $tags = $data['rest']['tags'];
        foreach ($tags as $value) {
            $rel_tags[] = array('project_id' => $result['id'], 'related' => 1, 'rid' => $value);
        }
        $employees = $data['rest']['employees'];
        foreach ($employees as $value) {
            $rel_tags[] = array('project_id' => $result['id'], 'related' => 2, 'rid' => $value);
        }
        ProjectRelations::insert($rel_tags);

        // project log
        $text = '[' . trans('general.create') . '] ' . $result['name'];
        ProjectLog::create(['project_id' => $result['id'], 'value' => $text, 'user_id' => auth()->user()->id]);

        // event
        $event = Event::create([
            'title' => trans('projects.project') . ' - ' . $result['name'], 
            'description' => $result['short_desc'], 
            'start' => $result['start_date'], 
            'end' => $result['end_date'], 
            'color' => $data['rest']['color'], 
            'user_id' => auth()->user()->id, 
            'ins' => $result['ins']
        ]);
        EventRelation::create(['event_id' => $event->id, 'related' => 1, 'r_id' => $result['id']]);

        $message = [
            'title' => trans('projects.project') . ' - ' . $result['name'], 
            'icon' => 'fa-bullhorn', 
            'background' => 'bg-success', 
            'data' => $result['short_desc']
        ];
        if ($employees) {
            $users = User::whereIn('id', $employees)->get();
            Notification::send($users, new Rose('', $message));
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
    public function update(Project $project, array $input)
    {
        $employees = @$input['employees'];
        $tags = @$input['tags'];
        $calender = @$input['link_to_calender'];
        $color = @$input['color'];
        $customer = @$input['customer'];

        unset($input['tags']);
        unset($input['employees']);
        unset($input['link_to_calender']);
        unset($input['color']);
           unset($input['customer']);
        $user_id = auth()->user()->id;

        $input['start_date'] = datetime_for_database($input['start_date'] . ' ' . $input['time_from']);
        $input['end_date'] = datetime_for_database($input['end_date'] . ' ' . $input['time_to']);
        unset($input['time_from']);
        unset($input['time_to']);
        $input = array_map( 'strip_tags', $input);
        $result = $project->update($input);


        if ($result) {
            ProjectRelations::where(['related' => 1, 'project_id' => $project->id])->delete();
            ProjectRelations::where(['related' => 2, 'project_id' => $project->id])->delete();
            ProjectRelations::where(['related' => 3, 'project_id' => $project->id])->delete();
            $er = EventRelation::where(['related' => 1, 'r_id' => $project->id])->first();
            if ($er) {
                $er->event->delete();
                $er->delete();
            }
            $tag_group = array();
            if (is_array($tags)) {
                foreach ($tags as $row) {
                    $tag_group[] = array('project_id' => $project->id, 'related' => 1, 'rid' => $row);
                }
            }

            if (is_array($employees)) {
                foreach ($employees as $row) {
                    $tag_group[] = array('project_id' => $project->id, 'related' => 2, 'rid' => $row);
                }
            }
               if ($customer > 0) {
                $tag_group[] = array('project_id' => $project->id, 'related' => 8, 'rid' => $customer);
            }
            $tag_group[] = array('project_id' => $project->id, 'related' => 3, 'rid' => $user_id);
            ProjectRelations::insert($tag_group);
            ProjectLog::create(array('project_id' => $project->id, 'value' => '[' . trans('general.create') . '] ' . $project->name, 'user_id' => $user_id));
            if ($calender) {
                $event = Event::create(array('title' => trans('projects.project') . ' - ' . $input['name'], 'description' => $input['short_desc'], 'start' => $input['start_date'], 'end' => $input['end_date'], 'color' => $color, 'user_id' => $user_id, 'ins' => $project->ins));
                EventRelation::create(array('event_id' => $event->id, 'related' => 1, 'r_id' => $project->id));
            }
            return $result;
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
    public function delete(Project $project)
    {
        if ($project->creator->id == auth()->user()->id) {
            if ($project->delete()) {
                $er = EventRelation::where(['related' => 1, 'r_id' => $project->id])->first();
                if ($er) {
                    $er->event->delete();
                    $er->delete();
                }
                return true;
            }
        }

        throw new GeneralException(trans('exceptions.backend.projects.delete_error'));
    }
}
