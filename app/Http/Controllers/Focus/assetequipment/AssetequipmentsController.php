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

namespace App\Http\Controllers\Focus\assetequipment;

use App\Models\assetequipment\Assetequipment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\assetequipment\CreateResponse;
use App\Http\Responses\Focus\assetequipment\EditResponse;
use App\Repositories\Focus\assetequipment\AssetequipmentRepository;
use App\Http\Requests\Focus\assetequipment\ManageAssetequipmentRequest;
use App\Http\Requests\Focus\assetequipment\StoreAssetequipmentRequest;
use App\Models\account\Account;

/**
 * ProductcategoriesController
 */
class AssetequipmentsController extends Controller
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
    public function __construct(AssetequipmentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageAssetequipmentRequest $request)
    {

        // $core = $this->branch->getForDataTable();
        // dd($core );

        return new ViewResponse('focus.assetequipments.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create()
    {
        return new CreateResponse('focus.assetequipments.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StoreAssetequipmentRequest $request)
    {
        $request->validate([
            'name' => 'required',
            'account_id' => 'required',
            'account_type' => 'required'
        ]);
        // extract request input
        $input = $request->except(['_token', 'ins']);

        $input['ins'] = auth()->user()->ins;

        $this->repository->create($input);

        return new RedirectResponse(route('biller.assetequipments.index'), ['flash_success' => 'Asset Equipment Successfully Created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\productcategory\Productcategory $productcategory
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(Assetequipment $assetequipment)
    {
        return new EditResponse($assetequipment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StoreAssetequipmentRequest $request, Assetequipment $assetequipment)
    {
        $request->validate([
            'name' => 'required',
            'account_id' => 'required',
            'account_type' => 'required'
        ]);
        // extract request input
        $input = $request->except(['_token', 'ins']);

        $this->repository->update($assetequipment, $input);

        return new RedirectResponse(route('biller.assetequipments.index'), ['flash_success' => 'Asset Equipment Successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Assetequipment $assetequipment)
    {
        $this->repository->delete($assetequipment);

        return new RedirectResponse(route('biller.assetequipments.index'), ['flash_success' => 'Asset Equipment Successfully Deleted']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Assetequipment $assetequipment, ManageAssetequipmentRequest $request)
    {
        return new ViewResponse('focus.assetequipments.view', compact('assetequipment'));
    }

    /**
     * Load Ledger Account Type
     */
    public function ledger_load(Request $request)
    {
        $accounts = Account::where('account_type', $request->account_type)->get();

        return response()->json($accounts);
    }

    /**
     * Search asset and equipments 
     */
    public function product_search(Request $request)
    {
        if (!access()->allow('product_search')) return false;

        $q = $request->post('keyword');

        $equipments = Assetequipment::where('name', 'LIKE', '%'.$q.'%')
            ->orWhere('account_type', 'LIKE', '%'.$q.'%')
            ->orWhereHas('account', function ($query) use ($q) {
                $query->where('holder', 'LIKE', '%'.$q.'%');
            })
            ->limit(6)->get(['id', 'name', 'account_id', 'account_type', 'cost']);

        return response()->json($equipments);
    }
}
