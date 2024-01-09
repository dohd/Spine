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

namespace App\Http\Controllers\Focus\client_vendor;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\client_vendor\ClientVendor;
use App\Repositories\Focus\client_vendor\ClientVendorRepository;

class ClientVendorsController extends Controller
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
    public function __construct(ClientVendorRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        return new ViewResponse('focus.client_vendors.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create()
    {
        return view('focus.client_vendors.create');
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
            'company' => 'required',
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'address' => 'required',
            'postbox' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'password' => 'required',
        ]);

        try {
            $this->repository->create($request->except(['_token']));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Vendor!', $th);
        }
        
        return new RedirectResponse(route('biller.client_vendors.index'), ['flash_success' => 'Vendor  Successfully Created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\productcategory\Productcategory $productcategory
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(ClientVendor $client_vendor, Request $request)
    {  
        $user = $client_vendor->user;
        return view('focus.client_vendors.edit', compact('client_vendor', 'user'));
    }

    /**
     * Update Resource In Storage
     * 
     */
    public function update(Request $request, ClientVendor $client_vendor)
    {
        $request->validate([
            'company' => 'required',
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'address' => 'required',
            'postbox' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
        ]);
        
        try {
            $this->repository->update($client_vendor, $request->except('_token'));
        } catch (\Throwable $th) { dd($th);
            return errorHandler('Error Updating Vendor!', $th);
        }
        
        return new RedirectResponse(route('biller.client_vendors.index'), ['flash_success' => 'Vendor  Successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(ClientVendor $client_vendor)
    {
        try {
            $this->repository->delete($client_vendor);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Vendor!', $th);
        }

        return new RedirectResponse(route('biller.client_vendors.index'), ['flash_error' => 'Vendor Successfully Deleted']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(ClientVendor $client_vendor, Request $request)
    {
        return new ViewResponse('focus.client_vendors.view', compact('client_vendor'));
    }
}
