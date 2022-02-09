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
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;
        //Create the model using repository create method
         
        $this->repository->create($input);
        //return with successfull message
        return new RedirectResponse(route('biller.assetequipments.index'), ['flash_success' => 'Asset & Equipment Successfully Created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\productcategory\Productcategory $productcategory
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(Assetequipment $assetequipment, StoreAssetequipmentRequest $request)
    {
        //dd(0);
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
        //Input received from the request
        $input = $request->only(['name', 'account_type', 'account_id', 'condtition', 'vendor', 'location', 'serial', 'warranty', 'warranty_expiry_date', 'cost', 'qty' ,'purchase_date' ]);
        //Update the model using repository update method
        $this->repository->update($assetequipment, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.assetequipments.index'), ['flash_success' => 'Record  Successfully Updated'  . ' <a href="' . route('biller.assetequipments.show', [$assetequipment->id]) . '" class="ml-5 btn btn-outline-light round btn-min-width bg-blue"><span class="fa fa-eye" aria-hidden="true"></span> ' . trans('general.view') . '  </a> &nbsp; &nbsp;' . ' <a href="' . route('biller.assetequipments.create') . '" class="btn btn-outline-light round btn-min-width bg-purple"><span class="fa fa-plus-circle" aria-hidden="true"></span> ' . trans('general.create') . '  </a>&nbsp; &nbsp;' . ' <a href="' . route('biller.assetequipments.index') . '" class="btn btn-outline-blue round btn-min-width bg-amber"><span class="fa fa-list blue" aria-hidden="true"></span> <span class="blue">' . trans('general.list') . '</span> </a>']);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Assetequipment $assetequipment, StoreAssetequipmentRequest $request)
    {

        //dd($branch);
        //Calling the delete method on repository
        $this->repository->delete($assetequipment);
        //returning with successfull message
        return new RedirectResponse(route('biller.assetequipments.index'), ['flash_success' => 'Record Successfully Deleted']);
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

        //returning with successfull message
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

    public function product_search(Request $request, $bill_type)
    {
    
        if (!access()->allow('product_search')) return false;

        $q = $request->post('keyword');
        $w = $request->post('wid');
        $s = $request->post('serial_mode');
        if ($bill_type == 'label') $q = @$q['term'];
        $wq = compact('q', 'w');
            

         $equipments = Assetequipment::where('name', 'LIKE', '%' . $q . '%')
           -> orWhere('account_type', 'LIKE', '%' . $q . '%')
            -> orWhereHas('account', function ($query) use ($wq) {
                $query->where('holder', 'LIKE', '%' . $wq['q'] . '%');
                return $query;
            })->limit(6)->get();
            $output = array();

            foreach ($equipments as $row) {

                 if ($row->id > 0) {
         $output[] = array('name' => $row->name.' - '.$row->account_type.' - '.$row->account->holder, 'id' => $row['id'], 'decription' => $row['name'],  'account_id' => $row['account_id'], 'account_type' => $row['account_type'],  'cost' => $row['cost'] );
            }
                
            }

        

        if (count($output) > 0)

            return view('focus.products.partials.search')->withDetails($output);
    }

    

}
