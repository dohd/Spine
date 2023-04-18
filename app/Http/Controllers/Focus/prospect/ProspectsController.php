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

namespace App\Http\Controllers\Focus\prospect;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\prospect\CreateResponse;
use App\Http\Responses\Focus\prospect\EditResponse;
use App\Repositories\Focus\prospect\ProspectRepository;
use App\Http\Requests\Focus\prospect\ManageProspectRequest;
use App\Models\branch\Branch;
use App\Models\prospect\Prospect;
use App\Models\remark\Remark;

/**
 * ProductcategoriesController
 */
class ProspectsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProspectRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProspectRepository $repository ;
     */
    public function __construct(ProspectRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        $open_prospect = Prospect::where('status', 0)->count();
        $closed_prospect = Prospect::where('status', 1)->count();
        $total_prospect = Prospect::count();

        return new ViewResponse('focus.prospects.index', compact('open_prospect', 'closed_prospect', 'total_prospect'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create()
    {
         return new CreateResponse('focus.prospects.create');
        //return view('focus.prospects.create', ['branches' => collect()]);
    }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param StoreProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\RedirectResponse
    //  */
    public function store(ManageProspectRequest $request)
    {
        $request->validate([
            'name' => 'required',
            'company' => 'required',
            'email' => 'required',
            'phone' => 'required',

        ]);
        // filter request input fields
        $data = $request->except(['_token', 'ins', 'files']);


        //Create the model using repository create method
        $this->repository->create($data);

        return new RedirectResponse(route('biller.prospects.index'), ['flash_success' => 'Prospect Successfully Created']);
    }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param \App\Models\prospect\Prospect $prospect
    //  * @param EditProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\Focus\productcategory\EditResponse
    //  */
    public function edit(Prospect $prospect)
    {
        $branches = Branch::get(['id', 'name', 'customer_id']);
       

        return new EditResponse('focus.prospects.edit', compact('prospect', 'branches'));
    }


    // follow up
    public function followup(Request $request)
    {
      
        $prospect = Prospect::where('id', $request->id); 
        $remarks = [];                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 
        if($prospect){
            $remarks = Remark::where('prospect_id',$request->id)->orderBy('created_at', 'DESC')->limit(10)->get();
           
        }
        return view('focus.prospects.partials.remarks_table',compact('remarks'));
    }



    // /**
    //  * Update the specified resource.
    //  *
    //  * @param \App\Models\prospect\Prospect $prospect
    //  * @param EditProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\Focus\productcategory\EditResponse
    //  */
    public function update(Request $request, Prospect $prospect)
    {
        // validate fields
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'reminder_date' => 'required',
            'remarks' => 'required'

        ]);

        // update input fields from request
        $data = $request->except(['_token', 'ins', 'files']);

        //Update the model using repository update method
        $this->repository->update($prospect, $data);

        return new RedirectResponse(route('biller.prospects.index'), ['flash_success' => 'Prospect Successfully Updated']);
    }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param \App\Models\prospect\Prospect $prospect
    //  * @return \App\Http\Responses\RedirectResponse
    //  */
    public function destroy(Prospect $prospect)
    {
        $this->repository->delete($prospect);
            
        return new RedirectResponse(route('biller.prospects.index'), ['flash_success' => 'Prospect Successfully Deleted']);
    }

    // /**
    //  * Show the view for the specific resource
    //  *
    //  * @param DeleteProductcategoryRequestNamespace $request
    //  * @param \App\Models\prospect\Prospect $prospect
    //  * @return \App\Http\Responses\RedirectResponse
    //  */
    public function show(Prospect $prospect, Request $request)
    {
        return new ViewResponse('focus.prospects.view', compact('prospect'));
    }

    // /**
    //  * Update Prospect Open Status
    //  */
    public function update_status(Prospect $prospect, Request $request)
    {
        
        $status = $request->status;
        $reason = $request->reason;
        $prospect->update(compact('status', 'reason'));

        return redirect()->back();
    }
}
