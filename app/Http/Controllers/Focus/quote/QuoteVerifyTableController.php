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
        $query = $this->quote->getForVerifyDataTable();
        $prefixes = prefixesArray(['quote', 'proforma_invoice', 'project'], auth()->user()->ins);

        return Datatables::of($query)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($quote) use($prefixes) {
                $tid = gen4tid($quote->bank_id? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid);
                return '<a class="font-weight-bold" href="'. route('biller.quotes.show',$quote) .'">'. $tid . $quote->revision .'</a>';
            })
            ->addColumn('customer', function ($quote) {
                $customer = $quote->lead? $quote->lead->client_name : '';
                if ($quote->customer) {
                    $customer = "{$quote->customer->company}";
                    if ($quote->branch) $customer .= " - {$quote->branch->name}";
                }
                
                return $customer;
            })
            ->addColumn('total', function ($quote) {
                if ($quote->currency) return amountFormat($quote->total, $quote->currency->id);
                return numberFormat($quote->total);
            })
            ->addColumn('verified_total', function ($quote) {
                if ($quote->currency) return amountFormat($quote->verified_total, $quote->currency->id);
                return numberFormat($quote->verified_total);
            })
            ->addColumn('lpo_number', function($quote) {
                if ($quote->lpo) return 'lpo - ' . $quote->lpo->lpo_no;
            })
            ->addColumn('project_tid', function($quote) use($prefixes) {
                if ($quote->project) 
                return gen4tid("{$prefixes[2]}-", $quote->project->tid);
            })
            ->addColumn('date', function($quote) {
                return dateFormat($quote->date);
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
