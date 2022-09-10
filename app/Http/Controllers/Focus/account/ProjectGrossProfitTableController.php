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

namespace App\Http\Controllers\Focus\account;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\account\AccountRepository;
use Yajra\DataTables\Facades\DataTables;

class ProjectGrossProfitTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var AccountRepository
     */
    protected $repository;

    // income & expense
    protected $income = 0;
    protected $expense = 0;

    /**
     * contructor to initialize repository object
     * @param AccountRepository $repository ;
     */
    public function __construct(AccountRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->repository->getForProjectGrossProfit();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('customer', function($project) {
                if ($project->customer_project)
                return $project->customer_project->company;
            })
            ->addColumn('tid', function($project) {
                return '<a href="'. route('biller.projects.show', $project) .'">'. gen4tid('Prj-', $project->tid) .'</a>';
            })
            ->addColumn('status', function($project) {
                return 'Active';
            })
            ->addColumn('quote_amount', function($project) {
                $quotes = array();
                foreach ($project->quotes as $quote) {
                    $tid = gen4tid($quote->bank_id? 'PI-': 'QT-', $quote->tid);
                    $quotes[] = '<a href="'. route('biller.quotes.show', $quote->id) .'">'. $tid .'</a>' . ' : ' . numberFormat($quote->subtotal) . '<br>';
                }
                return implode($quotes);
            })
            ->addColumn('income', function($project) {
                $income = 0;
                foreach ($project->quotes as $quote) {
                    $inv_product = $quote->invoice_product;
                    if ($inv_product) $income += $quote->subtotal;                        
                }
                $this->income = $income;

                return numberFormat($income);
            })
            ->addColumn('expense', function($project) {
                $expense = $project->purchase_items->sum('amount');
                $this->expense = $expense;

                return numberFormat($expense);
            })
            ->addColumn('gross_profit', function($project) {
                $profit = 0;
                if ($this->income > 0) 
                    $profit = $this->income  - $this->expense;
                
                return numberFormat($profit);
            })
            ->make(true);
    }
}