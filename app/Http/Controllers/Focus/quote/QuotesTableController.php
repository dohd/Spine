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
use App\Http\Requests\Focus\quote\ManageQuoteRequest;

/**
 * Class QuotesTableController.
 */
class QuotesTableController extends Controller
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
     * @param ManageQuoteRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageQuoteRequest $request)
    {
        $core = $this->quote->getForDataTable();
        return Datatables::of($core)
            ->addIndexColumn()
            ->addColumn('notes', function($quote) {
                return $quote->notes;
            })
            ->addColumn('tid', function ($quote) {
                return '<a class="font-weight-bold" href="' . route('biller.quotes.show', [$quote->id]) . '">' . $quote->tid . '</a>';
            })
            ->addColumn('customer', function ($quote) {
                if (isset($quote->customer) && isset($quote->lead->branch)) {
                    return $quote->customer->name.' - '
                        .$quote->lead->branch->name.' '
                        .'<a class="font-weight-bold" href="' . route('biller.customers.show', [$quote->customer->id]) . '"><i class="ft-eye"></i></a>';
                }
                return;
            })
            ->addColumn('created_at', function ($quote) {
                return dateFormat($quote->invoicedate);
            })
            ->addColumn('total', function ($quote) {
                return number_format($quote->total, 2);
            })
            ->addColumn('status', function ($quote) {
                return '<span class="st-' . $quote->status . '">' . trans('payments.' . $quote->status) . '</span>';
            })
            ->addColumn('verified', function ($quote) {
                return $quote->verified;
            })
            ->addColumn('actions', function ($quote) {
                $valid_token = token_validator('', 'q'.$quote->id .$quote->tid, true);

                return '<a href="'.route('biller.print_quote', [$quote->id, 4, $valid_token, 1]).'" class="btn btn-purple round" target="_blank" data-toggle="tooltip" data-placement="top" title="Print"><i class="fa fa-print"></i></a> '
                    .'<a href="'.route('biller.quotes.edit', [$quote, 'page=copy']).'" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="Copy"><i class="fa fa-clone" aria-hidden="true"></i></a> '
                    .$quote->action_buttons;
            })
            ->rawColumns(['notes', 'tid', 'customer', 'actions', 'status', 'total'])
            ->make(true);
    }
}
