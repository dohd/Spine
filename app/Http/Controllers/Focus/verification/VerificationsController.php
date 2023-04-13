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

namespace App\Http\Controllers\Focus\verification;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\verification\Verification;
use App\Repositories\Focus\verification\VerificationRepository;
use Illuminate\Http\Request;

class VerificationsController extends Controller
{
    /**
     * variable to store the repository object
     * @var VerificationRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param VerificationRepository $repository ;
     */
    public function __construct(VerificationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('focus.verifications.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('focus.verifications.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->repository->create($request->except('_token'));

        return new RedirectResponse(route('biller.verifications.index'), ['flash_success' => 'Verification Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Verification $verification
     * @return \Illuminate\Http\Response
     */
    public function edit(Verification $verification)
    {
        return view('focus.verifications.edit', compact('verification'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Verification $verification
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Verification $verification)
    {
        $this->repository->update($verification, $request->except('_token'));

        return new RedirectResponse(route('biller.verifications.index'), ['flash_success' => 'Verification Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Verification $verification
     * @return \Illuminate\Http\Response
     */
    public function destroy(Verification $verification)
    {
        $this->repository->delete($verification);

        return new RedirectResponse(route('biller.verifications.index'), ['flash_success' => 'Verification Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  Verification $verification
     * @return \Illuminate\Http\Response
     */
    public function show(Verification $verification)
    {
        return view('focus.verifications.view', compact('verification'));
    }

    /**
     * Display Verification Quotes Page
     * 
     */
    public function quote_index()
    {
        return view('focus.verifications.quote_index');
    }
}