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

use App\Models\invoice\Invoice;
use App\Models\product\ProductVariation;
use App\Models\transaction\Transaction;
use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\misc\Misc;
use App\Models\project\Project;
use Illuminate\Support\Facades\DB;


class CoreDashboard extends Controller
{
    public function index()
    {   
        $user = auth()->user();
        if (!$user->password_updated_at) {
            session()->put('flash_success', 'Please Update Your Password.');
        }
        if (!access()->allow('dashboard-owner')) {
            return view('focus.dashboard.common');
        }
        
            
        $start_date = date('Y-m') . '-01';
        $today = date('Y-m-d');
        // invoices
        $data['invoices'] = Invoice::whereBetween('invoicedate', [$start_date, $today])
            ->with('customer')
            ->latest()->limit(10)->get();
        // customers
        $data['customers'] = Customer::whereIn('id', $data['invoices']->pluck('customer_id')->toArray())->get();
        // stock alerts
        $data['stock_alert'] = ProductVariation::whereRaw('qty <= alert')
            ->whereHas('product', fn($q) => $q->where('stock_type', 'general'))
            ->orderBy('id', 'desc')->get();
        // transactions
        $transactions = Transaction::whereBetween('tr_date', [$start_date, $today])
            ->orderBy('id', 'desc')
            ->with('account')
            ->take(10)->get();

        return view('focus.dashboard.index', compact('data', 'transactions'));
    }


    public function mini_dash()
    {
        $start_date = date('Y-m') . '-01';
        $today = date('Y-m-d');
        // yesterday
        $today = date_for_database($today . ' -1 days');
        
        // invoices
        $today_invoices = Invoice::select(DB::raw('invoicedate, COUNT(*) as items, SUM(total) as total'))
        ->where('invoicedate', $today)
        ->groupBy('invoicedate')->first();
        if (!$today_invoices) $today_invoices = new Invoice;

        $this_month_invoices = Invoice::select(DB::raw('invoicedate, COUNT(*) as items, SUM(total) as total'))
        ->whereBetween('invoicedate', [$start_date, $today])
        ->groupBy('invoicedate')->first();
        if (!$this_month_invoices) $this_month_invoices = new Invoice;

        // transaction
        $transactions_today = Transaction::select(DB::raw('SUM(credit) as credit, SUM(debit) as debit'))
        ->whereHas('category', function ($q) {
            $q->whereIn('code', ['inv', 'bill']);
        })
        ->where('tr_date', $today)
        ->groupBy('tr_date')->first();
        if (!$transactions_today) $transactions_today = new Transaction;

        $income_transactions = Transaction::whereBetween('tr_date', [$start_date, $today])
        ->whereHas('category', function ($q) {
            $q->whereIn('code', ['inv']);
        })->get();
        $expense_transactions = Transaction::whereBetween('tr_date', [$start_date, $today])
        ->whereHas('category', function ($q) {
            $q->whereIn('code', ['bill']);
        })->get();
        

        $income_chart = array_map(function ($v) {
            return array('x' => $v['tr_date'], 'y' => (int) $v['credit']);
        }, $income_transactions->toArray());

        $expense_chart = array_map(function ($v) {
            return array('x' => $v['tr_date'], 'y' => (int) $v['debit']);
        }, $expense_transactions->toArray());

        $sales_chart = [];
        $this_month_invoices_array = $this_month_invoices->toArray();
        if ($this_month_invoices_array) {
            $sales_chart = array_map(function ($v) {
                return array('y' => $v['invoicedate'], 'sales' => (int) $v['total'], 'invoices' => (int) $v['items']);
            }, [$this_month_invoices_array]);    
        }

        return response()->json([
            'dash' => array(
                number_format($today_invoices->items, 1),
                amountFormat($today_invoices->total, 0, 1),
                number_format($this_month_invoices->items, 1),
                amountFormat($this_month_invoices->total, 0, 1),
                amountFormat($today_invoices->total, 0, 1),
                amountFormat($transactions_today->credit),
                amountFormat($transactions_today->debit),
                amountFormat($transactions_today->credit - $transactions_today->debit)
            ),
            'income_chart' => $income_chart,
            'expense_chart' => $expense_chart,
            'inv_exp' => array(
                'income' => (int) $income_transactions->sum('credit'), 
                'expense' => (int) $expense_transactions->sum('debit')
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
            return $q->where('rid', $user);
        })->get();

        return new ViewResponse('focus.projects.tasks.index', compact('mics', 'employees', 'project_select'));
    }
}
