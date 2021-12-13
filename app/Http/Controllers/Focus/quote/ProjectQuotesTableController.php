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
class ProjectQuotesTableController extends Controller
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
            ->addIndexColumn()
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
                    return $quote->customer->name.' - '.$quote->lead->branch->name.' '
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
            ->addColumn('project_number', function($quote) {
                return 'P-0001';
            })
            ->addColumn('verified', function ($quote) {
                return $quote->verified;
            })
            ->addColumn('lpo_number', function($quote) {
                return $quote->lpo_number;
            })
            ->addColumn('actions', function ($quote) {
                $valid_token = token_validator('', 'q'.$quote->id .$quote->tid, true);

                return '<a href="'.route('biller.print_quote', [$quote->id, 4, $valid_token, 1, 'verified=Yes']).'" class="btn btn-purple round" target="_blank" data-toggle="tooltip" data-placement="top" title="Print"><i class="fa fa-print"></i></a> '
                    .'<a href="'. route('biller.quotes.verify', $quote) .'" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="Verify"><i class="fa fa-check"></i></a>';
            })
            ->rawColumns(['notes', 'tid', 'customer', 'actions', 'status', 'total'])
            ->make(true);
    }
}
