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
use App\Models\items\PurchaseItem;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\project\ProjectRepository;

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
        // ['id', 'exp_category', 'supplier', 'product_name', 'uom', 'qty', 'rate', 'amount']
        $indx = 0;
        $dir_purchase_items = PurchaseItem::whereHas('project', fn($q) => $q->where('projects.id', request('project_id')))
            ->with('purchase', 'account')
            ->latest()->get();
        $dir_purchases = collect();
        foreach ($dir_purchase_items as $item) {
            $indx++;
            $data = (object) [
                'id' => $indx,
                'exp_category' => $item->type == 'Stock'? 'dir_purchase_stock' : ($item->type == 'Expense'? 'dir_purchase_service' : ''),
                'exp_account' => @$item->account->holder,
                'supplier' => @$item->purchase->suppliername ? $item->purchase->suppliername : ($item->purchase->supplier? $item->purchase->supplier->name : ''),
                'product_name' => $item->description,
                'uom' => $item->uom,
                'qty' => $item->qty,
                'rate' => $item->qty > 0? ($item->amount/$item->qty) : $item->amount,
                'amount' => $item->amount,
            ];
            $dir_purchases->add($data);
        }
        
        return Datatables::of($dir_purchases)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('exp_category', function ($item) {
                $exp_category = '';
                switch ($item->exp_category) {
                    case 'dir_purchase_stock': $exp_category = 'Direct Purchase Stock'; break;
                    case 'dir_purchase_service': $exp_category = 'Direct Purchase Service'; break;
                    case 'purchase_order_stock': $exp_category = 'Purchase Order Stock'; break;
                    case 'inventory_stock': $exp_category = 'Purchase Order Stock'; break;
                    case 'labour_service': $exp_category = 'Labour Service'; break;
                }

                if ($item->exp_account) return "{$exp_category}<br>(Account: {$item->exp_account})";
                return $exp_category;
            })
            ->make(true);
    }
}