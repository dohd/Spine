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

namespace App\Http\Controllers\Focus\general;

use App\Http\Responses\RedirectResponse;
use App\Models\Company\ConfigMeta;
use App\Models\invoice\Invoice;
use App\Models\product\ProductVariation;
use App\Models\transaction\Transaction;

use App\Http\Controllers\Controller;
use App\Models\transactioncategory\Transactioncategory;
use Illuminate\Support\Facades\DB;


class CoreDashboard extends Controller
{
    public function index()
    {
        if (access()->allow('dashboard-owner')) {
            $today = date('Y-m-d');
            $start_date = date('Y-m') . '-01';

            $invoice = Invoice::with(['customer'])->whereBetween('invoicedate', [date_for_database($start_date), date_for_database($today)])->orderBy('id', 'desc')->take(10);
            $data['invoices'] = $invoice->get();
            $data['customers'] = $invoice->groupBy('customer_id')->get();
            //  dd($data['customers']->pluck('customer_id')->toArray());
            $transactions = Transaction::with(['account'])->whereBetween('due_date', [date_for_database($start_date), date_for_database($today)])->orderBy('id', 'desc')->take(10)->get();

            $data['stock_alert'] = ProductVariation::whereRaw('qty <= alert')->whereHas('product', function ($query) {
                return $query->where('stock_type', '=', 1);
            })->orderBy('id', 'desc')->get();

            return view('focus.dashboard.index', compact('data', 'transactions'));
        }
        if (access()->allow('product-manage')) 
            return new RedirectResponse(route('biller.products.index'), []);
            
        return view('focus.dashboard.no_data');
    }


    public function mini_dash()
    {
        $start_date = date('Y-m') . '-01';
        $today = date('Y-m-d');

        $data['sales_chart'] = Invoice::whereBetween('invoicedate', [$start_date, $today])
        ->groupBy('invoicedate')
        ->select(DB::raw('invoicedate, COUNT(id) as items, SUM(total) as total'))->get();
        
        $data['today_invoices'] = Invoice::whereBetween('invoicedate', [$start_date, $today])
        ->where('invoicedate', $today)
        ->select(DB::raw('invoicedate, COUNT(id) as items, SUM(total) as total'))->get()->first();

        $data['month_invoices'] = Invoice::whereBetween('invoicedate', [$start_date, $today])
        ->select(DB::raw('invoicedate, COUNT(id) as items, SUM(total) as total'))->get()->first();
        
        $data['transactions'] = Transaction::whereBetween('due_date', [$start_date, $today])->where('due_date', $today)
        ->select(DB::raw('due_date, SUM(credit) as credit'))->get()->first();
        
        $sale_categ = Transactioncategory::where('code', 'inv')->first();
        $purchase_categ = Transactioncategory::where('code', 'bill')->first();
        $transactions_today = Transaction::whereIn('trans_category_id', [$sale_categ->id, $purchase_categ->id])
        ->where('due_date', $today)
        ->select(DB::raw('SUM(credit) as credit, SUM(debit) as debit'))->get()->first();

        $transactions = Transaction::whereIn('trans_category_id', [$sale_categ->id, $purchase_categ->id])
        ->whereBetween('due_date', [$start_date, $today])->get();
        $transactions_chart['income'] = $transactions->where('trans_category_id', $sale_categ->id);
        $transactions_chart['expenses'] = $transactions->where('trans_category_id', $purchase_categ->id);
        $transactions_chart['income_total'] = $transactions->where('trans_category_id', $sale_categ->id)->sum('credit');
        $transactions_chart['expenses_total'] = $transactions->where('trans_category_id', $purchase_categ->id)->sum('debit');

        $income_chart = [];
        foreach ($transactions_chart['income'] as $row_i) {
            $income_chart[] = array('x' => $row_i['due_date'], 'y' => (int) $row_i['credit']);
        }
        $expense_chart = [];
        foreach ($transactions_chart['expenses'] as $row_i) {
            $expense_chart[] = array('x' => $row_i['due_date'], 'y' => (int)$row_i['debit']);
        }
        $sales_chart = [];
        foreach ($data['sales_chart'] as $row) {
            $sales_chart[] = array('y' => $row['invoicedate'], 'sales' => intval(numberClean($row['total'])), 'invoices' => intval($row['items']));
        }

        return response()->json([
            'dash' => array(
                numberFormat($data['today_invoices']['items'], 0, 1),
                amountFormat($data['today_invoices']['total'], 0, 1),
                numberFormat($data['month_invoices']['items'], 0, 1),
                amountFormat($data['month_invoices']['total'], 0, 1),
                amountFormat($data['today_invoices']['total'], 0, 1),
                amountFormat($transactions_today['credit']),
                amountFormat($transactions_today['debit']),
                amountFormat($transactions_today['credit'] - $transactions_today['debit'])
            ),
            'income_chart' => $income_chart,
            'expense_chart' => $expense_chart,
            'inv_exp' => array(
                'income' => (int) $transactions_chart['income_total'], 
                'expense' => (int)$transactions_chart['expenses_total']
            ),
            'sales' => $sales_chart,
        ]);
    }

    public function todo()
    {
        $mics = Misc::all();
        $employees = Hrm::all();
        $user = auth()->user()->id;
        $project_select = Project::whereHas('users', function ($q) use ($user) {
            return $q->where('rid', '=', $user);
        })->get();
        return new ViewResponse('focus.projects.tasks.index', compact('mics', 'employees', 'project_select'));
    }
}
