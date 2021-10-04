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
            'manufacturer' => 'required',
            'location' => 'required',
            'unit_type' => 'required',
            
        ]);
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;
        //Create the model using repository create method
         
        $id = $this->repository->create($input);
        //return with successfull message
        return new RedirectResponse(route('biller.equipments.index'), ['flash_success' => 'Odu  Successfully Created' . ' <a href="' . route('biller.equipments.show', [$id]) . '" class="ml-5 btn btn-outline-light round btn-min-width bg-blue"><span class="fa fa-eye" aria-hidden="true"></span> ' . trans('general.view') . '  </a> &nbsp; &nbsp;' . ' <a href="' . route('biller.equipments.create') . '" class="btn btn-outline-light round btn-min-width bg-purple"><span class="fa fa-plus-circle" aria-hidden="true"></span> ' . trans('general.create') . '  </a>&nbsp; &nbsp;' . ' <a href="' . route('biller.equipments.index') . '" class="btn btn-outline-blue round btn-min-width bg-amber"><span class="fa fa-list blue" aria-hidden="true"></span> <span class="blue">' . trans('general.list') . '</span> </a>']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\productcategory\Productcategory $productcategory
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(Equipment $equipment, StoreEquipmentRequest $request)
    {
        //dd($equipment);
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
        if($q==1){
       $result = Equipment::all()->where('rel_id', '=', $q);
        return json_encode($result);

        }else{
            $result="";
             return json_encode($result);
        }
      
    }


    public function update(StoreEquipmentRequest $request, Equipment $equipment)
    {
        $request->validate([
            'name' => 'required',
            'location' => 'required'
        ]);
        //Input received from the request
        $input = $request->only(['name', 'rel_id', 'location', 'contact_name', 'contact_phone']);
        //Update the model using repository update method
        $this->repository->update($equipment, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.equipments.index'), ['flash_success' => 'Equipments  Successfully Updated'  . ' <a href="' . route('biller.equipments.show', [$branch->id]) . '" class="ml-5 btn btn-outline-light round btn-min-width bg-blue"><span class="fa fa-eye" aria-hidden="true"></span> ' . trans('general.view') . '  </a> &nbsp; &nbsp;' . ' <a href="' . route('biller.equipments.create') . '" class="btn btn-outline-light round btn-min-width bg-purple"><span class="fa fa-plus-circle" aria-hidden="true"></span> ' . trans('general.create') . '  </a>&nbsp; &nbsp;' . ' <a href="' . route('biller.equipments.index') . '" class="btn btn-outline-blue round btn-min-width bg-amber"><span class="fa fa-list blue" aria-hidden="true"></span> <span class="blue">' . trans('general.list') . '</span> </a>']);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Odu $odu, StoreOduRequest $request)
    {

        //dd($branch);
        //Calling the delete method on repository
        $this->repository->delete($odu);
        //returning with successfull message
        return new RedirectResponse(route('biller.odus.index'), ['flash_success' => 'ODU Successfully Deleted']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(odu $odu, ManageOduRequest $request)
    {

        //returning with successfull message
        return new ViewResponse('focus.odus.view', compact('odu'));
    }

     public function equipment_search(Request $request, $bill_type)
    {
        //if (!access()->allow('product_search')) return false;
        $q = $request->post('keyword');
        //$w = $request->post('client_id');
      


        $equipments = Equipment::where('unique_id', 'LIKE', '%' . $q . '%')->limit(6)->with(['customer'])->get();
        $output = array();

            foreach ($equipments as $row) {



                $output[] = array('name' => $row->unique_id,'customer' => $row->customer->company, 'unit_type' => $row->unit_type, 'make_type' => $row->make_type, 'id' => $row->id, 'capacity' => $row->capacity, 'location' => $row->location, 'next_maintenance_date' => dateFormat($row->next_maintenance_date), 'last_maint_date' => dateFormat($row->last_maint_date));


            }
        

        if (count($output) > 0)

            return view('focus.djcs.partials.search')->withDetails($output);
    }

}
