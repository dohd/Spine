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
use App\Repositories\Focus\invoice\InvoiceRepository;
use App\Http\Requests\Focus\invoice\ManageInvoiceRequest;

/**
 * Class InvoicesTableController.
 */
class InvoicesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var InvoiceRepository
     */
    protected $invoice;

    /**
     * contructor to initialize repository object
     * @param InvoiceRepository $invoice ;
     */
    public function __construct(InvoiceRepository $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * This method return the data of the model
     * @param ManageInvoiceRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageInvoiceRequest $request)
    {
        $core = $this->invoice->getForDataTable();

        $ins = auth()->user()->ins;
        $prefixes = prefixesArray(['invoice', 'quote', 'proforma_invoice'], $ins);

        // aggregate
        $amount_total = $core->sum('total');
        $balance_total = $amount_total - $core->sum('amountpaid');
        $aggregate = [
            'amount_total' => numberFormat($amount_total),
            'balance_total' => numberFormat($balance_total),
        ];        

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('customer', function ($invoice) {
                $link = '';
                $customer = $invoice->customer;
                if ($customer) {
                    $link = ' <a class="font-weight-bold" href="'. route('biller.customers.show', $customer) .'">'. $customer->name .'</a>'; 
                }
                return $link;             
            })
            ->addColumn('tid', function ($invoice) use($prefixes) {
                return '<a class="font-weight-bold" href="'.route('biller.invoices.show', [$invoice->id]).'">' 
                    . gen4tid("{$prefixes[0]}-", $invoice->tid) .'</a>';
            })
            ->addColumn('invoicedate', function ($invoice) {
                return dateFormat($invoice->invoicedate);
            })
            ->addColumn('total', function ($invoice) {
                return numberFormat($invoice->total);
            })
            ->addColumn('balance', function ($invoice) {
                return numberFormat($invoice->total - $invoice->amountpaid);
            })
            ->addColumn('status', function ($invoice) {
                return '<span class="st-' . $invoice->status . '">' . trans('payments.' . $invoice->status) . '</span>';
            })
            ->addColumn('invoiceduedate', function ($invoice) {
                return dateFormat($invoice->invoiceduedate);
            })
            ->addColumn('quote_tid', function ($invoice) use($prefixes) {
                $links = [];
                foreach ($invoice->products as $item) {
                    $quote = $item->quote;
                    if ($quote) {
                        $tid = gen4tid($quote->bank_id ? "{$prefixes[1]}-" : "{$prefixes[2]}-", $quote->tid);
                        $links[] = '<a href="'. route('biller.quotes.show', $quote) .'">'. $tid .'</a>';
                    }
                }
                return implode(', ', array_unique($links));
            })
            ->addColumn('last_pmt', function ($invoice) {
                $last_pmt = '';
                if ($invoice->payments->count()) {
                    $last_pmt_item = $invoice->payments()->orderBy('id', 'desc')->first();
                    $last_pmt .= dateFormat($last_pmt_item->paid_invoice->date);
                } 
                return $last_pmt;
            })
            ->addColumn('aggregate', function ($invoice) use($aggregate) {
                return $aggregate;
            })
            ->addColumn('actions', function ($invoice) {
                return $invoice->action_buttons;
            })
            ->make(true);
    }
}
