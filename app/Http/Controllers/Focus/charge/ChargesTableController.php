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
namespace App\Http\Controllers\Focus\charge;

use App\Http\Requests\Focus\general\ManageCompanyRequest;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\charge\ChargeRepository;
use App\Http\Requests\Focus\charge\ManageChargeRequest;

/**
 * Class BanksTableController.
 */
class ChargesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var BankRepository
     */
    protected $charge;

    /**
     * contructor to initialize repository object
     * @param BankRepository $charge ;
     */
    public function __construct(ChargeRepository $charge)
    {
        $this->charge = $charge;
    }

    /**
     * This method return the data of the model
     * @param ManageBankRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageChargeRequest $request)
    {
        //
        $core = $this->charge->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('account_id', function ($charge) {
                return $charge->account->holder;
            })
             ->addColumn('transaction_date', function ($charge) {
                return dateFormat($charge->transaction_date);
            })
              ->addColumn('debit', function ($charge) {
                return amountFormat($charge->debit);
            })
           ->addColumn('credit', function ($charge) {
                return amountFormat($charge->credit);
            })

            ->addColumn('created_at', function ($charge) {
                return Carbon::parse($charge->created_at)->toDateString();
            })
            ->addColumn('actions', function ($charge) {
                return $charge->action_buttons;
            })
            ->make(true);
    }
}
