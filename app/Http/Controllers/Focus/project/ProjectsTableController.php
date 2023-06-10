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
                if ($project->customer_project) {
                    $name = $project->customer_project->company;
                    if ($project->branch) $name .= " - {$project->branch->name}";
                }
                return $name;
            })
            ->editColumn('tid', function($project) use ($prefixes) {
                return '<a href="'.route('biller.projects.show', $project).'">'. gen4tid("{$prefixes[1]}-", $project->tid) .'</a>';;
            })
            ->filterColumn('tid', function($query, $tid) use($prefixes) {
                $arr = explode('-', $tid);
                if (count($arr) > 1 && strtolower($arr[0]) == strtolower($prefixes[1])) {
                    $query->where('tid', floatval($arr[1]));
                } elseif (floatval($tid)) {
                    $query->where('tid', floatval($tid));
                }
            })
            ->editColumn('start_date', function ($project) {
                return dateFormat($project->start_date);
            })
            ->orderColumn('start_date', '-start_date $1')
            ->editColumn('end_date', function ($project) {
                if (!$project->end_date) return '';
                return dateFormat($project->end_date);
            })
            ->orderColumn('end_date', '-end_date $1')
            ->addColumn('status', function ($project) {
                if ($project->misc)
                return ucfirst($project->misc->name);
            })
            ->editColumn('main_quote_id', function($project) {
                $tids = [];
                foreach ($project->quotes as $quote) {
                    $tid = gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid);
                    $tids[] = '<a href="'. route('biller.quotes.show', $quote) .'"><b>'. $tid .'</b></a>';
                }
                return implode(', ', $tids);
            })
            ->filterColumn('main_quote_id', function($query, $tid) use($prefixes) {
                $arr = explode('-', $tid);
                if (count($arr) > 1 && in_array($arr[0], ['QT','PI'])) {
                    $query->whereHas('quotes', fn($q) => $q->where('tid', floatval($arr[1])));
                } elseif (floatval($tid)) {
                    $query->whereHas('quotes', fn($q) => $q->where('tid', floatval($tid)));
                } 
            })
            ->addColumn('actions', function ($project) {
                return $project->action_buttons;
            })
            ->make(true);
    }
}