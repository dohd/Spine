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

namespace App\Http\Controllers\Focus\tenant;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\Access\User\User;
use App\Models\hrm\Hrm;
use App\Models\tenant\Tenant;
use App\Repositories\Focus\tenant\TenantRepository;

/**
 * ProductcategoriesController
 */
class TenantsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $repository ;
     */
    public function __construct(TenantRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(Request $request)
    {
        return new ViewResponse('focus.tenants.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create()
    {
        return view('focus.tenants.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'cname' => 'required',
            'address' => 'required',
            'postbox' => 'required',
            'cemail' => 'required',
            'phone' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'password' => 'required',
        ]);

        try {
            $this->repository->create($request->except(['_token']));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Tenant!', $th);
        }
        
        return new RedirectResponse(route('biller.tenants.index'), ['flash_success' => 'Tenant  Successfully Created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\productcategory\Productcategory $productcategory
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(Tenant $tenant, Request $request)
    {
        $user = User::where('ins', $tenant->id)
        ->where('created_at', $tenant->created_at)
        ->first();
        
        return view('focus.tenants.edit', compact('tenant', 'user'));
    }

    /**
     * Update Resource in Storage
     * 
     */
    public function update(Request $request, Tenant $tenant)
    {
        $request->validate([
            'cname' => 'required',
            'address' => 'required',
            'postbox' => 'required',
            'cemail' => 'required',
            'phone' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
        ]);

        try {
            $this->repository->update($tenant, $request->except(['_token']));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Tenant!', $th);
        }
        
        return new RedirectResponse(route('biller.tenants.index'), ['flash_success' => 'Tenant  Successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Tenant $tenant)
    {
        try {
            $this->repository->delete($tenant);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Tenant!', $th);
        }

        return new RedirectResponse(route('biller.tenants.index'), ['flash_success' => 'Tenant  Successfully Deleted']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Tenant $tenant, Request $request)
    {
        return new ViewResponse('focus.tenants.view', compact('tenant'));
    }
}
