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

namespace App\Http\Controllers\Focus\equipment;

use App\Models\equipment\Equipment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\equipment\CreateResponse;
use App\Http\Responses\Focus\equipment\EditResponse;
use App\Repositories\Focus\equipment\EquipmentRepository;
use App\Http\Requests\Focus\equipment\ManageEquipmentRequest;
use App\Http\Requests\Focus\equipment\StoreEquipmentRequest;


/**
 * ProductcategoriesController
 */
class EquipmentsController extends Controller
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
    public function __construct(EquipmentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageEquipmentRequest $request)
    {

        // $core = $this->branch->getForDataTable();
        // dd($core );

        return new ViewResponse('focus.equipments.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create(StoreEquipmentRequest $request)
    {
        return new CreateResponse('focus.equipments.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StoreEquipmentRequest $request)
    {
        $request->validate([
            'make_type' => 'required',
            'location' => 'required',
            'unit_type' => 'required',
        ]);
        // extract request input
        $input = $request->except(['_token', 'ins']);

        $input['ins'] = auth()->user()->ins;

        $this->repository->create($input);

        return new RedirectResponse(route('biller.equipments.index'), ['flash_success' => 'Equipment created successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\productcategory\Productcategory $productcategory
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(Equipment $equipment)
    {
        return new EditResponse($equipment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */

    public function equipment_load(Request $request)
    {

        $q = $request->get('id');
        if ($q == 1) {
            $result = Equipment::all()->where('rel_id', '=', $q);
            return json_encode($result);
        } else {
            $result = "";
            return json_encode($result);
        }
    }


    public function update(StoreEquipmentRequest $request, Equipment $equipment)
    {
        // extract request input
        $input = $request->except(['_token', 'ins']);

        $this->repository->update($equipment, $input);

        return new RedirectResponse(route('biller.equipments.index'), ['flash_success' => 'Equipment  Successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Equipment $equipment)
    {

        $this->repository->delete($equipment);

        return new RedirectResponse(route('biller.equipments.index'), ['flash_success' => 'Equipment Deleted Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Equipment $equipment)
    {
        return new ViewResponse('focus.equipments.view', compact('equipment'));
    }

    /**
     * Fetch customer equipments
     */
    public function equipment_search(Request $request, $id)
    {
        $key_word = $request->post('keyword');
        $equipments = Equipment::where('unique_id', 'LIKE', '%' . $key_word. '%')
            ->where('customer_id', $id)
            ->limit(6)
            ->with(['customer'])
            ->get();

        // transform equipemts
        $output = array();
        foreach ($equipments as $row) {
            $output[] = array(
                'name' => $row->unique_id,
                'customer' => $row->customer->company, 
                'unit_type' => $row->unit_type, 
                'make_type' => $row->make_type, 
                'id' => $row->id, 
                'capacity' => $row->capacity, 
                'location' => $row->location, 
                'next_maintenance_date' => $row->next_maintenance_date, 
                'last_maint_date' => $row->last_maint_date,
            );
        }
        
        return view('focus.djcs.partials.search')->withDetails($output);
    }
}
