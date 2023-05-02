<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

namespace App\Http\Controllers\Focus\project;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\project\TaskRepository;
use App\Http\Requests\Focus\project\ManageTaskRequest;

/**
 * Class TasksTableController.
 */
class TasksTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var TaskRepository
     */
    protected $task;

    /**
     * contructor to initialize repository object
     * @param TaskRepository $task ;
     */
    public function __construct(TaskRepository $task)
    {
        $this->task = $task;
    }

    /**
     * This method return the data of the model
     * @param ManageTaskRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = collect();
        if (access()->allow('manage-task') || project_access(request('project_id')))
            $core = $this->task->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('start', function ($task) {
                return '<span  class="font-size-small">'. dateTimeFormat($task->start) .'</span>';
            })
            ->addColumn('duedate', function ($task) {
                return '<span  class="font-size-small">'. dateTimeFormat($task->duedate) .'</span>';
            })
            ->addColumn('status', function ($task) {
                $task_back = task_status($task->status);
                return '<span class="badge" style="background-color:'. $task_back['color'] .'">'. $task_back['name'] . '</span> ';
            })
            ->addColumn('tags', function ($task) {
                $tag = '';
                foreach ($task->tags as $row) {
                    $tag .= '<span class="badge" style="background-color:' . $row['color'] . '">' . $row['name'] . '</span> ';
                }

                return '<div class="todo-item media"><div class="media-body"><div class="todo-title"><a href="#" title="View" class="view_task success" data-toggle="modal" data-target="#ViewTaskModal" data-item="' . $task->id . '">'
                    . $task->name . '</a><div class="float-right">' . $tag . '</div></div><span class="todo-desc">' . $task->short_desc . '</span></div> </div>';
            })
            ->addColumn('actions', function ($task) {
                $btn = '<a href="#" title="View" class="view_task success" data-toggle="modal" data-target="#ViewTaskModal" data-item="'. $task->id .'">
                    <i  class="ft-eye"></i></a> ';

                if (access()->allow('edit-task')) 
                    $btn .= '&nbsp;&nbsp;<a href="'. route("biller.tasks.edit", [$task->id]) . '" data-toggle="tooltip" data-placement="top" title="Edit"><i  class="ft-edit"></i></a>';
                
                if (access()->allow('delete-task')) 
                    $btn .= '&nbsp;&nbsp;<a class="danger" href="' . route("biller.tasks.destroy", [$task->id]) . '" table-method="delete" data-trans-button-cancel="' 
                        .trans('buttons.general.cancel') . '" data-trans-button-confirm="' . trans('buttons.general.crud.delete') . '" data-trans-title="'
                        .trans('strings.backend.general.are_you_sure') .'"title="Delete"> <i  class="fa fa-trash"></i> </a>';
                        
                return $btn;
            })
            ->make(true);
        
    }
}    
