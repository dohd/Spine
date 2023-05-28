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

namespace App\Http\Controllers\Focus\project;

use App\Http\Controllers\Controller;
use App\Models\items\ProjectstockItem;
use App\Models\items\PurchaseItem;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\project\ProjectRepository;
use App\Models\items\GoodsreceivenoteItem;

/**
 * Class ProjectsTableController.
 */
class ExpensesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProjectRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProjectRepository $repository ;
     */
    public function __construct(ProjectRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->get_expenses();
        $core = $this->request_filter($core);
        $group_totals = $this->group_totals($core);

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('exp_category', function ($item) {
                $exp_category = '';
                switch ($item->exp_category) {
                    case 'dir_purchase_stock':
                        $exp_category = 'Direct Purchase Stock';
                        break;
                    case 'dir_purchase_service':
                        $exp_category = 'Direct Purchase Service';
                        break;
                    case 'purchase_order_stock':
                        $exp_category = 'Purchase Order Stock';
                        break;
                    case 'inventory_stock':
                        $exp_category = 'Inventory Stock';
                        break;
                    case 'labour_service':
                        $exp_category = 'Labour Service';
                        break;
                }
                if ($item->ledger_account)
                    return "{$exp_category}<br>(Account: {$item->ledger_account})";
                return $exp_category;
            })
            ->editColumn('qty', function ($item) {
                return +$item->qty;
            })
            ->editColumn('rate', function ($item) {
                return numberFormat($item->rate);
            })
            ->editColumn('amount', function ($item) {
                return numberFormat($item->amount);
            })
            ->addColumn('group_totals', function () use ($group_totals) {
                return $group_totals;
            })
            ->make(true);
    }

    /**
     * Apply Expense Filter
     */
    public function request_filter($expenses)
    {
        $params = request()->only(['exp_category', 'ledger_id', 'supplier_id']);
        $params = array_filter($params);
        if (!$params) return $expenses;

        return $expenses->filter(function ($item) use ($params) {
            $eval = 0;
            foreach ($params as $key => $value) {
                if ($item->$key == $value) $eval += 1;
            }
            return count($params) == $eval;
        });
    }

    /**
     * Expense Group Totals
     */
    public function group_totals($expenses = [])
    {
        $group_totals = [];
        foreach ($expenses as $expense) {
            if (@$group_totals[$expense->exp_category])
                $group_totals[$expense->exp_category] += $expense->amount * 1;
            else $group_totals[$expense->exp_category] = $expense->amount * 1;
        }
        $group_totals['grand_total'] = collect(array_values($group_totals))->sum();

        return $group_totals;
    }

    /**
     * Collect Related Project Expenses
     */
    public function get_expenses()
    {
        $indx = 0;
        $expenses = collect();

        // direct purchase
        $dir_purchase_items = PurchaseItem::whereHas('project', fn ($q) => $q->where('projects.id', request('project_id')))
            ->with('purchase', 'account')
            ->get();
        foreach ($dir_purchase_items as $item) {
            $indx++;
            $data = (object) [
                'id' => $indx,
                'exp_category' => $item->type == 'Stock' ? 'dir_purchase_stock' : ($item->type == 'Expense' ? 'dir_purchase_service' : ''),
                'ledger_id' => @$item->account->id,
                'ledger_account' => @$item->account->holder,
                'supplier_id' => @$item->purchase->supplier->id,
                'supplier' => @$item->purchase->suppliername ? $item->purchase->suppliername : ($item->purchase->supplier ? $item->purchase->supplier->name : ''),
                'product_name' => $item->purchase? '(' . gen4tid('DP-', $item->purchase->tid) . ') <br>' . $item->description : '',
                'uom' => $item->uom,
                'qty' => $item->qty,
                'rate' => $item->qty > 0 ? ($item->amount / $item->qty) : $item->amount,
                'amount' => $item->amount,
            ];
            $expenses->add($data);
        }

        // inventory stock (issued)
        $issued_items = ProjectstockItem::whereHas('project_stock', function ($q) {
            $q->whereHas('quote', function ($q) {
                $q->whereHas('project', fn ($q) => $q->where('projects.id', request('project_id')));
            });
        })
        ->get();
        foreach ($issued_items as $item) {
            $indx++;
            $product_variation = $item->product_variation;
            $data = (object) [
                'id' => $indx,
                'exp_category' => 'inventory_stock',
                'ledger_id' => '',
                'ledger_account' => '',
                'supplier_id' => '',
                'supplier' => '',
                'product_name' => @$product_variation->name,
                'uom' => $item->unit,
                'qty' => $item->qty,
                'rate' => @$product_variation->purchase_price ?: 0,
                'amount' => @$product_variation ? $product_variation->purchase_price * $item->qty : 0,
            ];
            $expenses->add($data);
        }

        // purchase order goods received
        $goods_receive_items = GoodsreceivenoteItem::whereHas('purchaseorder_item', function ($q) {
            $q->where('itemproject_id', request('project_id'));
        })->get();
        foreach ($goods_receive_items as $item) {
            $indx++;
            $grn = $item->goodsreceivenote;
            $po_item = $item->purchaseorder_item;
            $po = @$po_item->purchaseorder;
            $data = (object) [
                'id' => $indx,
                'exp_category' => 'purchase_order_stock',
                'ledger_id' => @$item->account->id,
                'ledger_account' => @$item->account->holder,
                'supplier_id' => @$grn->supplier->id,
                'supplier' => @$grn->supplier->name,
                'product_name' => $po? '(' . gen4tid('PO-', $po->tid) . ') <br>' . @$po_item->description : '',
                'uom' => @$po_item->uom,
                'qty' => $item->qty,
                'rate' => $item->rate,
                'amount' => $item->rate * $item->qty,
            ];
            $expenses->add($data);
        }

        return $expenses;
    }
}
