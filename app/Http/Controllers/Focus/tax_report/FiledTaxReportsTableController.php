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
use App\Repositories\Focus\tax_report\TaxReportRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class FiledTaxReportsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var TaxReportRepository
     */
    protected $repository;

    // sale variables
    protected $invoice;
    protected $credit_note;
    protected $customer;

    // purchase variables
    protected $purchase;
    protected $debit_note;
    protected $supplier;

    /**
     * contructor to initialize repository object
     * @param TaxReportRepository $repository ;
     */
    public function __construct(TaxReportRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        if (request('is_sale')) return $this->sale_data();
        if (request('is_purchase')) return $this->purchase_data();
    }

    // sale table data
    public function sale_data()
    {
        $core = $this->repository->getForSalesDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->addColumn('pin', function ($item) {
                if ($item->credit_note) {
                    $this->credit_note = $item->credit_note;
                    if ($this->credit_note->customer) {
                        $this->customer = $this->credit_note->customer;
                    }
                    $this->invoice = null;
                } elseif ($item->invoice) {
                    $this->invoice = $item->invoice;
                    $this->customer = $this->invoice->customer;
                    $this->credit_note = null;
                } else {
                    $this->customer = (object) ['taxid' => '', 'company' => ''];
                }

                return $this->customer->taxid;
            })
            ->addColumn('customer', function ($item) {
                return $this->customer->company;
            })
            ->addColumn('etr_code', function ($item) {
                return 'KRAMW043202206040503';
            })
            ->addColumn('invoice_date', function ($item) {
                $date = '';
                if ($this->credit_note) $date = $this->credit_note->date;
                if ($this->invoice) $date = $this->invoice->invoicedate;
                if ($date) return dateFormat($date);
            })
            ->addColumn('invoice_no', function ($item) {
                $tid = '';
                if ($this->credit_note) $tid = $this->credit_note->tid;
                if ($this->invoice) $tid = $this->invoice->tid;
                return $tid;
            })
            ->addColumn('note', function ($item) {
                $note = '';
                if ($this->credit_note) $note = 'Credit Note';
                if ($this->invoice) $note = $this->invoice->notes;
                return $note;
            })
            ->addColumn('subtotal', function ($item) {
                $subtotal = 0;
                if ($this->credit_note) $subtotal = $this->credit_note->subtotal;
                if ($this->invoice) $subtotal = $this->invoice->subtotal;
                return numberFormat($subtotal);
            })
            ->addColumn('empty_col', function ($item) {
                return '';
            })
            ->addColumn('cn_invoice_no', function ($item) {
                if ($this->credit_note) {
                    $invoice = $this->credit_note->invoice;
                    if ($invoice) return $invoice->tid;
                }
            })
            ->addColumn('cn_invoice_date', function ($item) {
                if ($this->credit_note) {
                    $invoice = $this->credit_note->invoice;
                    if ($invoice) return dateFormat($invoice->invoicedate);
                }
            })
            ->make(true);
    }

    // purchases table data
    public function purchase_data()
    {
        $core = $this->repository->getForPurchasesDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->addColumn('pin', function ($item) {
                if ($item->debit_note) {
                    $this->debit_note = $item->debit_note;
                    if ($this->debit_note->supplier) 
                        $this->supplier = $this->debit_note->supplier;
                    $this->purchase = null;
                } elseif ($item->purchase) {
                    $this->purchase = $item->purchase;
                    $this->supplier = $this->purchase->supplier;
                    $this->debit_note = null;
                } else {
                    $this->supplier = (object) ['' => '', 'name' => ''];
                }

                return $this->supplier->taxid;
            })
            ->addColumn('supplier', function ($item) {
                $suppliername = '';
                $bill = $item->purchase;
                if ($bill && $bill->purchase) {
                    $suppliername .= $bill->purchase->suppliername;
                }   
                
                return $suppliername ?: $this->supplier->name;
            })
            ->addColumn('invoice_date', function ($item) {
                $date = '';
                if ($this->debit_note) $date = $this->debit_note->date;
                if ($this->purchase) $date = $this->purchase->date;
                if ($date) return dateFormat($date);
            })
            ->addColumn('invoice_no', function ($item) {
                $tid = '';
                if ($this->debit_note) $tid = $this->debit_note->tid;
                elseif ($item->purchase) $tid = $item->purchase->reference;
                return $tid;
            })
            ->addColumn('note', function ($item) {
                $note = ($this->purchase && $this->purchase->tax == 8)? 'Fuel' : 'Goods';
                if ($this->debit_note) $note = 'Credit Note';
                return $note;
            })
            ->addColumn('subtotal', function ($item) {
                $subtotal = 0;
                if ($this->debit_note) $subtotal = $this->debit_note->subtotal;
                if ($this->purchase) $subtotal = $this->purchase->subtotal;
                return numberFormat($subtotal);
            })
            ->addColumn('empty_col', function ($item) {
                return '';
            })
            ->addColumn('source', function ($item) {
                return 'local';
            })
            ->make(true);
    }
}
