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
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('mass_select', function ($quote) {
                return  '<input type="checkbox"  class="row-select" value="'.$quote->id.'">';
            })
            ->addColumn('title', function($quote) {
                return $quote->notes;
            })
            ->addColumn('tid', function ($quote) {
                $tid = sprintf('%04d', $quote->tid);
                if ($quote->bank_id) $tid = 'PI-'.$tid;
                else $tid = 'QT-'.$tid;

                return '<a class="font-weight-bold" href="'.route('biller.quotes.show', [$quote->id]).'">' . $tid . $quote->revision .'</a>';
            })
            ->addColumn('customer', function ($quote) {
                $customer = isset($quote->customer) ? $quote->customer->company : '';
                $branch  = isset($quote->branch) ? $quote->branch->name : '';
                if ($customer && $branch) 
                    return $customer.' - '.$branch
                        .'&nbsp;<a class="font-weight-bold" href="'.route('biller.customers.show', [$quote->customer->id]).'"><i class="ft-eye"></i></a>';
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
            ->addColumn('tid', function($quote) {
                if ($quote->project_quote_id) 
                    return 'Prj-'.sprintf('%04d', $quote->project_quote->project->tid);
            })
            ->addColumn('lpo_number', function($quote) {
                if ($quote->lpo)
                    return $quote->lpo->lpo_no . '<br> Kes: ' . number_format($quote->lpo->amount, 2);

                return 'Null:';
            })
            ->addColumn('lead_tid', function($quote) {
                return 'Tkt-' . sprintf('%04d', $quote->lead->reference);
            })          
            ->make(true);
    }
}
