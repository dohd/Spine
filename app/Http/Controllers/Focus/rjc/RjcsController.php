<?php

namespace App\Http\Controllers\Focus\rjc;

use App\Http\Controllers\Controller;
use App\Http\Requests\Focus\rjc\ManageRjcRequest;
use App\Http\Responses\Focus\rjc\EditResponse;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\items\RjcItem;
use App\Models\project\Project;
use App\Models\rjc\Rjc;
use App\Repositories\Focus\rjc\RjcRepository;
use Illuminate\Http\Request;

class RjcsController extends Controller
{
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param RjcRepository $repository ;
     */
    public function __construct(RjcRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.rjcs.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $last_rjc =  Rjc::orderBy('tid', 'DESC')->first('tid');
        $projects =  Project::doesntHave('rjc')
            ->whereHas('quotes', function ($q) {
                $q->where('verified', 'Yes')->whereIn('invoiced', ['Yes', 'No']);
            })->get(['id', 'name', 'tid', 'main_quote_id']);

        foreach($projects as $project) {
            $lead_tids = [];
            $quote_tids = [];                
            foreach ($project->quotes as $quote) {
                $lead_tids[] = gen4tid('Tkt-', $quote->lead->reference);
                if ($quote->bank_id) $quote_tids[] = gen4tid('PI-', $quote->tid);
                else $quote_tids[] = gen4tid('QT-', $quote->tid);
            }
            $project['lead_tids'] = implode(', ', $lead_tids);            
            $project['quote_tids'] = implode(', ', $quote_tids);            
        }
        
        return view('focus.rjcs.create', compact('projects', 'last_rjc'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'attention' => 'required',
            'prepared_by' => 'required',
            'technician' => 'required',
            'subject' => 'required'
        ]);

        $data = $request->only([
            'tid', 'project_id', 'client_ref', 'technician', 'action_taken', 'root_cause', 'recommendations', 'subject', 
            'prepared_by', 'attention', 'region', 'report_date', 'image_one', 'image_two', 'image_three', 'image_four', 'caption_one', 
            'caption_two', 'caption_three', 'caption_four'
        ]);
        $data_items = $request->only(['row_index', 'tag_number', 'joc_card', 'equipment_type', 'make', 'capacity', 'location', 'last_service_date', 'next_service_date']);
        $data['ins'] = auth()->user()->ins;

        $result = $this->repository->create(compact('data', 'data_items'));

        return new RedirectResponse(route('biller.rjcs.index'), ['flash_success' => 'Rjc Report successfully created']);
    }    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Rjc $rjc)
    {
        $rjc_items = RjcItem::where('rjc_id', $rjc->id)->get();

        return new ViewResponse('focus.rjcs.view', compact('rjc', 'rjc_items'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Rjc $rjc)
    {
        $items = $rjc->rjc_items()->orderBy('row_index')->get();
        $projects =  Project::where('main_quote_id', '>', 0)
            ->orderBy('id', 'desc')
            ->get(['id', 'name', 'tid', 'main_quote_id']);
        // append quote tid
        foreach($projects as $project) {
            $lead_tids = array();
            $quote_tids = array();                
            foreach ($project->quotes as $quote) {
                $lead_tids[] = 'Tkt-'.sprintf('%04d', $quote->lead->reference);
                // quote
                $tid = sprintf('%04d', $quote->tid);
                if ($quote->bank_id) $quote_tids[] = 'PI-'. $tid;
                else $quote_tids[] = 'QT-'. $tid;
            }
            $project['lead_tids'] = implode(', ', $lead_tids);            
            $project['quote_tids'] = implode(', ', $quote_tids);            
        }

        return new EditResponse('focus.rjcs.edit', compact('rjc', 'projects', 'items'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ManageRjcRequest $request, Rjc $rjc)
    {
        $request->validate([
            'attention' => 'required',
            'prepared_by' => 'required',
            'technician' => 'required',
            'subject' => 'required'
        ]);
        $data = $request->only([
            'tid', 'project_id', 'client_ref', 'technician', 'action_taken', 'root_cause', 'recommendations', 
            'subject', 'prepared_by', 'attention', 'region', 'report_date', 'image_one', 'image_two', 'image_three', 
            'image_four', 'caption_one', 'caption_two', 'caption_three', 'caption_four'
        ]);
        $data_items = $request->only(['row_index', 'item_id', 'tag_number', 'joc_card', 'equipment_type', 'make', 'capacity', 'location', 'last_service_date', 'next_service_date']);
        
        $data['ins'] = auth()->user()->ins;
        $data['id'] = $rjc->id;

        $this->repository->update(compact('data', 'data_items'));

        return new RedirectResponse(route('biller.rjcs.index'), ['flash_success' => 'Rjc Report successfully updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Rjc $rjc)
    {
        $this->repository->delete($rjc);

        return new RedirectResponse(route('biller.rjcs.index'), ['flash_success' => 'Rjc report successfully deleted']);
    }
}
