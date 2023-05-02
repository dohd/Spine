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

namespace App\Http\Controllers\Focus\banktransfer;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\banktransfer\BanktransferRepository;
use App\Http\Requests\Focus\banktransfer\ManageBanktransferRequest;

/**
 * Class BanksTableController.
 */
class BanktransfersTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var BankRepository
     */
    protected $banktransfer;

    /**
     * contructor to initialize repository object
     * @param BankRepository $banktransfer ;
     */
    public function __construct(BanktransferRepository $banktransfer)
    {
        $this->banktransfer = $banktransfer;
    }

    /**
     * This method return the data of the model
     * @param ManageBankRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageBanktransferRequest $request)
    {
        $core = $this->banktransfer->getForDataTable();
        $core = $core->map(function($v, $i) use($core) {
            if ($i && $i % 2 > 0) {
                $credit_tr = $core[$i - 1];
                $credit_account_holder = $credit_tr->account->holder;
                $debit_account_holder = $v->account->holder;
                $holder = $credit_account_holder . " : " . $debit_account_holder;
                $v['holder'] = $holder;
            }
            return $v;
        })->filter(fn($v) => $v['debit'] > 0);

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('account', function ($banktransfer) {
                printlog($banktransfer);
                return $banktransfer->holder;
            })
            ->addColumn('debit', function ($banktransfer) {
                return amountFormat($banktransfer->debit);
            })
            ->addColumn('transaction_date', function ($banktransfer) {
                return dateFormat($banktransfer->tr_date);
            })
            ->addColumn('actions', function ($banktransfer) {
                return $banktransfer->action_buttons;
            })
            ->make(true);
    }
}
