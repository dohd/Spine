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
namespace App\Http\Controllers\Focus\overtimepay;

use App\Models\overtimepay\OvertimePay;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\overtimepay\CreateResponse;
use App\Http\Responses\Focus\overtimepay\EditResponse;
use App\Repositories\Focus\overtimepay\OvertimePayRepository;
use App\Models\department\Department;


/**
 * overtimepaysController
 */
class OvertimePayController extends Controller
{
    /**
     * variable to store the repository object
     * @var overtimepayRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param overtimepayRepository $repository ;
     */
    public function __construct(OvertimePayRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\overtimepay\Request $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.overtimepay.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateovertimepayRequestNamespace $request
     * @return \App\Http\Responses\Focus\overtimeview
     */
    public function create(Request $request)
    {
        return new CreateResponse('focus.overtimepay.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreovertimepayRequestNamespace $request
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
        return new RedirectResponse(route('biller.overtimepay.index'), ['flash_success' => 'OverTimePay Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\overtimepay\overtimepay $overtimepay
     * @param EditovertimepayRequestNamespace $request
     * @return \App\Http\Responses\Focus\overtimepay\EditResponse
     */
    public function edit(OvertimePay $overtimepay, Request $request)
    {
        return new EditResponse($overtimepay);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateovertimepayRequestNamespace $request
     * @param App\Models\overtimepay\overtimepay $overtimepay
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, OvertimePay $overtimepay)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($overtimepay, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.overtimepay.index'), ['flash_success' => 'OvertimePay Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteovertimepayRequestNamespace $request
     * @param App\Models\overtimepay\overtimepay $overtimepay
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(OvertimePay $overtimepay, Request $request)
    {
        //Calling the delete method on repository
        $this->repository->delete($overtimepay);
        //returning with successfull message
        return new RedirectResponse(route('biller.overtimepay.index'), ['flash_success' => 'OverTimePay Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteovertimepayRequestNamespace $request
     * @param App\Models\overtimepay\overtimepay $overtimepay
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(OvertimePay $overtimepay, Request $request)
    {
        //returning with successfull message
        return new ViewResponse('focus.overtimepay.view', compact('overtimepay'));
    }
    public function select(Request $request)
    {
        $q = $request->keyword;
        $users = Department::where('name', 'LIKE', '%'.$q.'%')
            ->limit(6)->get(['id', 'name']);

        return response()->json($users);
    }

}
