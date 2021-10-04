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

use Carbon\Carbon;
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
            ->addColumn('tid', function ($purchase) {
                return '<a class="font-weight-bold" href="' . route('biller.purchaseorders.show', [$purchase->id]) . '">' . $purchase->tid . '</a>';
            })
            ->addColumn('trans_date', function ($purchase) {
                return dateFormat($purchase->transaction_date);
            })

            ->addColumn('supplier_id', function ($purchase) {

                if ($purchase->payer_id) {
                    switch ($purchase->payer_type) {
                        case 'supplier':
                            return $purchase->supplier->name . ' <a class="font-weight-bold" href="' . route('biller.suppliers.show', [$purchase->supplier->id]) . '"><i class="ft-eye"></i></a>';
                            break;
                        case 'customer':
                          return $purchase->customer->company . ' <a class="font-weight-bold" href="' . route('biller.suppliers.show', [$purchase->customer->id]) . '"><i class="ft-eye"></i></a>';
                            break;
                             case 'walkin':
                            return $purchase->payer;
                            break;
                    }
                }
                if ($purchase->payer) return $purchase->payer;


            })

            ->addColumn('debit', function ($purchase) {
                return amountFormat($purchase->debit);
            })

             ->addColumn('credit', function ($purchase) {
                return amountFormat($purchase->credit);
            })
             ->addColumn('balance', function ($purchase) {
                return amountFormat($purchase->credit-$purchase->total_paid_amount);
            })
            ->addColumn('created_at', function ($purchaseorder) {
                return dateFormat($purchaseorder->invoicedate);
            })
            
           
            ->addColumn('actions', function ($purchase) {

                 
                    switch ($purchase->payer_type) {
                        case 'supplier':
                             return '<a class="btn btn-purple round" href="' . route('biller.makepayment.single_payment', [$purchase->id]) . '" title="List"><i class="fa fa-cc-visa"></i></a>' . $purchase->action_buttons;
                            break;
                        case 'customer':
                        if($purchase->debit>0){
                               return '<a class="btn btn-purple round" href="' . route('biller.makepayment.receive_single_payment', [$purchase->id]) . '" title="List"><i class="fa fa-cc-visa"></i></a>' ;

                        }
                       
                          //.$purchase->action_buttons;
                            break;
                             case 'walkin':
                             return '<a class="btn btn-purple round" href="' . route('biller.makepayment.single_payment', [$purchase->id]) . '" title="List"><i class="fa fa-cc-visa"></i></a>' . $purchase->action_buttons;
                            break;
                    }
                

                //return $purchase->action_buttons;
            })->rawColumns(['tid', 'supplier_id','actions'])
            ->make(true);
    }
}
