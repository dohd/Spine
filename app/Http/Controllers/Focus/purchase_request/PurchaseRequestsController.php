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

namespace App\Http\Controllers\Focus\purchase_request;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\Access\User\User;
use App\Models\purchase_request\PurchaseRequest;
use App\Repositories\Focus\purchase_request\PurchaseRequestRepository;
use Illuminate\Http\Request;

class PurchaseRequestsController extends Controller
{
    /**
     * variable to store the repository object
     * @var PurchaseRequestRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param PurchaseRequestRepository $repository ;
     */
    public function __construct(PurchaseRequestRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.purchase_requests.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tid = PurchaseRequest::where('ins', auth()->user()->ins)->max('tid');
        $users = User::all();

        return view('focus.purchase_requests.create', compact('users', 'tid'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->repository->create($request->except('_token', 'files'));

        return new RedirectResponse(route('biller.purchase_requests.index'), ['flash_success' => 'Purchase Requisition Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  PurchaseRequest $purchase_request
     * @return \Illuminate\Http\Response
     */
    public function edit(PurchaseRequest $purchase_request)
    {
        $users = User::all();

        return view('focus.purchase_requests.edit', compact('purchase_request', 'users'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  PurchaseRequest $purchase_request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PurchaseRequest $purchase_request)
    {
        $this->repository->update($purchase_request, $request->except('_token'));

        return new RedirectResponse(route('biller.purchase_requests.index'), ['flash_success' => 'Purchase Requisition Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  PurchaseRequest $purchase_request
     * @return \Illuminate\Http\Response
     */
    public function destroy(PurchaseRequest $purchase_request)
    {
        $this->repository->delete($purchase_request);

        return new RedirectResponse(route('biller.purchase_requests.index'), ['flash_success' => 'Purchase Requisition Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  PurchaseRequest $purchase_request
     * @return \Illuminate\Http\Response
     */
    public function show(PurchaseRequest $purchase_request)
    {
        return view('focus.purchase_requests.view', compact('purchase_request'));
    }
}
