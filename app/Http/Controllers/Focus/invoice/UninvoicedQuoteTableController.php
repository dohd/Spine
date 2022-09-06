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
namespace App\Http\Controllers\Focus\invoice;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\quote\QuoteRepository;

/**
 * Class QuotesTableController.
 */
class UninvoicedQuoteTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var QuoteRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param QuoteRepository $repository ;
     */
    public function __construct(QuoteRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->repository->getForVerifyNotInvoicedDataTable();
        
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('mass_select', function ($quote) {
                return  '<input type="checkbox"  class="row-select" value="'. $quote->id .'">';
            })
            ->addColumn('title', function($quote) {
                return $quote->notes;
            })
            ->addColumn('tid', function ($quote) {
                $tid = gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid);
                return '<a class="font-weight-bold" href="'. route('biller.quotes.show', $quote) .'">' . $tid . $quote->revision .'</a>';
            })
            ->addColumn('customer', function ($quote) {
                $customer = isset($quote->customer) ? $quote->customer->company : '';
                $branch  = isset($quote->branch) ? $quote->branch->name : '';
                if ($customer && $branch) 
                    return $customer.' - '.$branch
                        .'&nbsp;<a class="font-weight-bold" href="'.route('biller.customers.show', $quote->customer).'"><i class="ft-eye"></i></a>';
            })
            ->addColumn('created_at', function ($quote) {
                return dateFormat($quote->invoicedate);
            })
            ->addColumn('total', function ($quote) {
                return numberFormat($quote->total);
            })
            ->addColumn('verified_total', function ($quote) {
                return numberFormat($quote->verified_total);
            })
            ->addColumn('diff_total', function ($quote) {
                return numberFormat($quote->total - $quote->verified_total);
            })
            ->addColumn('project_tid', function($quote) {
                if ($quote->project_quote_id) 
                return gen4tid('Prj-', $quote->project_quote->project->tid);
            })
            ->addColumn('lpo_number', function($quote) {
                if (!$quote->lpo)  return 'Null:';

                return $quote->lpo->lpo_no . '<br> Kes: ' . numberFormat($quote->lpo->amount);
            })
            ->make(true);
    }
}
