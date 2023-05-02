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
namespace App\Http\Controllers\Focus\overtimerate;

use App\Models\overtimerate\OvertimeRate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\overtimerate\CreateResponse;
use App\Http\Responses\Focus\overtimerate\EditResponse;
use App\Repositories\Focus\overtimerate\OvertimeRateRepository;
use App\Models\department\Department;


/**
 * overtimeratesController
 */
class OvertimeRateController extends Controller
{
    /**
     * variable to store the repository object
     * @var overtimerateRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param overtimerateRepository $repository ;
     */
    public function __construct(OvertimeRateRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\overtimerate\Request $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.overtimerate.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateovertimerateRequestNamespace $request
     * @return \App\Http\Responses\Focus\overtimerate\CreateResponse
     */
    public function create(Request $request)
    {
        return new CreateResponse('focus.overtimerate.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreovertimerateRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        // dd($request->all());
        //Input received from the request
        $input = $request->except(['_token']);
        $input['ins'] = auth()->user()->ins;
        $input['user_id'] = auth()->user()->id;
        //Create the model using repository create method
        $this->repository->create($input);
        //return with successfull messagetrans
        return new RedirectResponse(route('biller.overtimerates.index'), ['flash_success' => 'Job Title Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\overtimerate\overtimerate $overtimerate
     * @param EditovertimerateRequestNamespace $request
     * @return \App\Http\Responses\Focus\overtimerate\EditResponse
     */
    public function edit(OvertimeRate $overtimerate, Request $request)
    {
        return new EditResponse($overtimerate);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateovertimerateRequestNamespace $request
     * @param App\Models\overtimerate\overtimerate $overtimerate
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, OvertimeRate $overtimerate)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($overtimerate, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.overtimerates.index'), ['flash_success' => trans('alerts.backend.overtimerates.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteovertimerateRequestNamespace $request
     * @param App\Models\overtimerate\overtimerate $overtimerate
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(OvertimeRate $overtimerate, Request $request)
    {
        //Calling the delete method on repository
        $this->repository->delete($overtimerate);
        //returning with successfull message
        return new RedirectResponse(route('biller.overtimerates.index'), ['flash_success' => trans('alerts.backend.overtimerates.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteovertimerateRequestNamespace $request
     * @param App\Models\overtimerate\overtimerate $overtimerate
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(OvertimeRate $overtimerate, Request $request)
    {
        //returning with successfull message
        return new ViewResponse('focus.overtimerate.view', compact('overtimerate'));
    }
    public function select(Request $request)
    {
        $q = $request->keyword;
        $users = Department::where('name', 'LIKE', '%'.$q.'%')
            ->limit(6)->get(['id', 'name']);

        return response()->json($users);
    }

}
