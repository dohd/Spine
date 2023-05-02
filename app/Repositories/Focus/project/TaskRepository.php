<?php

namespace App\Repositories\Focus\project;

use App\Http\Utilities\Notification;
use App\Models\Access\User\User;
use App\Models\event\Event;
use App\Models\event\EventRelation;
use App\Models\project\ProjectLog;
use App\Models\project\ProjectRelations;
use App\Models\project\TaskRelations;
use App\Notifications\Rose;
use DB;
use Carbon\Carbon;
use App\Models\project\Task;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TaskRepository.
 */
class TaskRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Task::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable($uid = 0)
    {
        $q = $this->query();
        if (request('p') AND project_access(request('p'))) {
            $q->whereHas('project', function ($s) {

                return $s->where('project_id', '=', request('p', 0));
            });
        }
             if (request('p_c') AND project_client(request('p_c'))) {
                 $q->withoutGlobalScopes();
            $q->whereHas('project', function ($s) {

                return $s->withoutGlobalScopes()->where('project_id', '=', request('p_c', 0));
            });
        }
        if ($uid) {
            $q->whereHas('users', function ($s) use ($uid) {
                return $s->where('users.id', '=', $uid);
            });
        }
        return $q->get(['id','name','status','start','duedate','ins AS cus']);
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @return Task $task
     * @throws GeneralException
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $employees = @$input['employees'];
        $tags = @$input['tags'];
        $projects = @$input['projects'];
        $calender = @$input['link_to_calender'];
        $color = @$input['color'];

        $input = array_diff_key($input, array_flip(['tags', 'employees', 'projects', 'link_to_calender', 'color']));
        $input['start'] = datetime_for_database($input['start'] . ' ' . $input['time_from']);
        $input['duedate'] = datetime_for_database($input['duedate'] . ' ' . $input['time_to']);
        if ($employees) $input['creator_id'] = current($employees);
        unset($input['time_from'], $input['time_to']);
        
        $result = Task::create($input);

        $tag_group = [];
        if (is_array($tags)) {
            foreach ($tags as $row) {
                $tag_group[] = ['todolist_id' => $result->id, 'related' => 1, 'rid' => $row];
            }
        }

        $employee_group = [];
        if (is_array($employees)) {
            foreach ($employees as $row) {
                $tag_group[] = array('todolist_id' => $result->id, 'related' => 2, 'rid' => $row);
                $employee_group[] = $row;
            }
        }

        $project_group = [];
        if (is_array($projects)) {
            foreach ($projects as $row) {
                if (project_access($row)) 
                    $project_group[] = ['project_id' => $row, 'related' => 4, 'rid' => $result->id];
            }
            ProjectRelations::insert($project_group);
        }
        TaskRelations::insert($tag_group);

        if ($calender) {
            $data = [
                'title' => trans('tasks.task') . ' - ' . $input['name'], 
                'description' => $input['short_desc'], 
                'start' => $input['start'], 
                'end' => $input['duedate'], 
                'color' => $color,
            ];
            $event = Event::create($data);
            EventRelation::create(['event_id' => $event->id, 'related' => 2, 'r_id' => $result->id]);
        }

        $message = [
            'title' => trans('tasks.task') . ' - ' . $input['name'], 
            'icon' => 'fa-bullhorn', 
            'background' => 'bg-success', 
            'data' => $input['short_desc']
        ];
        if ($employee_group) {
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

        throw new GeneralException(trans('exceptions.backend.tasks.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Task $task
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Task $task, array $input)
    {

        $employees = @$input['employees'];
        $tags = @$input['tags'];
        $projects = @$input['projects'];
        $calender = @$input['link_to_calender'];
        $color = @$input['color'];
        unset($input['tags']);
        unset($input['employees']);
        unset($input['projects']);
        unset($input['link_to_calender']);
        unset($input['color']);
        $user_id = auth()->user()->id;
        $input['start'] = datetime_for_database($input['start'] . ' ' . $input['time_from']);
        $input['duedate'] = datetime_for_database($input['duedate'] . ' ' . $input['time_to']);
        unset($input['time_from']);
        unset($input['time_to']);
        $input = array_map( 'strip_tags', $input);
        $result = $task->update($input);
        if ($result) {
            ProjectRelations::where(['related' => 4, 'rid' => $task->id])->delete();
            TaskRelations::where(['related' => 1, 'todolist_id' => $task->id])->delete();
            TaskRelations::where(['related' => 2, 'todolist_id' => $task->id])->delete();
            $er = EventRelation::where(['related' => 2, 'r_id' => $task->id])->first();
            if ($er) {
                $er->event->delete();
                $er->delete();
            }


            $tag_group = array();
            if (is_array($tags)) {
                foreach ($tags as $row) {
                    $tag_group[] = array('todolist_id' => $task->id, 'related' => 1, 'rid' => $row);
                }
            }

            if (is_array($employees)) {
                foreach ($employees as $row) {
                    $tag_group[] = array('todolist_id' => $task->id, 'related' => 2, 'rid' => $row);
                }
            }
            if (is_array($projects)) {
                $p_group = array();
                foreach ($projects as $row) {
                    if (project_access($row)) $p_group[] = array('project_id' => $row, 'related' => 4, 'rid' => $task->id);
                }
                ProjectRelations::insert($p_group);

            }
            TaskRelations::insert($tag_group);
            if ($calender) {
                $event = Event::create(array('title' => trans('tasks.task') . ' - ' . $input['name'], 'description' => $input['short_desc'], 'start' => $input['start'], 'end' => $input['duedate'], 'color' => $color, 'user_id' => $user_id, 'ins' => $task->ins));
                EventRelation::create(array('event_id' => $event->id, 'related' => 2, 'r_id' => $task->id));
            }
            return $result;
        }


        throw new GeneralException(trans('exceptions.backend.tasks.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Task $task
     * @return bool
     * @throws GeneralException
     */
    public function delete(Task $task)
    {
        if ($task) {
            ProjectRelations::where('related', '=', 4)->where('rid', '=', $task->id)->delete();
            $er = EventRelation::where(['related' => 2, 'r_id' => $task->id])->first();
            if ($er) {
                $er->event->delete();
                $er->delete();
            }
            $task->delete();
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.tasks.delete_error'));
    }
}
