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
            ->addColumn('customer', function($project) {
                if ($project->customer_project)
                    return $project->customer_project->company;
            })
            ->addColumn('tid', function($project) {
                return gen4tid('Prj-', $project->tid);
            })
            ->addColumn('quote_tid', function($project) {
                $tids = array();                
                foreach ($project->quotes as $quote) {
                    $tid = $quote->bank_id ? gen4tid('PI-', $quote->tid) : gen4tid('QT-', $quote->tid);
                    $tids[] = '<a href="'.route('biller.projects.create_project_budget', $quote).'" data-toggle="tooltip" title="Budget"><b>'. $tid .'</b></a>';
                }
                return implode(', ', $tids);
            })
            ->addColumn('lead_tid', function($project) {
                $tids = array();                
                foreach ($project->quotes as $quote) {
                    $tids[] = gen4tid('Tkt-', $quote->lead->reference);
                }
                return implode(', ', $tids);
            })
            ->addColumn('start_status', function ($project) {
                if ($project->quote && $project->quote->budget)
                    return 'budgeted:';
                return 'pending:';
            })
            ->addColumn('start_date', function ($project) {
                return dateFormat($project->start_date);
            })
            ->addColumn('actions', function ($project) {
                return $project->action_buttons;
            })
            ->make(true);
    }
}