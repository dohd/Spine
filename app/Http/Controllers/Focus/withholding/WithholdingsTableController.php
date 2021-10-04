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
namespace App\Http\Controllers\Focus\withholding;

use App\Http\Requests\Focus\general\ManageCompanyRequest;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\withholding\WithholdingRepository;
use App\Http\Requests\Focus\withholding\ManageWithholdingRequest;

/**
 * Class BanksTableController.
 */
class WithholdingsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var BankRepository
     */
    protected $withholding;

    /**
     * contructor to initialize repository object
     * @param BankRepository $withholding ;
     */
    public function __construct(WithholdingRepository $withholding)
    {
        $this->withholding = $withholding;
    }

    /**
     * This method return the data of the model
     * @param ManageBankRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageWithholdingRequest $request)
    {
        //
        $core = $this->withholding->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('account_id', function ($withholding) {
                return $withholding->customer->company;
            })
             ->addColumn('transaction_date', function ($withholding) {
                return dateFormat($withholding->transaction_date);
            })
              ->addColumn('debit', function ($withholding) {
                return amountFormat($withholding->debit);
            })
           ->addColumn('credit', function ($withholding) {
                return amountFormat($withholding->credit);
            })

            ->addColumn('created_at', function ($withholding) {
                return Carbon::parse($withholding->created_at)->toDateString();
            })
            ->addColumn('actions', function ($withholding) {
                return $withholding->action_buttons;
            })
            ->make(true);
    }
}
