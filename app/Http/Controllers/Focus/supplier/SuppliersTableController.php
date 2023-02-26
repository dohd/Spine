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
        if (request('is_transaction')) return $this->invoke_transaction();
        if (request('is_bill')) return $this->invoke_bill();
        if (request('is_statement')) return $this->invoke_statement();
            
        $core = $this->supplier->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($supplier) {
                return '<a class="font-weight-bold" href="' . route('biller.suppliers.show', $supplier) . '">' . $supplier->name . '</a>';
            })
            ->make(true);
    }

    // statement on account data
    public function invoke_transaction()
    {
        $core = $this->supplier->getTransactionsForDataTable();

        // printlog($core->toArray());
        
        // filter out tr with same tr_ref i.e direct_purchase and bill
        // $bill_tr = [];
        // $res_tr = [];
        // foreach ($core as $tr) {
        //     if ($tr->tr_type == 'bill') $bill_tr[$tr->tr_ref] = $tr;
        //     else $res_tr[] = $tr;
        // }

        // $core = collect(array_merge($bill_tr, $res_tr));
        $core = $core->sortBy('tr_date');

        // printlog($core->toArray());

        return Datatables::of($core)
        ->escapeColumns(['id'])
        ->addIndexColumn()
        ->addColumn('date', function ($tr) {
            $date = dateFormat($tr->tr_date);
            $sort_id = strtotime($date);
            return "<span sort_id='{$sort_id}'>{$date}</span>";
        })
        ->addColumn('type', function ($tr) {
            return $tr->tr_type;
        })
        ->addColumn('note', function ($tr) {
            $note = $tr->note;
            return $note;
        })
        ->addColumn('bill_amount', function ($tr) {
            return numberFormat($tr->credit);
        })
        ->addColumn('amount_paid', function ($tr) {
            return numberFormat($tr->debit);
        })
        ->addColumn('account_balance', function ($tr) {
            if ($tr->debit > 0) $this->balance -= $tr->debit;
            elseif ($tr->credit > 0) $this->balance += $tr->credit;

            return numberFormat($this->balance);
        })
        ->make(true);
    }

    // bill data 
    public function invoke_bill()
    {
        $core = $this->supplier->getBillsForDataTable();
        
        return Datatables::of($core)
        ->escapeColumns(['id'])
        ->addIndexColumn()
        ->addColumn('date', function ($bill) {
            return dateFormat($bill->date);
        })
        ->addColumn('status', function ($bill) {
            return $bill->status;
        })
        ->addColumn('note', function ($bill) {
            return gen4tid('BILL-', $bill->tid) . ' - ' . $bill->note;
        })
        ->addColumn('amount', function ($bill) {
            return numberFormat($bill->total);
        })
        ->addColumn('paid', function ($bill) {
            return numberFormat($bill->amount_paid);
        })
        ->make(true);
    }

    // statement on bill data
    public function invoke_statement()
    {
        $core = $this->supplier->getStatementForDataTable();
        
        return Datatables::of($core)
        ->escapeColumns(['id'])
        ->addIndexColumn()
        ->addColumn('date', function ($statement) {
            return dateFormat($statement->date);
        })
        ->addColumn('type', function ($statement) {
            $record = $statement->type;
            switch ($record) {
                case 'bill': 
                    $record = '<a href="'. route('biller.utility-bills.show', $statement->bill_id) .'">'. $record .'</a>';
                    break;
                case 'payment': 
                    // $type = '<a href="'. route('biller.invoices.show', $statement->invoice_id) .'">'. $type .'</a>';
                    break; 
            }
            
            return $record;
        })
        ->addColumn('note', function ($statement) {
            return $statement->note;
        })
        ->addColumn('bill_amount', function ($statement) {
            return numberFormat($statement->credit);
        })
        ->addColumn('amount_paid', function ($statement) {
            return numberFormat($statement->debit);
        })
        ->addColumn('bill_balance', function ($statement) {
            if ($statement->type == 'bill') 
                $this->balance = $statement->credit;
            else $this->balance -= $statement->debit;

            return numberFormat($this->balance);
        })
        ->make(true);
    }
}
