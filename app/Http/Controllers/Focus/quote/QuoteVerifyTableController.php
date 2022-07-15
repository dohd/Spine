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
class QuoteVerifyTableController extends Controller
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
        $core = $this->quote->getForVerifyDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($quote) {
                $tid = gen4tid('QT-', $quote->tid);
                if ($quote->bank_id) $tid = gen4tid('PI-', $quote->tid);

                return '<a class="font-weight-bold" href="' . route('biller.quotes.show', [$quote->id]) . '">' . $tid . $quote->revision . '</a>';
            })
            ->addColumn('lead_tid', function($quote) {
                if (isset($quote->lead->reference))
                return gen4tid('Tkt-', $quote->lead->reference);
            })
            ->addColumn('customer', function ($quote) {
                $client_name = $quote->customer ? $quote->customer->name : '';
                $branch_name = $quote->branch ? $quote->branch->name : '';
                if ($client_name && $branch_name) 
                    return $client_name . ' - ' . $branch_name . ' <a class="font-weight-bold" href="'.route('biller.customers.show', [$quote->customer->id]).'"><i class="ft-eye"></i></a>';

                return $quote->lead->client_name;
            })
            ->addColumn('created_at', function ($quote) {
                return dateFormat($quote->invoicedate);
            })
            ->addColumn('total', function ($quote) {
                return number_format($quote->total, 2);
            })
            ->addColumn('verified', function ($quote) {
                return $quote->verified . ':';
            })
            ->addColumn('lpo_number', function($quote) {
                if ($quote->lpo) return 'lpo - ' . $quote->lpo->lpo_no;
            })
            ->addColumn('project_tid', function($quote) {
                if (isset($quote->project_quote->project)) 
                return gen4tid('Prj-', $quote->project_quote->project->tid);
            })
            ->addColumn('actions', function ($quote) {
                $valid_token = token_validator('', 'q'.$quote->id .$quote->tid, true);
                if ($quote->verified == 'No') {
                    return '<a href="'. route('biller.quotes.verify', $quote) .'" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="Verify">
                        <i class="fa fa-check"></i></a>';
                }
                    
                return '<a href="'.route('biller.print_verified_quote', [$quote->id, 4, $valid_token, 1, 'verified=Yes']).'" class="btn btn-purple round" target="_blank" data-toggle="tooltip" data-placement="top" title="Print">
                    <i class="fa fa-print"></i></a> '
                    .'<a href="'. route('biller.quotes.verify', $quote) .'" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="Verify">
                    <i class="fa fa-check"></i></a>';
            })
            ->make(true);
    }
}
