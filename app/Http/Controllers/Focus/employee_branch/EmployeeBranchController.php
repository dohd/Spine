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
namespace App\Http\Controllers\Focus\employee_branch;

use App\Models\employee_branch\EmployeeBranch;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\employee_branch\CreateResponse;
use App\Http\Responses\Focus\employee_branch\EditResponse;
use App\Repositories\Focus\employee_branch\EmployeeBranchRepository;



/**
 * employee_branchsController
 */
class EmployeeBranchController extends Controller
{
    /**
     * variable to store the repository object
     * @var employee_branchRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param employee_branchRepository $repository ;
     */
    public function __construct(EmployeeBranchRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\employee_branch\Manageemployee_branchRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(Request $request)
    {
        return new ViewResponse('focus.employee_branch.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Createemployee_branchRequestNamespace $request
     * @return \App\Http\Responses\Focus\employee_branch\CreateResponse
     */
    public function create(Request $request)
    {
        return new CreateResponse('focus.employee_branch.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Storeemployee_branchRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;
        //Create the model using repository create method
        $this->repository->create($input);
        //return with successfull message
        return new RedirectResponse(route('biller.employee_branch.index'), ['flash_success' => 'Employee Brach Created Successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\employee_branch\employee_branch $employee_branch
     * @param Editemployee_branchRequestNamespace $request
     * @return \App\Http\Responses\Focus\employee_branch\EditResponse
     */
    public function edit(EmployeeBranch $employee_branch, Request $request)
    {
        return new EditResponse($employee_branch);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Updateemployee_branchRequestNamespace $request
     * @param App\Models\employee_branch\employee_branch $employee_branch
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, EmployeeBranch $employee_branch)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($employee_branch, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.employee_branch.index'), ['flash_success' => 'Employee Branch Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Deleteemployee_branchRequestNamespace $request
     * @param App\Models\employee_branch\employee_branch $employee_branch
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(EmployeeBranch $employee_branch, Request $request)
    {
        //Calling the delete method on repository
        $this->repository->delete($employee_branch);
        //returning with successfull message
        return new RedirectResponse(route('biller.employee_branch.index'), ['flash_success' => 'Employee Branch Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Deleteemployee_branchRequestNamespace $request
     * @param App\Models\employee_branch\employee_branch $employee_branch
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(EmployeeBranch $employee_branch, Request $request)
    {

        //returning with successfull message
        return new ViewResponse('focus.employee_branch.view', compact('employee_branch'));
    }

}
