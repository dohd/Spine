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
use App\Models\branch\Branch;
use App\Models\customer\Customer;

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

        // $core = $this->lead->getForDataTable();
        // dd($core );

        return new ViewResponse('focus.leads.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create(ManageLeadRequest $request)
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
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;
        $input['user_id'] = auth()->user()->id;

        //Create the model using repository create method
        $id = $this->repository->create($input);

        //return with successfull message
        return new RedirectResponse(
            route('biller.leads.index'),
            [
                'flash_success' => 'Lead  Successfully Created'
                    . ' <a href="'
                    . route('biller.leads.show', [$id])
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
     * @param App\Models\productcategory\Productcategory $productcategory
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(Lead $lead)
    {
        //dd(0);
        return new EditResponse($lead);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */

    public function lead_load(Request $request)
    {
        $id = $request->get('id');
        $result = Lead::all()->where('rel_id', '=', $id);
        return json_encode($result);
    }


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

        // update date format to 'YY-MM-DD'
        $input['date_of_request'] = date('Y-m-d', strtotime($input['date_of_request']));

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
     * @param App\Models\productcategory\Productcategory $productcategory
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
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Lead $lead, Request $request)
    {
        $branch = Branch::find($lead->branch_id, ['id', 'name']);
        $customer = Customer::find($lead->client_id, ['id', 'name', 'phone', 'email']);

        return new ViewResponse('focus.leads.view', compact('lead', 'branch', 'customer'));
    }

    public function lead_search(ManageLeadRequest $request)
    {
        $q = $request->post('keyword');
        // $user = \App\Models\lead\Lead::with('primary_group')->where('name', 'LIKE', '%' . $q . '%')->where('active', '=', 1)->orWhere('email', 'LIKE', '%' . $q . '')->limit(6)->get(array('id', 'taxid', 'name', 'phone', 'address', 'city', 'email'));
        $lead = \App\Models\lead\Lead::where('id', $q)->first();
        if (count($lead) > 0) return $lead;
        return false;
    }
}
