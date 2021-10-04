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
use App\Models\charge\Charge;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\charge\CreateResponse;
use App\Http\Responses\Focus\charge\EditResponse;
use App\Repositories\Focus\charge\ChargeRepository;
use App\Http\Requests\Focus\charge\ManageChargeRequest;
use App\Http\Requests\Focus\charge\StoreChargeRequest;


/**
 * BanksController
 */
class ChargesController extends Controller
{
    /**
     * variable to store the repository object
     * @var BankRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param BankRepository $repository ;
     */
    public function __construct(ChargeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\bank\ManageBankRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageChargeRequest $request)
    {
       $words = array();
         return new ViewResponse('focus.charges.index', compact('words'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateBankRequestNamespace $request
     * @return \App\Http\Responses\Focus\bank\CreateResponse
     */
    public function create(StoreChargeRequest $request)
    {
        return new CreateResponse('focus.charges.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreBankRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StoreChargeRequest $request)
    {

        $request->validate([
            'amount' => 'required',
            'account_id' => 'required',
            'expense_account_id' => 'required'
        ]);


      $credit = $request->only(['tid', 'account_id', 'method', 'refer_no', 'note']);
      $debit= $request->only(['tid', 'method', 'refer_no', 'note']);



      $credit['ins'] = auth()->user()->ins;
      $credit['user_id'] = auth()->user()->id;
      $credit['credit'] = numberClean($request->input('amount'));
      $credit['transaction_date'] = date_for_database($request->input('transaction_date'));

      $debit['ins'] = auth()->user()->ins;
      $debit['user_id'] = auth()->user()->id;
      $debit['account_id'] = numberClean($request->input('expense_account_id'));
      $debit['debit'] = numberClean($request->input('amount'));
      $debit['transaction_date'] = date_for_database($request->input('transaction_date'));


      $result = $this->repository->create(compact('credit','debit'));

       return new RedirectResponse(route('biller.charges.index'), ['flash_success' => trans('alerts.backend.banks.created')]);

     
 

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\bank\Bank $bank
     * @param EditBankRequestNamespace $request
     * @return \App\Http\Responses\Focus\bank\EditResponse
     */
    public function edit(Charge $charge, StoreChargeRequest $request)
    {
        return new EditResponse($charge);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StoreChargeRequest $request, Charge $charge)
    {
        $request->validate([
            'name' => 'required|string',
            'bank' => 'required|string',
            'number' => 'required'
        ]);
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($charge, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.charges.index'), ['flash_success' => trans('alerts.backend.charges.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Charge $charge, StoreChargeRequest  $request)
    {
        //Calling the delete method on repository
        $this->repository->delete($charge);
        //returning with successfull message
        return new RedirectResponse(route('biller.charges.index'), ['flash_success' => trans('alerts.backend.charges.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Charge $charge, ManageChargeRequest $request)
    {

        //returning with successfull message
        return new ViewResponse('focus.charges.view', compact('charge'));
    }

}
