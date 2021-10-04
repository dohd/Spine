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
namespace App\Http\Controllers\Focus\equipmentcategory;

use App\Models\equipmentcategory\EquipmentCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\equipmentcategory\CreateResponse;
use App\Http\Responses\Focus\equipmentcategory\EditResponse;
use App\Repositories\Focus\equipmentcategory\EquipmentCategoryRepository;
use App\Http\Requests\Focus\equipmentcategory\ManageEquipmentCategoryRequest;
use App\Http\Requests\Focus\equipmentcategory\StoreEquipmentCategoryRequest;


/**
 * ProductcategoriesController
 */
class EquipmentCategoriesController extends Controller
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
    public function __construct(EquipmentCategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageEquipmentCategoryRequest $request)
    {

        // $core = $this->branch->getForDataTable();
        // dd($core );

        return new ViewResponse('focus.equipmentcategories.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create(ManageEquipmentCategoryRequest $request)
    {

        return new CreateResponse('focus.equipmentcategories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(ManageEquipmentCategoryRequest $request)
    {
        $request->validate([
            'name' => 'required',
           
        ]);
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;
        //Create the model using repository create method
         
        $id = $this->repository->create($input);
        //return with successfull message
        return new RedirectResponse(route('biller.equipmentcategories.index'), ['flash_success' => 'Region  Successfully Created' . ' <a href="' . route('biller.equipmentcategories.show', [$id]) . '" class="ml-5 btn btn-outline-light round btn-min-width bg-blue"><span class="fa fa-eye" aria-hidden="true"></span> ' . trans('general.view') . '  </a> &nbsp; &nbsp;' . ' <a href="' . route('biller.equipmentcategories.create') . '" class="btn btn-outline-light round btn-min-width bg-purple"><span class="fa fa-plus-circle" aria-hidden="true"></span> ' . trans('general.create') . '  </a>&nbsp; &nbsp;' . ' <a href="' . route('biller.equipmentcategories.index') . '" class="btn btn-outline-blue round btn-min-width bg-amber"><span class="fa fa-list blue" aria-hidden="true"></span> <span class="blue">' . trans('general.list') . '</span> </a>']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\productcategory\Productcategory $productcategory
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(EquipmentCategory $equipmentcategory, ManageEquipmentCategoryRequest $request)
    {
        //dd(0);
        return new EditResponse($region);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */

     public function branch_load(Request $request)
    {
        $q = $request->get('id');
        $result = Branch::all()->where('rel_id', '=', $q);
        return json_encode($result);
    }


    public function update(ManageEquipmentCategoryRequest $request, EquipmentCategory $equipmentcategory)
    {
        $request->validate([
            'name' => 'required',
            'location' => 'required'
        ]);
        //Input received from the request
        $input = $request->only(['name', 'rel_id', 'location', 'contact_name', 'contact_phone']);
        //Update the model using repository update method
        $this->repository->update($branch, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.branches.index'), ['flash_success' => 'Branch  Successfully Updated'  . ' <a href="' . route('biller.branches.show', [$branch->id]) . '" class="ml-5 btn btn-outline-light round btn-min-width bg-blue"><span class="fa fa-eye" aria-hidden="true"></span> ' . trans('general.view') . '  </a> &nbsp; &nbsp;' . ' <a href="' . route('biller.branches.create') . '" class="btn btn-outline-light round btn-min-width bg-purple"><span class="fa fa-plus-circle" aria-hidden="true"></span> ' . trans('general.create') . '  </a>&nbsp; &nbsp;' . ' <a href="' . route('biller.branches.index') . '" class="btn btn-outline-blue round btn-min-width bg-amber"><span class="fa fa-list blue" aria-hidden="true"></span> <span class="blue">' . trans('general.list') . '</span> </a>']);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(EquipmentCategory $equipmentcategory, ManageEquipmentCategoryRequest $request)
    {

        //dd($branch);
        //Calling the delete method on repository
        $this->repository->delete($branch);
        //returning with successfull message
        return new RedirectResponse(route('biller.equipmentcategories.index'), ['flash_success' => 'Equipment Category Successfully Deleted']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(EquipmentCategory $equipmentcategory, ManageEquipmentCategoryRequest $request)
    {

        //returning with successfull message
        return new ViewResponse('focus.equipmentcategories.view', compact('branch'));
    }

}
