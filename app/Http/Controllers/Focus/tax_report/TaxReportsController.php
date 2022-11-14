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

namespace App\Http\Controllers\Focus\tax_report;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\creditnote\CreditNote;
use App\Models\invoice\Invoice;
use App\Models\purchase\Purchase;
use App\Models\tax_report\TaxReport;
use App\Repositories\Focus\tax_report\TaxReportRepository;
use Illuminate\Http\Request;


class TaxReportsController extends Controller
{
    /**
     * variable to store the repository object
     * @var TaxReportRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param TaxReportRepository $repository ;
     */
    public function __construct(TaxReportRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.tax_reports.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('focus.tax_reports.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->repository->create($request->except('_token'));

        return new RedirectResponse(route('biller.tax_reports.index'), ['flash_success' => 'Tax Report Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  TaxReport $tax_report
     * @return \Illuminate\Http\Response
     */
    public function edit(TaxReport $tax_report)
    {
        return redirect()->back();

        return view('focus.tax_reports.edit', compact('tax_report'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  TaxReport $tax_report
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TaxReport $tax_report)
    {
        $this->repository->update($tax_report, $request->except('_token'));

        return new RedirectResponse(route('biller.tax_reports.index'), ['flash_success' => 'Tax Report Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  TaxReport $tax_report
     * @return \Illuminate\Http\Response
     */
    public function destroy(TaxReport $tax_report)
    {
        $this->repository->delete($tax_report);

        return new RedirectResponse(route('biller.tax_reports.index'), ['flash_success' => 'Tax Report Deleted Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  TaxReport $tax_report
     * @return \Illuminate\Http\Response
     */
    public function show(TaxReport $tax_report)
    {
        return redirect()->back();

        return view('focus.tax_reports.view', compact('tax_report'));
    }

    /**
     * Display filed report
     */
    public function filed_report()
    {
        return view('focus.tax_reports.filed_report');
    }

    /**
     * Sales to be filed on report creation
     */
    public function get_sales()
    {
        $month = request('sale_month', 0);

        $invoices = Invoice::when($month, fn($q) => $q->whereMonth('invoicedate', $month))
            ->where('tid', '>', 0)
            ->doesntHave('invoice_tax_reports')
            ->get()->map(fn($v) => [
                'id' => $v->id,
                'invoice_tid' => $v->tid,
                'invoice_date' => $v->invoicedate,
                'customer' => $v->customer->company,
                'note' => $v->notes,
                'subtotal' => $v->subtotal,
                'total' => $v->total,
                'tax' => $v->tax,
                'tax_rate' => $v->tax_id,
                'type' => 'invoice',
                'credit_note_date' => '',
                'credit_note_tid' => '',
            ]);
            
        $credit_notes = CreditNote::when($month, function ($q) use($month) {
            $q->whereHas('invoice', fn($q) => $q->whereMonth('invoicedate', $month));
        })
        ->doesntHave('credit_note_tax_reports')
        ->whereNull('supplier_id')->get()->map(fn($v) => [
            'id' => $v->id,
            'credit_note_tid' => $v->tid,
            'credit_note_date' => $v->date,
            'customer' => $v->customer->company,
            'note' => 'Credit Note',
            'subtotal' => $v->subtotal,
            'total' => $v->total,
            'tax' => $v->tax,
            'tax_rate' => ($v->tax/$v->subtotal * 100),
            'type' => 'credit_note',
            'invoice_date' => $v->invoice->invoicedate,
            'invoice_tid' => $v->invoice->tid,
        ]);
            
        $sales = $invoices->merge($credit_notes);

        return response()->json($sales->toArray());
    }

    /**
     * Purchases to be filed on report creation
     */
    public function get_purchases()
    {
        $month = request('purchase_month', 0);
        
        $direct_purchases = Purchase::when($month, fn($q) => $q->whereMonth('date', $month))
            ->doesntHave('purchase_tax_reports')
            ->where('doc_ref_type', 'Invoice')
            ->get()->map(fn($v) => [
                'id' => $v->id,
                'purchase_date' => $v->date,
                'supplier' => $v->supplier->name,
                'invoice_no' => $v->doc_ref,
                'note' => 'Goods',
                'subtotal' => $v->paidttl,
                'total' => $v->grandttl,
                'tax' => $v->grandtax,
                'tax_rate' => $v->tax,
                'type' => 'purchase',
                'debit_note_date' => '',
            ]);

        $debit_notes = CreditNote::when($month, function ($q) use($month) {
            $q->whereHas('supplier', fn($q) => $q->whereMonth('date', $month));
        })
        ->doesntHave('debit_note_tax_reports')
        ->whereNull('customer_id')->get()->map(fn($v) => [
            'id' => $v->id,
            'debit_note_date' => $v->date,
            'supplier' => $v->supplier->name,
            'note' => 'Debit Note',
            'subtotal' => $v->paidttl,
            'total' => $v->grandttl,
            'tax' => $v->grandtax,
            'tax_rate' => ($v->tax/$v->subtotal * 100),
            'type' => 'debit_note',
            'purchase_date' => $v->date,
            'invoice_no' => '',
        ]);
           
        $purchases = $direct_purchases->merge($debit_notes);

        return response()->json($purchases->toArray());
    }    
}
