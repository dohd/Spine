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

namespace App\Http\Controllers\Focus\remark;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\remark\CreateResponse;
use App\Http\Responses\Focus\remark\EditResponse;
use App\Repositories\Focus\remark\RemarkRepository;
use App\Http\Requests\Focus\remark\ManageRemarkRequest;
use App\Models\branch\Branch;
use App\Models\remark\Remark;

/**
 * ProductcategoriesController
 */
class RemarksController extends Controller
{
    /**
     * variable to store the repository object
     * @var RemarkRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param RemarkRepository $repository ;
     */
    public function __construct(RemarkRepository $repository)
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
        

        return new ViewResponse('focus.remarks.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create()
    {
         return new CreateResponse('focus.remarks.create');
      
    }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param StoreProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\RedirectResponse
    //  */
    public function store(ManageRemarkRequest $request)
    {
       
        $request->validate([
            
            'recepient' => 'required',
            'reminder_date' => 'required',
            'remarks' => 'required',

        ]);
        // filter request input fields
        $data = $request->except(['_token', 'ins', 'files']);

        //Create the model using repository create method
        $this->repository->create($data);
        
        return response()->json([
            'message'=>'Remark created Successfully',
            'remark'=>$data
        ]);
    }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param \App\Models\remark\Remark $remark
    //  * @param EditProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\Focus\productcategory\EditResponse
    //  */
    public function edit(Remark $remark)
    {
        $branches = Branch::get(['id', 'name', 'customer_id']);
       

        return new EditResponse('focus.remarks.edit', compact('remark', 'branches'));
    }

    // /**
    //  * Update the specified resource.
    //  *
    //  * @param \App\Models\remark\Remark $remark
    //  * @param EditProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\Focus\productcategory\EditResponse
    //  */
    public function update(Request $request, Remark $remark)
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
        $this->repository->update($remark, $data);

        return view('successModal');
    }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param \App\Models\remark\Remark $remark
    //  * @return \App\Http\Responses\RedirectResponse
    //  */
    public function destroy(Remark $remark)
    {
        $this->repository->delete($remark);
            
        return new RedirectResponse(route('biller.remarks.index'), ['flash_success' => 'Remark Successfully Deleted']);
    }

    // /**
    //  * Show the view for the specific resource
    //  *
    //  * @param DeleteProductcategoryRequestNamespace $request
    //  * @param \App\Models\remark\Remark $remark
    //  * @return \App\Http\Responses\RedirectResponse
    //  */
    public function show(Remark $remark, Request $request)
    {
        return new ViewResponse('focus.remarks.view', compact('remark'));
    }

    

    // /**
    //  * Update Remark Open Status
    //  */
    public function update_status(Remark $remark, Request $request)
    {
        
        $status = $request->status;
        $reason = $request->reason;
        $remark->update(compact('status', 'reason'));

        return redirect()->back();
    }
}
