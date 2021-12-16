<?php

namespace App\Repositories\Focus\project;

use App\Models\event\Event;
use App\Models\event\EventRelation;
use App\Models\project\ProjectLog;
use App\Models\project\ProjectRelations;
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

        return $q->get([
            'id', 'name', 'status', 'project_number', 'priority', 'started_status', 'progress', 
            'end_date', 'created_at'
        ]);
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
        // $project['start_date'] = datetime_for_database($project['start_date'] . ' ' . $data['rest']['time_from']);
        // $project['end_date'] = datetime_for_database($project['end_date'] . ' ' . $data['rest']['time_to']);
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


        /**
         * Initial logic of the commented out browser fields
         */
        // project relations tags
        // $rel_tags = array(
        //     ['project_id' => $result['id'], 'related' => 3, 'rid' => auth()->user()->id],
        //     ['project_id' => $result['id'], 'related' => 8, 'rid' => $result['customer_id']]
        // );
        // $tags = $data['rest']['tags'];
        // foreach ($tags as $value) {
        //     $rel_tags[] = array('project_id' => $result['id'], 'related' => 1, 'rid' => $value);
        // }
        // $employees = $data['rest']['employees'];
        // foreach ($employees as $value) {
        //     $rel_tags[] = array('project_id' => $result['id'], 'related' => 2, 'rid' => $value);
        // }
        // ProjectRelations::insert($rel_tags);

        // // project log
        // $text = '[' . trans('general.create') . '] ' . $result['name'];
        // ProjectLog::create(['project_id' => $result['id'], 'value' => $text, 'user_id' => auth()->user()->id]);

        // // event
        // $event = Event::create([
        //     'title' => trans('projects.project') . ' - ' . $result['name'], 
        //     'description' => $result['short_desc'], 
        //     'start' => $result['start_date'], 
        //     'end' => $result['end_date'], 
        //     'color' => $data['rest']['color'], 
        //     'user_id' => auth()->user()->id, 
        //     'ins' => $result['ins']
        // ]);
        // EventRelation::create(['event_id' => $event->id, 'related' => 1, 'r_id' => $result['id']]);

        // $message = [
        //     'title' => trans('projects.project') . ' - ' . $result['name'], 
        //     'icon' => 'fa-bullhorn', 
        //     'background' => 'bg-success', 
        //     'data' => $result['short_desc']
        // ];
        // if ($employees) {
        //     $users = User::whereIn('id', $employees)->get();
        //     Notification::send($users, new Rose('', $message));
        // } else {
        //     $notification = new Rose(auth()->user(), $message);
        //     auth()->user()->notify($notification);
        // }
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

        /**
         * Initial logic of the commented out browser fields
         */
        // $employees = @$input['employees'];
        // $tags = @$input['tags'];
        // $calender = @$input['link_to_calender'];
        // $color = @$input['color'];
        // $customer = @$input['customer'];

        // unset($input['tags']);
        // unset($input['employees']);
        // unset($input['link_to_calender']);
        // unset($input['color']);
        //    unset($input['customer']);
        // $user_id = auth()->user()->id;

        // $input['start_date'] = datetime_for_database($input['start_date'] . ' ' . $input['time_from']);
        // $input['end_date'] = datetime_for_database($input['end_date'] . ' ' . $input['time_to']);
        // unset($input['time_from']);
        // unset($input['time_to']);
        // $input = array_map( 'strip_tags', $input);
        // $result = $project->update($input);


        // if ($result) {
        //     ProjectRelations::where(['related' => 1, 'project_id' => $project->id])->delete();
        //     ProjectRelations::where(['related' => 2, 'project_id' => $project->id])->delete();
        //     ProjectRelations::where(['related' => 3, 'project_id' => $project->id])->delete();
        //     $er = EventRelation::where(['related' => 1, 'r_id' => $project->id])->first();
        //     if ($er) {
        //         $er->event->delete();
        //         $er->delete();
        //     }
        //     $tag_group = array();
        //     if (is_array($tags)) {
        //         foreach ($tags as $row) {
        //             $tag_group[] = array('project_id' => $project->id, 'related' => 1, 'rid' => $row);
        //         }
        //     }

        //     if (is_array($employees)) {
        //         foreach ($employees as $row) {
        //             $tag_group[] = array('project_id' => $project->id, 'related' => 2, 'rid' => $row);
        //         }
        //     }
        //        if ($customer > 0) {
        //         $tag_group[] = array('project_id' => $project->id, 'related' => 8, 'rid' => $customer);
        //     }
        //     $tag_group[] = array('project_id' => $project->id, 'related' => 3, 'rid' => $user_id);
        //     ProjectRelations::insert($tag_group);
        //     ProjectLog::create(array('project_id' => $project->id, 'value' => '[' . trans('general.create') . '] ' . $project->name, 'user_id' => $user_id));
        //     if ($calender) {
        //         $event = Event::create(array('title' => trans('projects.project') . ' - ' . $input['name'], 'description' => $input['short_desc'], 'start' => $input['start_date'], 'end' => $input['end_date'], 'color' => $color, 'user_id' => $user_id, 'ins' => $project->ins));
        //         EventRelation::create(array('event_id' => $event->id, 'related' => 1, 'r_id' => $project->id));
        //     }
        //     return $result;
        // }
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
}
