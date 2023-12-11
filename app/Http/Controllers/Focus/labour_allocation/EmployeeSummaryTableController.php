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
namespace App\Http\Controllers\Focus\labour_allocation;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\labour_allocation\LabourAllocationRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class EmployeeSummaryTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var LabourAllocationRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param LabourAllocationRepository $repository ;
     */
    public function __construct(LabourAllocationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->repository->getForEmployeeSummary();
        
        $total_hrs = $core->filter(fn($v) => $v->is_payable)->sum('hrs');
        $aggregate = compact('total_hrs');
        
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($item) {
                $labour = $item->labour;
                if (@$labour->project) return gen4tid('PRJ-',$labour->project->tid);
            })
            ->addColumn('employee_name', function ($item) {
               $employee = $item->employee;
               if($employee){
                    return $employee->first_name." ".$employee->last_name;
               }
            })
            ->addColumn('customer', function ($item) {
                $project = @$item->labour->project;
                if ($project) {
                    $customer = @$project->customer->company;
                    $branch = @$project->branch->name;
                    if ($branch) $customer .= " - {$branch}";
                    return $customer;
                }
            })
            ->addColumn('project_name', function ($item) {
                $labour = $item->labour;
                if($labour) return $labour->project ? $labour->project->name : '';
            })
             ->addColumn('main_quote_id', function($item) {
                $tids = [];
                 $labour = $item->labour;
                 if($labour){
                    $proj = $labour->project;
                    if($proj){
                        foreach ($proj->quotes as $quote) {
                            $tid = gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid);
                            $tids[] = '<a href="'. route('biller.quotes.show', $quote) .'">'. $tid .'</a>';
                        }
                    }
                }
                return implode(', ', $tids);
            })
            
            ->addColumn('job_card', function ($item) {
                return $item->labour ? $item->labour->job_card : '';
            })
            ->addColumn('date', function ($item) {
                return dateFormat($item->date);
            })
            ->addColumn('hrs', function ($item) {
                return $item->hrs;
            })
            ->addColumn('type', function ($item) {
                return strtoupper($item->type);
            })
            ->addColumn('aggregate', function () use($aggregate) {
                return $aggregate;
            })
            ->addColumn('actions', function ($item) {
                return $item->action_buttons;
            })
            ->make(true);
    }
}
