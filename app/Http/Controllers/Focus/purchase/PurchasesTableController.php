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
     * @var PurchaseorderRepository
     */
    protected $purchase;

    /**
     * contructor to initialize repository object
     * @param PurchaseorderRepository $purchaseorder ;
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
                return '<a class="font-weight-bold" href="' . route('biller.purchaseorders.show', [$purchase->id]) . '">' . $purchase->id . '</a>';
            })
            ->addColumn('date', function ($purchase) {
                return dateFormat($purchase->date);
            })
            ->addColumn('supplier', function ($purchase) {
                if ($purchase->supplier)
                    return $purchase->supplier->name . ' <a class="font-weight-bold" href="' . route('biller.suppliers.show', [$purchase->supplier->id]) . '">
                        <i class="ft-eye"></i></a>';

                return $purchase->suppliername;
            })
            ->addColumn('debit', function ($purchase) {
                return;
            })
            ->addColumn('credit', function ($purchase) {
                return;
            })
            ->addColumn('balance', function ($purchase) {
                return;
            })
            ->addColumn('actions', function ($purchase) {
                $type = $purchase->supplier_type;
                if ($type == 'supplier') {
                    return '<a class="btn btn-purple round" href="' . route('biller.makepayment.single_payment', [$purchase->id]) . '" title="List">
                        <i class="fa fa-cc-visa"></i></a>'. $purchase->action_buttons;
                }
                return '<a class="btn btn-purple round" href="' . route('biller.makepayment.single_payment', [$purchase->id]) . '" title="List">
                    <i class="fa fa-cc-visa"></i></a>' . $purchase->action_buttons;
            })
            ->make(true);
    }
}