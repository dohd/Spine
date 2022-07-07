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

namespace App\Http\Controllers\Focus\customer;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\customer\CustomerRepository;
use App\Http\Requests\Focus\customer\ManageCustomerRequest;

/**
 * Class CustomersTableController.
 */
class CustomersTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var CustomerRepository
     */
    protected $customer;
    protected $balance = 0;

    /**
     * contructor to initialize repository object
     * @param CustomerRepository $customer ;
     */
    public function __construct(CustomerRepository $customer)
    {
        $this->customer = $customer;
    }

    /**
     * This method return the data of the model
     * @param ManageCustomerRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageCustomerRequest $request)
    {
        if (request('is_transaction')) 
            return $this->invoke_transaction();
        if (request('is_invoice')) 
            return $this->invoke_invoice();
        if (request('is_statement')) 
            return $this->invoke_statement();

        $core = $this->customer->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($customer) {
                return '<a class="font-weight-bold" href="' . route('biller.customers.show', $customer) . '">' . $customer->name . '</a>';
            })
            ->make(true);
    }

    public function invoke_transaction()
    {
        $core = $this->customer->getTransactionsForDataTable();

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
            if ($tr->invoice && $tr->tr_type == 'inv')
                return gen4tid('Inv-', $tr->invoice->tid) . ' - ' . $tr->invoice->notes;
            return $tr->note;
        })
        ->addColumn('invoice_amount', function ($tr) {
            return numberFormat($tr->debit);
        })
        ->addColumn('amount_paid', function ($tr) {
            return numberFormat($tr->credit);
        })
        ->addColumn('account_balance', function ($tr) {
            if ($tr->debit > 0) $this->balance += $tr->debit;
            elseif ($tr->credit > 0) $this->balance -= $tr->credit;

            return numberFormat($this->balance);
        })
        ->make(true);
    }

    public function invoke_invoice()
    {
        $core = $this->customer->getInvoicesForDataTable();

        return Datatables::of($core)
        ->escapeColumns(['id'])
        ->addIndexColumn()
        ->addColumn('date', function ($invoice) {
            return dateFormat($invoice->invoicedate);
        })
        ->addColumn('status', function ($invoice) {
            return $invoice->status;
        })
        ->addColumn('note', function ($invoice) {
            return gen4tid('Inv-', $invoice->tid) . ' - ' . $invoice->notes;
        })
        ->addColumn('amount', function ($invoice) {
            return numberFormat($invoice->total);
        })
        ->addColumn('paid', function ($invoice) {
            return numberFormat($invoice->amountpaid);
        })
        ->make(true);
    }

    public function invoke_statement()
    {
        $core = $this->customer->getStatementsForDataTable();

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
            if ($tr->invoice && $tr->tr_type == 'inv')
                return gen4tid('Inv-', $tr->invoice->tid) . ' - ' . $tr->invoice->notes;
            return $tr->note;
        })
        ->addColumn('invoice_amount', function ($tr) {
            return numberFormat($tr->debit);
        })
        ->addColumn('amount_paid', function ($tr) {
            return numberFormat($tr->credit);
        })
        ->addColumn('invoice_balance', function ($tr) {
            if ($tr->tr_type == 'inv') $this->balance = $tr->debit;
            $this->balance -= $tr->credit;

            return numberFormat($this->balance);
        })
        ->make(true);
    }
}
