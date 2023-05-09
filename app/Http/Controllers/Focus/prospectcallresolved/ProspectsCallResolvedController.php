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

namespace App\Http\Controllers\Focus\prospectcallresolved;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\prospect\CreateResponse;
use App\Http\Responses\Focus\prospect\EditResponse;
use App\Repositories\Focus\prospectcallresolved\ProspectCallResolvedRepository;
use App\Http\Requests\Focus\prospectcallresolved\ProspectCallResolvedRequest;
use App\Models\prospectcallresolved\ProspectCallResolved;


/**
 * ProductcategoriesController
 */
class ProspectsCallResolvedController extends Controller
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
    public function __construct(ProspectCallResolvedRepository $repository)
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
        $open_prospect = ProspectCallResolved::where('status', 0)->count();
        $closed_prospect = ProspectCallResolved::where('status', 1)->count();
        $total_prospect = ProspectCallResolved::count();

        return new ViewResponse('focus.prospects.index', compact('open_prospect', 'closed_prospect', 'total_prospect'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    // public function create()
    // {
    //     return new CreateResponse('focus.prospects.create');
    //     //return view('focus.prospects.create', ['branches' => collect()]);
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param StoreProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\RedirectResponse
    //  */
    public function store(ProspectCallResolvedRequest $request)
    {

        // filter request input fields
        $data = $request->except(['_token', 'ins', 'files']);
       

        //Create the model using repository create method
        $this->repository->create($data);

        return new RedirectResponse(route('biller.calllists.mytoday'), ['flash_success' => 'ProspectCallResolved Successfully Created']);
    }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param \App\Models\prospect\ProspectCallResolved $prospect
    //  * @param EditProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\Focus\productcategory\EditResponse
    //  */
    public function edit(ProspectCallResolved $prospect)
    {
        return new EditResponse('focus.prospects.edit', compact('prospect'));
    }


    // follow up
    // public function followup(Request $request)
    // {
    //     $remarks = Remark::where('prospect_id', $request->id)->orderBy('updated_at', 'DESC')->limit(10)->get();
    //     return view('focus.prospects.partials.remarks_table', compact('remarks'));
    // }



    // /**
    //  * Update the specified resource.
    //  *
    //  * @param \App\Models\prospect\ProspectCallResolved $prospect
    //  * @param EditProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\Focus\productcategory\EditResponse
    //  */
    public function update(ProspectCallResolvedRequest $request, ProspectCallResolved $prospect)
    {

        //dd($request);
        // update input fields from request
        $data = $request->only(['company', 'name', 'email', 'phone', 'region', 'industry']);

        //Update the model using repository update method
        $this->repository->update($prospect, compact('data'));

        return new RedirectResponse(route('biller.prospects.index'), ['flash_success' => 'ProspectCallResolved Successfully Updated']);
    }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param \App\Models\prospect\ProspectCallResolved $prospect
    //  * @return \App\Http\Responses\RedirectResponse
    //  */
    public function destroy(ProspectCallResolved $prospect)
    {
        $this->repository->delete($prospect);

        return new RedirectResponse(route('biller.prospects.index'), ['flash_success' => 'ProspectCallResolved Successfully Deleted']);
    }

    // /**
    //  * Show the view for the specific resource
    //  *
    //  * @param DeleteProductcategoryRequestNamespace $request
    //  * @param \App\Models\prospect\ProspectCallResolved $prospect
    //  * @return \App\Http\Responses\RedirectResponse
    //  */
    public function show(ProspectCallResolved $prospect)
    {
        return new ViewResponse('focus.prospects.view', compact('prospect'));
    }

    // /**
    //  * Update ProspectCallResolved Open Status
    //  */
    public function update_status(ProspectCallResolved $prospect, Request $request)
    {

        $status = $request->status;
        $reason = $request->reason;
        $prospect->update(compact('status', 'reason'));

        return redirect()->back();
    }

    public function notpicked(Request $request)
    {

        // filter request input fields
        $data = $request->except(['_token', 'ins', 'files','call_id']);
        $calllist = $request->only(['prospect_id', 'call_id', 'reminder_date']);
        
        //Create the model using repository create method
        $this->repository->notpickedcreate($data,$calllist);

        return new RedirectResponse(route('biller.calllists.mytoday'), ['flash_success' => 'ProspectCallResolved Successfully Created']);
    }
    public function pickedbusy(Request $request)
    {

        // filter request input fields
        $data = $request->except(['_token', 'ins', 'files','call_id']);

        $calllist = $request->only(['prospect_id', 'call_id', 'reminder_date']);
        
        //Create the model using repository create method
        $this->repository->pickedbusycreate($data,$calllist);

        return new RedirectResponse(route('biller.calllists.mytoday'), ['flash_success' => 'ProspectCallResolved Successfully Created']);
    }
}
