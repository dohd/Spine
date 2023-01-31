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
class QuotesTableController extends Controller
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
        $core = $this->repository->getForDataTable();

        $sum_total = numberFormat($core->sum('total'));

        $ins = auth()->user()->ins;
        $prefixes = prefixesArray(['quote', 'proforma_invoice', 'lead', 'invoice'], $ins);

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($quote) use($prefixes) {               
                $link = route('biller.quotes.show', [$quote->id]);
                if ($quote->bank_id) $link = route('biller.quotes.show', [$quote->id, 'page=pi']);
                return '<a class="font-weight-bold" href="' . $link . '">' . gen4tid($quote->bank_id? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid) . '</a>';
            })
            ->addColumn('customer', function ($quote) {
                $customer = '';
                if ($quote->customer) {
                    $customer .= $quote->customer->company;
                    if ($quote->branch) $customer .= " - {$quote->branch->name}";
                } elseif ($quote->lead) {
                    $customer .= $quote->lead->client_name;
                }

                return $customer;
            })
            ->addColumn('date', function ($quote) {
                return dateFormat($quote->date);
            })
            ->addColumn('total', function ($quote) {
                if ($quote->currency) return amountFormat($quote->total, $quote->currency->id);
                return numberFormat($quote->total);
            })   
            ->addColumn('lead_tid', function($quote) use($prefixes) {
                $link = '';
                if ($quote->lead) {
                    $link = '<a href="'. route('biller.leads.show', $quote->lead) .'">'.gen4tid("{$prefixes[2]}-", $quote->lead->reference).'</a>';
                }
                return $link;
            })
            ->addColumn('invoice_tid', function ($quote) use($prefixes) {
                $inv_product = $quote->invoice_product;
                if ($inv_product) return gen4tid("{$prefixes[3]}-", $inv_product->invoice->tid);
            })
            ->addColumn('sum_total', function ($quote) use($sum_total) {
                return $sum_total;
            })
            ->addColumn('actions', function ($quote) {
                $action_buttons = $quote->action_buttons;
                if (request('page') == 'pi') {
                    $name = 'biller.quotes.show';
                    $action_buttons = str_replace(route($name, $quote), route($name, [$quote, 'page=pi']), $action_buttons);
                }
                $valid_token = token_validator('', 'q'.$quote->id .$quote->tid, true);
                $copy_text = $quote->bank_id ? 'PI Copy' : 'Quote Copy';
                $task = $quote->bank_id ? 'page=pi&task=pi_to_pi' : 'task=quote_to_quote';

                return '<a href="'.route('biller.print_quote', [$quote->id, 4, $valid_token, 1]).'" class="btn btn-purple round" target="_blank" data-toggle="tooltip" data-placement="top" title="Print"><i class="fa fa-print"></i></a> '
                    .'<a href="'.route('biller.quotes.edit', [$quote, $task]).'" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="'. $copy_text .'"><i class="fa fa-clone" aria-hidden="true"></i></a> '
                    .$action_buttons;
            })
            ->make(true);
    }
}
