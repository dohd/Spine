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
use App\Models\additional\Additional;
use App\Models\Company\Company;
use App\Models\creditnote\CreditNote;
use App\Models\invoice\Invoice;
use App\Models\purchase\Purchase;
use App\Models\tax_report\TaxReport;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\Focus\tax_report\TaxReportRepository;
use DateTime;
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
        $additionals = Additional::where('value', '>', 0)->get();
        $zero_rated = Additional::where('value', 0)->latest()->limit(1)->get();
        $additionals = $additionals->merge($zero_rated);

        $prev_month = (date('m')-1) . '-' . date('Y');
        
        return view('focus.tax_reports.create', compact('additionals', 'prev_month'));
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
        $additionals = Additional::all();
        
        return view('focus.tax_reports.edit', compact('tax_report', 'additionals'));
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
        return view('focus.tax_reports.view', compact('tax_report'));
    }

    /**
     * Display filed report
     */
    public function filed_report()
    {
        $tax_reports = TaxReport::get(['id', 'title']);
        $company = Company::find(auth()->user()->ins,['id', 'etr_code']);

        $additionals = Additional::where('value', '>', 0)->get();
        $zero_rated = Additional::where('value', 0)->latest()->limit(1)->get();
        $additionals = $additionals->merge($zero_rated);

        return view('focus.tax_reports.filed_report', compact('tax_reports', 'company', 'additionals'));
    }

    /**
     * Sales to be filed on report creation
     */
    public function get_sales()
    {
        $sale_month = explode('-', request('sale_month', '0-0'));
        $month = current($sale_month);
        $year = end($sale_month);
        printlog($sale_month);
        $invoices = Invoice::when($month, fn($q) => $q->whereMonth('invoicedate', $month)->whereYear('invoicedate', $year))
            ->where('tid', '>', 0)
            ->where(function ($q) {
                $q->doesntHave('invoice_tax_reports');
                $q->orWhereHas('invoice_tax_reports', function ($q) {
                    $q->where('is_filed', 0);
                });
            })
            ->get()->map(fn($v) => [
                'id' => $v->id,
                'invoice_tid' => $v->tid,
                'invoice_date' => $v->invoicedate,
                'tax_pin' => $v->customer->taxid ?: '',
                'customer' => $v->customer->company ?: '',
                'note' => $v->notes,
                'subtotal' => $v->subtotal,
                'total' => $v->total,
                'tax' => $v->tax,
                'tax_rate' => $v->tax_id,
                'type' => 'invoice',
                'credit_note_date' => '',
                'credit_note_tid' => '',
            ]);

        $credit_notes = CreditNote::when($month, fn($q) => $q->whereMonth('date', $month))
        ->where(function ($q) {
            $q->doesntHave('credit_note_tax_reports');
            $q->orWhereHas('credit_note_tax_reports', function ($q) {
                $q->where('is_filed', 0);
            });
        })
        ->whereNull('supplier_id')->get()->map(fn($v) => [
            'id' => $v->id,
            'credit_note_tid' => $v->tid,
            'invoice_date' => $v->date,
            'tax_pin' => $v->customer->taxid ?: '',
            'customer' => $v->customer->company,
            'note' => 'Credit Note',
            'subtotal' => -1 * $v->subtotal,
            'total' => -1 * $v->total,
            'tax' =>  -1 * $v->tax,
            'tax_rate' => ($v->tax/$v->subtotal * 100),
            'type' => 'credit_note',
            'credit_note_date' => $v->invoice->invoicedate,
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
        $purchase_month = explode('-', request('purchase_month', '0-0'));
        $month = current($purchase_month);
        $year = end($purchase_month);
        
        $bills = UtilityBill::when($month, fn($q) => $q->whereMonth('date', $month)->whereYear('date', $year))
            ->where('tid', '>', 0)
            ->whereIn('document_type', ['direct_purchase', 'goods_receive_note'])
            ->where(function ($q) {
                $q->doesntHave('purchase_tax_reports');
                $q->orWhereHas('purchase_tax_reports', function ($q) {
                    $q->where('is_filed', 0);
                });
            })
            ->get()->map(function ($v) {
                $note = '';
                $suppliername = '';
                $supplier_taxid = '';
                if ($v->document_type == 'direct_purchase') {         
                    $purchase = $v->purchase;
                    if ($purchase) {
                        if ($v->tax_rate == 8) {
                            $note .= gen4tid('DP-', $purchase->tid) . ' Fuel';
                        } else $note .= gen4tid('DP-', $purchase->tid) . ' Goods';
                        $suppliername .= $purchase->suppliername;
                        $supplier_taxid .= $purchase->supplier_taxid;
                    }                    
                } elseif ($v->document_type == 'goods_receive_note') {
                    $grn = $v->grn;
                    if ($grn) {
                        if ($v->tax_rate == 8) {
                            $note .= gen4tid('Grn-', $grn->tid) . ' Fuel';
                        } else $note .= gen4tid('Grn-', $grn->tid) . ' Goods';
                    } 
                }
                
                return [
                    'id' => $v->id,
                    'purchase_date' => $v->date,
                    'tax_pin' => $supplier_taxid ?: $v->supplier->taxid,
                    'supplier' => $suppliername ?: $v->supplier->name,
                    'invoice_no' => $v->reference,
                    'note' => $note,
                    'subtotal' => $v->subtotal,
                    'total' => $v->total,
                    'tax' => $v->tax,
                    'tax_rate' => $v->tax_rate,
                    'type' => 'purchase',
                    'debit_note_date' => '',
                ];
            });

        $debit_notes = CreditNote::when($month, function ($q) use($month) {
            $q->whereHas('supplier', fn($q) => $q->whereMonth('date', $month));
        })
        ->where(function ($q) {
            $q->doesntHave('debit_note_tax_reports');
            $q->orWhereHas('debit_note_tax_reports', function ($q) {
                $q->where('is_filed', 0);
            });
        })
        ->whereNull('customer_id')->get()->map(fn($v) => [
            'id' => $v->id,
            'debit_note_date' => $v->date,
            'tax_pin' => $v->supplier->taxid ?: '',
            'supplier' => $v->suppliername ?: $v->supplier->name,
            'note' => 'Debit Note',
            'subtotal' => $v->subtotal,
            'total' => $v->total,
            'tax' => $v->tax,
            'tax_rate' => ($v->tax/$v->subtotal * 100),
            'type' => 'debit_note',
            'purchase_date' => $v->date,
            'invoice_no' => '',
        ]);
           
        $purchases = $bills->merge($debit_notes);

        return response()->json($purchases->toArray());
    }    
}
