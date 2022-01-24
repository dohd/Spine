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
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\project\ProjectRepository;

/**
 * Class ProjectsTableController.
 */
class ProjectsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProjectRepository
     */
    protected $project;

    /**
     * contructor to initialize repository object
     * @param ProjectRepository $project ;
     */
    public function __construct(ProjectRepository $project)
    {
        $this->project = $project;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->project->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($project) {
                $tg = '';
                foreach ($project->tags as $row) {
                    $tg .= '<span class="badge" style="background-color:' . $row['color'] . '">' . $row['name'] . '</span> ';
                }
                return '<div class="todo-item media"><div class="media-body"><div class="todo-title">'.$project->name.'<div>' . $tg . '</div></div></div></div>';
            })
            ->addColumn('project_number', function($project) {
                return 'Prj-'.sprintf('%04d', $project->project_number);
            })
            ->addColumn('quote_tid', function($project) {
                $tids = array();                
                foreach ($project->quotes as $quote) {
                    $tid = sprintf('%04d', $quote->tid);
                    $tid = ($quote->bank_id) ? 'PI-'. $tid : $tid = 'QT-'. $tid;
                    $tids[] = '<a href="'.route('biller.projects.create_project_budget', $quote).'" data-toggle="tooltip" title="Budget"><b>'. $tid .'</b></a>';
                }

                return implode(', ', $tids);
            })
            ->addColumn('lead_tid', function($project) {
                $tids = array();                
                foreach ($project->quotes as $quote) {
                    $tids[] = 'Tkt-' . sprintf('%04d', $quote->lead->reference);
                }

                return implode(', ', $tids);
            })
            // ->addColumn('priority', function ($project) {
            //     return '<span class="">' . $project->priority . '</span> ';
            // })
            ->addColumn('start_status', function ($project) {
                $badge = 'badge-secondary';
                if ($project->start_status == 'running') $badge = 'badge-success';
                            
                return '<span class="badge '. $badge .'">' . $project->start_status . '</span>';
            })
            ->addColumn('progress', function ($project) {
                $task_back = task_status($project->status);
                return '<a href="#" class="view_project" data-toggle="modal" data-target="#ViewProjectModal" data-item="' 
                    . $project->id . '"><span class="badge" style="background-color:' . $task_back['color'] . '">' . $task_back['name'] . '</span></a> ' . numberFormat($project->progress) . ' %';
            })
            // ->addColumn('deadline', function ($project) {
            //     return dateTimeFormat($project->end_date);
            // })
            ->addColumn('created_at', function ($project) {
                return dateFormat($project->created_at);
            })
            ->addColumn('actions', function ($project) {
                $btn = '<a href="#" title="View" class="view_project success" data-toggle="modal" data-target="#ViewProjectModal" data-item="' . $project->id . '"><i class="ft-eye fa-lg"></i></a>';
                // $valid_project_creator = isset($project->creator) && $project->creator->id == auth()->user()->id;
                if (true) {
                    $btn .= '&nbsp;&nbsp;<a href="'.route("biller.projects.edit", [$project->id]).'" data-toggle="tooltip" data-placement="top" title="Edit"><i class="ft-edit fa-lg"></i></a>';
                    // $btn .= '&nbsp;&nbsp;<a class="danger" href="'.route("biller.projects.destroy", [$project->id]).'" data-method="delete" data-trans-button-cancel="' . trans('buttons.general.cancel') . '" data-trans-button-confirm="' . trans('buttons.general.crud.delete') . '" data-trans-title="' . trans('strings.backend.general.are_you_sure') . '" data-toggle="tooltip" data-placement="top" title="Delete"><i class="fa fa-trash fa-lg"></i></a>';
                }

                return $btn;
            })
            ->make(true);
    }
}
