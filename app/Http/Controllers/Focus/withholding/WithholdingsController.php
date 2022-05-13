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

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\withholding\CreateResponse;
use App\Http\Responses\Focus\withholding\EditResponse;
use App\Repositories\Focus\withholding\WithholdingRepository;
use App\Http\Requests\Focus\withholding\ManageWithholdingRequest;
use App\Http\Requests\Focus\withholding\StoreWithholdingRequest;


/**
 * BanksController
 */
class WithholdingsController extends Controller
{
    /**
     * variable to store the repository object
     * @var WithholdingRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param WithholdingRepository $repository ;
     */
    public function __construct(WithholdingRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\bank\ManageBankRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageWithholdingRequest $request)
    {
       $words = array();
         return new ViewResponse('focus.withholdings.index', compact('words'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateBankRequestNamespace $request
     * @return \App\Http\Responses\Focus\bank\CreateResponse
     */
    public function create(StoreWithholdingRequest $request)
    {
        return new CreateResponse('focus.withholdings.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreBankRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StoreWithholdingRequest $request)
    {
        // extract request fields
        $data = $request->only([
            'customer_id', 'tid', 'date', 'due_date', 'certificate', 'amount', 'amount_ttl', 'deposit_ttl', 'doc_ref'
        ]);
        $data_items = $request->only(['invoice_id', 'paid']);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function ($v) { return $v['paid']; });

        $this->repository->create(compact('data', 'data_items'));

        return trans('alerts.backend.withholdings.created');

       return new RedirectResponse(route('biller.withholdings.index'), ['flash_success' => trans('alerts.backend.withholdings.created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\bank\Bank $bank
     * @param EditBankRequestNamespace $request
     * @return \App\Http\Responses\Focus\bank\EditResponse
     */
    public function edit(Withholding $withholding, StoreWithholdingRequest $request)
    {
        return new EditResponse($withholding);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StoreWithholdingRequest $request, Withholding $withholding)
    {
        $request->validate([
            'name' => 'required|string',
            'bank' => 'required|string',
            'number' => 'required'
        ]);
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($witholding, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.withholdings.index'), ['flash_success' => trans('alerts.backend.withholdings.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Withholding $withholding, StoreWithholdingRequest  $request)
    {
        //Calling the delete method on repository
        $this->repository->delete($withholding);
        //returning with successfull message
        return new RedirectResponse(route('biller.withholdings.index'), ['flash_success' => trans('alerts.backend.witholdings.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Withholding $withholding, ManageWithholdingRequest $request)
    {

        //returning with successfull message
        return new ViewResponse('focus.withholdings.view', compact('charge'));
    }

}
