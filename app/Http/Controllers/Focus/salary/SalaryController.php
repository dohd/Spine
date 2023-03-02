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
namespace App\Http\Controllers\Focus\salary;

use App\Models\salary\Salary;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\salary\CreateResponse;
use App\Http\Responses\Focus\salary\EditResponse;
use App\Repositories\Focus\salary\SalaryRepository;
use App\Models\hrm\Hrm;


/**
 * salarysController
 */
class SalaryController extends Controller
{
    /**
     * variable to store the repository object
     * @var SalaryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param SalaryRepository $repository ;
     */
    public function __construct(SalaryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\salary\Request $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.salary.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreatesalaryRequestNamespace $request
     * @return \App\Http\Responses\Focus\salary\CreateResponse
     */
    public function create(Request $request)
    {
        return new CreateResponse('focus.salary.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoresalaryRequestNamespace $request
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
        return new RedirectResponse(route('biller.salary.index'), ['flash_success' => 'Salary Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\salary\salary $salary
     * @param EditsalaryRequestNamespace $request
     * @return \App\Http\Responses\Focus\salary\EditResponse
     */
    public function edit(Salary $salary, Request $request)
    {
        return new EditResponse($salary);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatesalaryRequestNamespace $request
     * @param App\Models\salary\salary $salary
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, Salary $salary)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($salary, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.salary.index'), ['flash_success' => trans('alerts.backend.salary.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletesalaryRequestNamespace $request
     * @param App\Models\salary\salary $salary
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Salary $salary, Request $request)
    {
        //Calling the delete method on repository
        $this->repository->delete($salary);
        //returning with successfull message
        return new RedirectResponse(route('biller.salary.index'), ['flash_success' => trans('alerts.backend.salary.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletesalaryRequestNamespace $request
     * @param App\Models\salary\salary $salary
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Salary $salary, Request $request)
    {
        //returning with successfull message
        return new ViewResponse('focus.salary.view', compact('salary'));
    }
    public function select(Request $request)
    {
        $q = $request->keyword;
        $users = Hrm::where('first_name', 'LIKE', '%'.$q.'%')
            ->orWhere('email', 'LIKE', '%'.$q.'')
            ->limit(6)->get(['id', 'first_name', 'email']);

        return response()->json($users);
    }

    public function renew_contract(Request $request)
    {
        //dd($request->all());
        $renew_contract = Salary::find($request->id);
        $renew_contract->basic_pay = $request->basic_pay;
        $renew_contract->house_allowance = $request->house_allowance;
        $renew_contract->transport_allowance = $request->transport_allowance;
        $renew_contract->directors_fee = $request->directors_fee;
        $renew_contract->contract_type = $request->contract_type;
        $start_date = date_for_database($request->start_date);
        $renew_contract->start_date = $start_date;
        $renew_contract->duration = $request->duration;
        $renew_contract->update();
        return new RedirectResponse(route('biller.salary.index'), ['flash_success' => 'Contract Renewed Successfully!!']);
    }
    public function terminate_contract(Request $request)
    {
        //dd($request->all());
        $terminate_contract = Salary::find($request->id);
        $terminate_date = date_for_database($request->terminate_date);
        if($terminate_contract->status == 'ongoing'){
            $terminate_contract->status = $request->status;
            $terminate_contract->update();
            return new RedirectResponse(route('biller.salary.index'), ['flash_success' => 'Contract Terminated Successfully!!']);
        }
        
        return new RedirectResponse(route('biller.salary.index'), ['flash_error' => 'Contract Cannot be Terminated!!']);
    }
}
