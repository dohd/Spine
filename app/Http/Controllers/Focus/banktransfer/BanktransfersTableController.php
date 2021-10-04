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

use App\Http\Requests\Focus\general\ManageCompanyRequest;
use Carbon\Carbon;
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
        //
        $core = $this->banktransfer->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('account_id', function ($banktransfer) {
                return $banktransfer->account->holder;
            })
             ->addColumn('transaction_date', function ($charge) {
                return dateFormat($banktransfer->transaction_date);
            })
              ->addColumn('debit', function ($banktransfer) {
                return amountFormat($banktransfer->debit);
            })
           ->addColumn('credit', function ($banktransfer) {
                return amountFormat($banktransfer->credit);
            })

            ->addColumn('created_at', function ($banktransfer) {
                return Carbon::parse($banktransfer->created_at)->toDateString();
            })
            ->addColumn('actions', function ($banktransfer) {
                if($banktransfer->second_trans==0){
                  return $banktransfer->action_buttons;  
                }else{
                       return '';  
                }

               
                
            })
            ->make(true);
    }
}
