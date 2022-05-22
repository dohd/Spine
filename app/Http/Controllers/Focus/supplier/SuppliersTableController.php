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
namespace App\Http\Controllers\Focus\supplier;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\supplier\SupplierRepository;
use App\Http\Requests\Focus\supplier\ManageSupplierRequest;

/**
 * Class SuppliersTableController.
 */
class SuppliersTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var SupplierRepository
     */
    protected $supplier;
    protected $balance = 0;

    /**
     * contructor to initialize repository object
     * @param SupplierRepository $supplier ;
     */
    public function __construct(SupplierRepository $supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * This method return the data of the model
     * @param ManageSupplierRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageSupplierRequest $request)
    {
        if (request('is_transaction')) 
            return $this->invoke_transaction();
        if (request('is_bill')) 
            return $this->invoke_bill();
        if (request('is_statement')) 
            return $this->invoke_statement();

        $core = $this->supplier->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($supplier) {
                return '<a class="font-weight-bold" href="' . route('biller.suppliers.show', [$supplier->id]) . '">' . $supplier->company . '</a>';
            })
            ->make(true);
    }

    public function invoke_transaction()
    {
        $core = $this->supplier->getTransactionsForDataTable();
        return Datatables::of($core)
        ->escapeColumns(['id'])
        ->addIndexColumn()
        ->addColumn('date', function ($tr) {
            return dateFormat($tr->tr_date);
        })
        ->addColumn('type', function ($tr) {
            return $tr->tr_type;
        })
        ->addColumn('note', function ($tr) {
            return $tr->note;
        })
        ->addColumn('bill_amount', function ($tr) {
            return numberFormat($tr->credit);
        })
        ->addColumn('amount_paid', function ($tr) {
            return numberFormat($tr->debit);
        })
        ->addColumn('balance', function ($tr) {
            if ($tr->credit > 0) $this->balance += $tr->credit;
            elseif ($tr->debit > 0) $this->balance -= $tr->debit;

            return numberFormat($this->balance);
        })
        ->make(true);
    }

    public function invoke_bill()
    {
        $core = $this->supplier->getPurchaseorderBillsForDataTable();
        return Datatables::of($core)
        ->escapeColumns(['id'])
        ->addIndexColumn()
        ->addColumn('date', function ($bill) {
            return dateFormat($bill->date);
        })
        ->addColumn('reference', function ($bill) {
            return $bill->doc_ref_type . ' - ' . $bill->doc_ref;
        })
        ->addColumn('note', function ($bill) {
            return $bill->note;
        })
        ->addColumn('amount', function ($bill) {
            return numberFormat($bill->grandttl);
        })
        ->addColumn('paid', function ($bill) {
            return numberFormat($bill->amountpaid);
        })
        ->make(true);
    }

    public function invoke_statement()
    {
        $core = $this->supplier->getStatementsForDataTable();
        return Datatables::of($core)
        ->escapeColumns(['id'])
        ->addIndexColumn()
        ->addColumn('date', function ($tr) {
            return dateFormat($tr->tr_date);
        })
        ->addColumn('type', function ($tr) {
            return $tr->tr_type;
        })
        ->addColumn('note', function ($tr) {
            return $tr->note;
        })
        ->addColumn('bill_amount', function ($tr) {
            return numberFormat($tr->credit);
        })
        ->addColumn('amount_paid', function ($tr) {
            return numberFormat($tr->debit);
        })
        ->addColumn('balance', function ($tr) {
            if ($tr->credit > 0) $this->balance += $tr->credit;
            elseif ($tr->debit > 0) $this->balance -= $tr->debit;
            
            return numberFormat($this->balance);
        })
        ->make(true);
    }
}
