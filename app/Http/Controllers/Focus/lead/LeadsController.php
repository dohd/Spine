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

use App\Models\lead\Lead;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\lead\CreateResponse;
use App\Http\Responses\Focus\lead\EditResponse;
use App\Repositories\Focus\lead\LeadRepository;
use App\Http\Requests\Focus\lead\ManageLeadRequest;

/**
 * ProductcategoriesController
 */
class LeadsController extends Controller
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
        return new ViewResponse('focus.leads.index');
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
        $input = $request->except(['_token', 'ins']);

        $input['ins'] = auth()->user()->ins;
        $input['user_id'] = auth()->user()->id;

        //Create the model using repository create method
        $result = $this->repository->create($input);

        return new RedirectResponse(
            route('biller.leads.index'),
            [
                'flash_success' => 'Lead  Successfully Created'
                    . ' <a href="'
                    . route('biller.leads.show', $result['id'])
                    . '" class="ml-5 btn btn-outline-light round btn-min-width bg-blue"><span class="fa fa-eye" aria-hidden="true"></span> '
                    . trans('general.view')
                    . '  </a> &nbsp; &nbsp;'
                    . ' <a href="'
                    . route('biller.leads.create')
                    . '" class="btn btn-outline-light round btn-min-width bg-purple"><span class="fa fa-plus-circle" aria-hidden="true"></span> '
                    . trans('general.create')
                    . '  </a>&nbsp; &nbsp;'
                    . ' <a href="'
                    . route('biller.leads.index')
                    . '" class="btn btn-outline-blue round btn-min-width bg-amber"><span class="fa fa-list blue" aria-hidden="true"></span> <span class="blue">'
                    . trans('general.list')
                    . '</span> </a>'
            ]
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\Lead $lead
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
     * @param App\Models\Lead $lead
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function update(Request $request, Lead $lead)
    {
        // fields to validate
        $fields = [
            'reference' => 'required',
            'date_of_request' => 'required',
            'title' => 'required',
            'source' => 'required',
            'assign_to' => 'required',
        ];
        $request->validate($fields);
        $input = $request->except(['_token', 'ins']);
        $input['date_of_request'] = date_for_database($input['date_of_request']);

        //Update the model using repository update method
        $this->repository->update($lead, $input);

        return new RedirectResponse(
            route('biller.leads.index'),
            [
                'flash_success' => 'Lead Successfully Updated'
                    . '<a href="'
                    . route('biller.leads.show', [$lead->id])
                    . '" class="ml-5 btn btn-outline-light round btn-min-width bg-blue"><span class="fa fa-eye" aria-hidden="true"></span> '
                    . trans('general.view')
                    . '  </a> &nbsp; &nbsp;'
                    . ' <a href="'
                    . route('biller.leads.create')
                    . '" class="btn btn-outline-light round btn-min-width bg-purple"><span class="fa fa-plus-circle" aria-hidden="true"></span> '
                    . trans('general.create')
                    . '  </a>&nbsp; &nbsp;'
                    . ' <a href="'
                    . route('biller.leads.index')
                    . '" class="btn btn-outline-blue round btn-min-width bg-amber"><span class="fa fa-list blue" aria-hidden="true"></span> <span class="blue">'
                    . trans('general.list')
                    . '</span> </a>'
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\Lead $lead
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Lead $lead)
    {
        //dd($lead);
        //Calling the delete method on repository
        $this->repository->delete($lead);
        //returning with successfull message
        return new RedirectResponse(route('biller.leads.index'), ['flash_success' => 'Lead Successfully Deleted']);
    }

    /**
     * Show the view for the specific resource
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\Lead $lead
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
        $result = Lead::all()->where('rel_id', '=', $id);
        return json_encode($result);
    }
    
    // search specific lead with defined parameters
    public function lead_search(ManageLeadRequest $request)
    {
        $q = $request->post('keyword');
        $lead = Lead::where('id', $q)->first();
        if ($lead) return $lead;
        return false;
    }

    // update Lead status
    public function update_status(Request $request, $id)
    {
        $status = $request->post('status');
        $reason = $request->post('reason');

        Lead::find($id)->update(compact('status', 'reason'));

        return redirect()->back();
    }
}
