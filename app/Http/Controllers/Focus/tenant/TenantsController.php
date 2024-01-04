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
use App\Models\customer\Customer;
use App\Models\tenant\Tenant;
use App\Models\tenant_service\TenantService;
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
        $tenant_services = TenantService::get();

        return view('focus.tenants.create', compact('tenant_services'));
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
            'country' => 'required',
            'postbox' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'user_email' => 'required',
            'password' => 'required',
            'package_id' => 'required',
            'maintenance_cost' => 'required',
        ]);

        try {
            $this->repository->create($request->except(['_token']));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Account!', $th);
        }
        
        return new RedirectResponse(route('biller.tenants.index'), ['flash_success' => 'Account  Successfully Created']);
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
        $user = User::where(['ins' => $tenant->id, 'created_at' => $tenant->created_at])->first();
        $tenant_services = TenantService::get();

        return view('focus.tenants.edit', compact('tenant', 'user', 'tenant_services'));
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
            'country' => 'required',
            'postbox' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'user_email' => 'required',
            'package_id' => 'required',
            'maintenance_cost' => 'required',
        ]);

        try {
            $this->repository->update($tenant, $request->except(['_token']));
        } catch (\Throwable $th) { dd($th);
            return errorHandler('Error Updating Account!', $th);
        }
        
        return new RedirectResponse(route('biller.tenants.index'), ['flash_success' => 'Account  Successfully Updated']);
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
            return errorHandler('Error Deleting Account!', $th);
        }

        return new RedirectResponse(route('biller.tenants.index'), ['flash_success' => 'Account  Successfully Deleted']);
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

    /**
     * Select Tenants
     * 
     */
    public function select(Request $request)
    {
        $q = $request->input('q');
        $tenants = Tenant::where('cname', 'LIKE', '%' . $q . '%')->limit(10)->get();
        return response()->json($tenants);
    }

    /**
     * Select Business Customers
     */
    public function customers(Request $request)
    {
        $q = $request->input('q');
        $customers = Customer::where('company', 'LIKE', '%' . $q . '%')
        ->limit(10)->get();

        return response()->json($customers);
    }
}
