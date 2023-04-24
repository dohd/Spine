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
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProjectRepository $repository ;
     */
    public function __construct(ProjectRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $query = $this->repository->getForDataTable();
        $ins = auth()->user()->ins;
        $prefixes = prefixesArray(['lead', 'project', 'proforma_invoice', 'quote'], $ins);

        return Datatables::of($query)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('customer', function($project) {
                $name = '';
                $customer = $project->customer_project;
                $branch = $project->branch;
                if ($customer && $branch) $name = "{$customer->company} - {$branch->name}";
                elseif ($customer) $name = $customer->company;
                
                return $name;
            })
            ->editColumn('tid', function($project) use ($prefixes) {
                return gen4tid("{$prefixes[1]}-", $project->tid);
            })
            ->filterColumn('tid', function($query, $tid) use($prefixes) {
                $arr = explode('-', $tid);
                if (strtolower($arr[0]) == strtolower($prefixes[1]) && isset($arr[1])) {
                    $query->where('tid', floatval($arr[1]));
                } elseif (floatval($tid)) {
                    $query->where('tid', floatval($tid));
                }
            })
            ->addColumn('quote_budget', function($project) {
                $links = '';
                foreach ($project->quotes as $quote) {
                    $tid = gen4tid($quote->bank_id ? 'PI-' : 'QT-', $quote->tid);
                    $status = $quote->budget? 'budgeted' : 'pending';
                    $links .= '<a href="'. route('biller.projects.create_project_budget', $quote). '" data-toggle="tooltip" title="Budget">
                        <b>'. $tid . '</b></a> :'. $status .'<br>';
                }
                
                return $links;
            })
            ->filterColumn('quote_budget', function($query, $budget) use($prefixes) {
                $arr = explode('-', $budget);
                if (strtolower($arr[0]) == strtolower($prefixes[2]) && isset($arr[1])) {
                    $query->whereHas('quotes', fn($q) => $q->where('tid', floatval($arr[1])));
                    
                    //fn($q) => $q->where('budget', floatval($arr[1]))
                } 
                elseif (strtolower($arr[0]) == strtolower($prefixes[3]) && isset($arr[1])) {
                    $query->whereHas('quotes', fn($q) => $q->where('tid', floatval($arr[1])));
                }
                elseif (floatval($budget)) {
                    //$query->whereHas('quotes', fn($q) => $q->where('budget', floatval($budget)));
                    $query->whereHas('quotes',  fn($q) => $q->where('tid', floatval($budget)));
                }
            })
            ->addColumn('lead_tid', function($project) use ($prefixes) {
                $tids = array();                
                foreach ($project->quotes as $quote) {
                    $tids[] = gen4tid("{$prefixes[0]}-", $quote->lead->reference);
                }
                return implode(', ', $tids);
            })
            ->filterColumn('lead_tid', function($query, $tid) use($prefixes) {
                $arr = explode('-', $tid);
                if (strtolower($arr[0]) == strtolower($prefixes[0]) && isset($arr[1])) {
                    $query->whereHas('quotes', fn($q) => $q->whereHas('lead', fn($q) => $q->where('reference', floatval($arr[1]))));
                    
                    //fn($q) => $q->where('tid', floatval($arr[1]))
                } elseif (floatval($tid)) {
                    //$query->whereHas('quotes', fn($q) => $q->where('tid', floatval($tid)));
                    $query->whereHas('quotes', fn($q) => $q->whereHas('lead', fn($q) => $q->where('reference', floatval($tid))));
                }
            })
            ->editColumn('start_date', function ($project) {
                return dateFormat($project->start_date);
            })
            ->orderColumn('start_date', '-start_date $1')
            ->editColumn('end_date', function ($project) {
                return dateFormat($project->end_date);
            })
            ->orderColumn('end_date', '-end_date $1')
            ->addColumn('status', function ($project) {
                return ucfirst($project->status);
            })
            ->addColumn('actions', function ($project) {
                return $project->action_buttons;
            })
            ->make(true);
    }
}