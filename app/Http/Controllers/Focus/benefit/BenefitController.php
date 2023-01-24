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
namespace App\Http\Controllers\Focus\benefit;

use App\Models\benefit\Benefit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\benefit\CreateResponse;
use App\Http\Responses\Focus\benefit\EditResponse;
use App\Repositories\Focus\benefit\BenefitRepository;
use App\Models\department\Department;


/**
 * benefitsController
 */
class BenefitController extends Controller
{
    /**
     * variable to store the repository object
     * @var benefitRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param benefitRepository $repository ;
     */
    public function __construct(BenefitRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\benefit\Request $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.benefit.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreatebenefitRequestNamespace $request
     * @return \App\Http\Responses\Focus\benefit\CreateResponse
     */
    public function create(Request $request)
    {
        return new CreateResponse('focus.benefit.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorebenefitRequestNamespace $request
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
        return new RedirectResponse(route('biller.benefits.index'), ['flash_success' => 'Job Title Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\benefit\benefit $benefit
     * @param EditbenefitRequestNamespace $request
     * @return \App\Http\Responses\Focus\benefit\EditResponse
     */
    public function edit(Benefit $benefit, Request $request)
    {
        return new EditResponse($benefit);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatebenefitRequestNamespace $request
     * @param App\Models\benefit\benefit $benefit
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, benefit $benefit)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($benefit, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.benefits.index'), ['flash_success' => trans('alerts.backend.benefits.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletebenefitRequestNamespace $request
     * @param App\Models\benefit\benefit $benefit
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Benefit $benefit, Request $request)
    {
        //Calling the delete method on repository
        $this->repository->delete($benefit);
        //returning with successfull message
        return new RedirectResponse(route('biller.benefits.index'), ['flash_success' => trans('alerts.backend.benefits.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletebenefitRequestNamespace $request
     * @param App\Models\benefit\benefit $benefit
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Benefit $benefit, Request $request)
    {
        //returning with successfull message
        return new ViewResponse('focus.benefit.view', compact('benefit'));
    }
    public function select(Request $request)
    {
        $q = $request->keyword;
        $users = Department::where('name', 'LIKE', '%'.$q.'%')
            ->limit(6)->get(['id', 'name']);

        return response()->json($users);
    }

}
