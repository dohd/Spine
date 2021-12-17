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

namespace App\Http\Controllers\Focus\djc;

use App\Models\djc\Djc;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\djc\CreateResponse;
use App\Http\Responses\Focus\djc\EditResponse;
use App\Repositories\Focus\djc\DjcRepository;
use App\Http\Requests\Focus\djc\ManageDjcRequest;
use App\Models\items\DjcItem;
use App\Models\lead\Lead;

/**
 * DjcsController
 */
class DjcsController extends Controller
{
    /**
     * variable to store the repository object
     * @var AccountRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param AccountRepository $repository ;
     */
    public function __construct(DjcRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\account\ManageAccountRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.djcs.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateDjcRequestNamespace $request
     * @return \App\Http\Responses\Focus\djc\CreateResponse
     */
    public function create(ManageDjcRequest $request)
    {
        $leads = Lead::orderBy('id', 'desc')->get();
        $djc =  Djc::orderBy('tid', 'desc')->first('tid');
        $tid =  isset($djc)? $djc->tid+1 : 1;
        
        return new CreateResponse('focus.djcs.create', compact('leads','tid'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreAccountRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(ManageDjcRequest $request)
    {
        $request->validate([
            'attention' => 'required',
            'prepared_by' => 'required',
            'technician' => 'required',
            'subject' => 'required'
        ]);

        $data = $request->only(['client_ref', 'jobcard_date', 'job_card', 'tid', 'lead_id', 'client_id', 'branch_id', 'reference', 'technician', 'action_taken', 'root_cause', 'recommendations', 'subject', 'prepared_by', 'attention', 'region', 'report_date', 'image_one', 'image_two', 'image_three', 'image_four', 'caption_one', 'caption_two', 'caption_three', 'caption_four']);
        $data_item = $request->only(['row_index', 'tag_number', 'joc_card', 'equipment_type', 'make', 'capacity', 'location', 'last_service_date', 'next_service_date']);
        $data['ins'] = auth()->user()->ins;

        //Create the model using repository create method
        $result = $this->repository->create(compact('data', 'data_item'));

        return new RedirectResponse(route('biller.djcs.index', [$result['id']]), ['flash_success' => 'Djc Report Created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\djc\Djc $djc
     * @return \App\Http\Responses\Focus\djc\EditResponse
     */
    public function edit(Djc $djc)
    {
        $leads = Lead::all();
        $items = $djc->items()->orderBy('row_index', 'ASC')->get();

        return new EditResponse('focus.djcs.edit', compact('djc', 'leads', 'items'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateDjcRequestNamespace $request
     * @param App\Models\djc\Djc $djc
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(ManageDjcRequest $request, Djc $djc)
    {
        $request->validate([
            'attention' => 'required',
            'prepared_by' => 'required',
            'technician' => 'required',
            'subject' => 'required'
        ]);
        
        $data = $request->only(['client_ref', 'jobcard_date', 'job_card', 'tid', 'lead_id', 'client_id', 'branch_id', 'reference', 'technician', 'action_taken', 'root_cause', 'recommendations', 'subject', 'prepared_by', 'attention', 'region', 'report_date', 'image_one', 'image_two', 'image_three', 'image_four', 'caption_one', 'caption_two', 'caption_three', 'caption_four']);
        $data_item = $request->only(['row_index', 'item_id', 'tag_number', 'joc_card', 'equipment_type', 'make', 'capacity', 'location', 'last_service_date', 'next_service_date']);
        
        $data['ins'] = auth()->user()->ins;
        $data['id'] = $djc->id;

        // Update using repository update method
        $this->repository->update(compact('data', 'data_item'));

        //return with successfull message
        return new RedirectResponse(route('biller.djcs.index'), ['flash_success' => 'Djc report updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteAccountRequestNamespace $request
     * @param App\Models\djc\Djc $djc
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Djc $djc)
    {
        $this->repository->delete($djc);
        
        return new RedirectResponse(route('biller.djcs.index'), ['flash_success' => 'Djc deleted successfully']);
    }

    /**
     * View the specified resource from storage
     * 
     * @param App\Models\djc\Djc $djc
     * @return \App\Http\Responses\ViewResponse
     */
    public function show(Djc $djc)
    {
        $djc_items = DjcItem::where('djc_id', $djc->id)->get();

        return new ViewResponse('focus.djcs.view', compact('djc', 'djc_items'));
    }

    // Delete djc item
    public function delete_item($id)
    {
        $this->repository->delete_item($id);

        return response()->noContent();
    }
}
