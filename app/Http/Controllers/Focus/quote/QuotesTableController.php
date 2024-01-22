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
        $query = $this->repository->getForDataTable();

        $query_1 = clone $query;
        $sum_total = numberFormat($query_1->sum('total'));

        $ins = auth()->user()->ins;
        $prefixes = prefixesArray(['quote', 'proforma_invoice', 'lead', 'invoice'], $ins);

        return Datatables::of($query)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('tid', function ($quote) use($prefixes) {               
                $link = route('biller.quotes.show', [$quote->id]);
                if ($quote->bank_id) $link = route('biller.quotes.show', [$quote->id, 'page=pi']);
                return '<a class="font-weight-bold" href="' . $link . '">' . gen4tid($quote->bank_id? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid) . '</a>';
            })
            ->filterColumn('tid', function($query, $tid) use($prefixes) {
                $arr = explode('-', $tid);
                if (strtolower($arr[0]) == strtolower($prefixes[1]) && isset($arr[1])) {
                    $query->where('tid', floatval($arr[1]));
                } elseif (floatval($tid)) {
                    $query->where('tid', floatval($tid));
                }
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
            ->orderColumn('date', '-date $1')
            ->addColumn('total', function ($quote) {
                if ($quote->currency) return amountFormat($quote->total, $quote->currency->id);
                return numberFormat($quote->total);
            })   
            ->addColumn('approved_date', function ($quote) {
                return $quote->approved_date? dateFormat($quote->approved_date) : '';
            })
            ->orderColumn('approved_date', '-approved_date $1')
            ->editColumn('lead_tid', function($quote) use($prefixes) {
                $link = '';
                if ($quote->lead) {
                    if (auth()->user()->customer_id) return gen4tid("{$prefixes[2]}-", $quote->lead->reference);
                    $link = '<a href="'. route('biller.leads.show', $quote->lead) .'">'.gen4tid("{$prefixes[2]}-", $quote->lead->reference).'</a>';
                }
                return $link;
            })
            ->filterColumn('lead_tid', function($query, $tid) use($prefixes) {
                $arr = explode('-', $tid);
                if (strtolower($arr[0]) == strtolower($prefixes[2]) && isset($arr[1])) {
                    $query->whereHas('lead', fn($q) => $q->where('reference', floatval($arr[1])));
                } elseif (floatval($tid)) {
                    $query->whereHas('lead', fn($q) => $q->where('reference', floatval($tid)));
                }
            })
            ->addColumn('invoice_tid', function ($quote) use($prefixes) {
                $inv_product = $quote->invoice_product;
                if (@$inv_product->invoice) return gen4tid("{$prefixes[3]}-", $inv_product->invoice->tid);
            })
            ->filterColumn('invoice_tid', function($query, $tid) use($prefixes) {
                $arr = explode('-', $tid);
                if (strtolower($arr[0]) == strtolower($prefixes[3]) && isset($arr[1])) {
                    $query->whereHas('invoice', fn($q) => $q->where('tid', floatval($arr[1])));
                } elseif (floatval($tid)) {
                    $query->whereHas('invoice', fn($q) => $q->where('tid', floatval($tid)));
                }
            })
            ->addColumn('sum_total', function() use($sum_total) {
                return $sum_total;
            })
            ->addColumn('budget_status', function ($quote) {
                return $quote->budget? '<span class="badge badge-success">budgeted</span>' : 
                    '<span class="badge badge-secondary">pending</span>';
            })
            ->addColumn('actions', function ($quote) {
                $action_buttons = $quote->action_buttons;
                if (request('page') == 'pi') {
                    $name = 'biller.quotes.show';
                    $action_buttons = str_replace(route($name, $quote), route($name, [$quote, 'page=pi']), $action_buttons);
                }
                if (auth()->user()->customer_id) return $action_buttons;

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
