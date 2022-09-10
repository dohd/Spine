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
                return ' <a class="font-weight-bold" href="'. route('biller.customers.show', [$invoice->customer->id]) .'">'
                    . $invoice->customer->name .'</a>';                    
            })
            ->addColumn('tid', function ($invoice) {
                return '<a class="font-weight-bold" href="'.route('biller.invoices.show', [$invoice->id]).'">' 
                    . gen4tid('Inv-', $invoice->tid) .'</a>';
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
            ->addColumn('quote_tid', function ($invoice) {
                $tids = array();
                foreach ($invoice->products as $item) {
                    $quote = $item->quote;
                    if ($quote) {
                        $tids[] = '<a href="'. route('biller.quotes.show', $quote) .'">'
                            . gen4tid($quote->bank_id ? 'PI-' : 'QT-', $quote->tid) .'</a>';
                    }
                }
                return implode(', ', $tids);
            })
            ->addColumn('last_pmt', function ($invoice) {
                if ($invoice->payments->count()) {
                    $last_pmt_item = $invoice->payments()->orderBy('id', 'desc')->first();
                    return dateFormat($last_pmt_item->paid_invoice->date);
                } 

                return '';
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
