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

namespace App\Http\Controllers\Focus\lead;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\lead\CreateResponse;
use App\Http\Responses\Focus\lead\EditResponse;
use App\Repositories\Focus\lead\LeadRepository;
use App\Http\Requests\Focus\lead\ManageLeadRequest;
use App\Models\lead\Lead;

/**
 * ProductcategoriesController
 */
class LeadsController extends Controller
{
    /**
     * variable to store the repository object
     * @var LeadRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param LeadRepository $repository ;
     */
    public function __construct(LeadRepository $repository)
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
        $leads_open = Lead::where('status', 0)->count();
        $leads_closed = Lead::where('status', 1)->count();

        return new ViewResponse('focus.leads.index', compact('leads_open', 'leads_closed'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create()
    {
        return new CreateResponse('focus.leads.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(ManageLeadRequest $request)
    {
        $request->validate([
            'reference' => 'required',
            'date_of_request' => 'required',
            'title' => 'required',
            'source' => 'required',
            'assign_to' => 'required'

        ]);
        // filter request input fields
        $data = $request->except(['_token', 'ins', 'files']);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        //Create the model using repository create method
        $this->repository->create($data);

        return new RedirectResponse(route('biller.leads.index'), ['flash_success' => 'Ticket Successfully Created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\lead\Lead $lead
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(Lead $lead)
    {
        return new EditResponse('focus.leads.edit', compact('lead'));
    }

    /**
     * Update the specified resource.
     *
     * @param \App\Models\lead\Lead $lead
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function update(Request $request, Lead $lead)
    {
        // validate fields
        $fields = [
            'reference' => 'required',
            'date_of_request' => 'required',
            'title' => 'required',
            'source' => 'required',
            'assign_to' => 'required',
        ];
        $request->validate($fields);

        // update input fields from request
        $data = $request->except(['_token', 'ins', 'files']);
        $data['date_of_request'] = date_for_database($data['date_of_request']);

        //Update the model using repository update method
        $this->repository->update($lead, $data);

        return new RedirectResponse(route('biller.leads.index'), ['flash_success' => 'Ticket Successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\lead\Lead $lead
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Lead $lead)
    {
        $resp = $this->repository->delete($lead);
            
        return new RedirectResponse(route('biller.leads.index'), ['flash_success' => 'Ticket Successfully Deleted']);
    }

    /**
     * Show the view for the specific resource
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param \App\Models\lead\Lead $lead
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Lead $lead, Request $request)
    {
        return new ViewResponse('focus.leads.view', compact('lead'));
    }

    // fetch lead details with specific lead_id
    public function lead_load(Request $request)
    {
        $id = $request->get('id');
        
        $leads = Lead::all()->where('rel_id', $id);

        return response()->json($leads);
    }
    
    // search specific lead with defined parameters
    public function lead_search(ManageLeadRequest $request)
    {
        $q = $request->post('keyword');

        $leads = Lead::where('title', 'LIKE', '%'. $q .'%')->limit(6)->get();

        return response()->json($leads);        
    }

    /**
     * Update Lead Open Status
     */
    public function update_status(Lead $lead, Request $request)
    {
        // dd($lead);
        $status = $request->status;
        $reason = $request->reason;
        $lead->update(compact('status', 'reason'));

        return redirect()->back();
    }
}
