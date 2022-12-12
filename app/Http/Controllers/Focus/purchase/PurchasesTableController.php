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

namespace App\Http\Controllers\Focus\purchase;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\purchase\PurchaseRepository;
use App\Http\Requests\Focus\purchase\ManagePurchaseRequest;

/**
 * Class PurchaseordersTableController.
 */
class PurchasesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var PurchaseRepository
     */
    protected $purchase;

    /**
     * contructor to initialize repository object
     * @param PurchaseRepository $purchaseorder ;
     */
    public function __construct(PurchaseRepository $purchase)
    {
        $this->purchase = $purchase;
    }

    /**
     * This method return the data of the model
     * @param ManagePurchaseorderRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManagePurchaseRequest $request)
    {
        $core = $this->purchase->getForDataTable();

        return Datatables::of($core)
            ->addIndexColumn()
            ->escapeColumns(['id'])
            ->addColumn('tid', function ($purchase) {
                return '<a class="font-weight-bold" href="' . route('biller.purchases.show', $purchase->id) . '">' . gen4tid('DP-', $purchase->tid) . '</a>';
            })
            ->addColumn('date', function ($purchase) {
                return dateFormat($purchase->date);
            })
            ->addColumn('supplier', function ($purchase) {
                $name = $purchase->suppliername;
                if ($purchase->supplier) {
                    $supplier = $purchase->supplier;
                    $name = $name ?: $supplier->name;
                    if ($supplier->taxid) $name .= " - {$supplier->taxid}";
                }

                return ' <a class="font-weight-bold" href="'. route('biller.suppliers.show', $purchase->supplier_id) .'">'. $name .'</a>';
            })
            ->addColumn('reference', function ($purchase) {
                $reference = $purchase->doc_ref_type;
                if ($purchase->doc_ref) $reference .= " - {$purchase->doc_ref}";
                
                return $reference;
            })
            ->addColumn('amount', function ($purchase) {
                return numberFormat($purchase->grandttl);
            })
            ->addColumn('balance', function ($purchase) {
                return numberFormat($purchase->grandttl - $purchase->amountpaid);
            })
            ->addColumn('actions', function ($purchase) {
                return $purchase->action_buttons;
            })
            ->make(true);
    }
}