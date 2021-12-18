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
namespace App\Http\Controllers\Focus\quote;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\quote\QuoteRepository;

/**
 * Class QuotesTableController.
 */
class QuoteInvoiceTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var QuoteRepository
     */
    protected $quote;

    /**
     * contructor to initialize repository object
     * @param QuoteRepository $quote ;
     */
    public function __construct(QuoteRepository $quote)
    {
        $this->quote = $quote;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->quote->getForVerifyNotInvoicedDataTable();

        return Datatables::of($core)
            ->addIndexColumn()
            ->addColumn('mass_select', function ($quote) {
                return  '<input type="checkbox" class="row-select" value="' . $quote->id .'">' ;
            })
            ->addColumn('notes', function($quote) {
                return $quote->notes;
            })
            ->addColumn('tid', function ($quote) {
                $tid = sprintf('%04d', $quote->tid);
                if ($quote->bank_id) $tid = 'PI-'.$tid;
                else $tid = 'QT-'.$tid;

                return '<a class="font-weight-bold" href="' . route('biller.quotes.show', [$quote->id]) . '">' . $tid . '</a>';
            })
            ->addColumn('customer', function ($quote) {
                if (isset($quote->customer) && isset($quote->lead->branch)) {
                    return $quote->customer->company.' - '.$quote->lead->branch->name.' '
                        .'<a class="font-weight-bold" href="' . route('biller.customers.show', [$quote->customer->id]) . '"><i class="ft-eye"></i></a>';
                }
                return $quote->lead->client_name;
            })
            ->addColumn('created_at', function ($quote) {
                return dateFormat($quote->invoicedate);
            })
            ->addColumn('total', function ($quote) {
                return number_format($quote->total, 2);
            })
            ->addColumn('verified_total', function ($quote) {
                return number_format($quote->verified_total, 2);
            })
            ->addColumn('project_number', function($quote) {
                $tid = '';
                if (isset($quote->project_quote->project)) {
                    $tid = 'P-'.sprintf('%04d', $quote->project_quote->project->project_number);
                }
                return $tid;
            })
            ->addColumn('verified', function ($quote) {
                return $quote->verified;
            })
            ->addColumn('lpo_number', function($quote) {
                return $quote->lpo_number;
            })
         
            ->rawColumns(['notes', 'tid', 'customer', 'actions', 'status', 'total','mass_select'])
            ->make(true);
    }
}
