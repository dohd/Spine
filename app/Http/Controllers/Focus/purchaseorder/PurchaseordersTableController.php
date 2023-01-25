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
namespace App\Http\Controllers\Focus\purchaseorder;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\purchaseorder\PurchaseorderRepository;
use App\Http\Requests\Focus\purchaseorder\ManagePurchaseorderRequest;

/**
 * Class PurchaseordersTableController.
 */
class PurchaseordersTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var PurchaseorderRepository
     */
    protected $purchaseorder;

    /**
     * contructor to initialize repository object
     * @param PurchaseorderRepository $purchaseorder ;
     */
    public function __construct(PurchaseorderRepository $purchaseorder)
    {
        $this->purchaseorder = $purchaseorder;
    }

    /**
     * This method return the data of the model
     * @param ManagePurchaseorderRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->purchaseorder->getForDataTable();

        $prefixes = prefixesArray(['purchase_order'], auth()->user()->ins);
        // aggregate
        $order_total = $core->sum('grandttl');
        $grn_total = 0;
        foreach ($core as $po) {
            $grn_total += $po->grns->sum('total');
        }
        $aggregate = [
            'order_total' => numberFormat($order_total),
            'grn_total' => numberFormat($grn_total),
            'due_total' => numberFormat($order_total - $grn_total),
        ];   

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($po) use($prefixes) {
                return '<a class="font-weight-bold" href="' . route('biller.purchaseorders.show', $po) . '">' . gen4tid("{$prefixes[0]}-", $po->tid) . '</a>';
            })
            ->addColumn('supplier', function ($po) {
                if ($po->supplier)
                return ' <a class="font-weight-bold" href="' . route('biller.suppliers.show', $po->supplier) . '">'. $po->supplier->name . '</a>';
            })
            ->addColumn('count', function ($po) {
                return $po->items->count();
            })
            ->addColumn('date', function ($po) {
                return dateFormat($po->date);
            })
            ->addColumn('amount', function ($po) {
                return numberFormat($po->grandttl);
            })
            ->addColumn('status', function ($po) {
                return $po->status;
            })
            ->addColumn('actions', function ($po) {
                return $po->action_buttons;
            })
            ->addColumn('aggregate', function () use($aggregate) {
                return $aggregate;
            })
            ->make(true);
    }
}
