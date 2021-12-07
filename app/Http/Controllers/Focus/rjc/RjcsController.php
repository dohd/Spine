<?php

namespace App\Http\Controllers\Focus\rjc;

use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
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
        $projects =  Project::all(['id', 'name', 'project_number']);
        $rjc =  Rjc::orderBy('tid', 'desc')->first('tid');
        $tid = isset($rjc) ? $rjc->tid+1 : 1;

        return view('focus.rjcs.create')->with(compact('projects', 'tid'));
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

        $data = $request->only(['tid', 'project_id', 'reference', 'technician', 'action_taken', 'root_cause', 'recommendations', 'subject', 'prepared_by', 'attention', 'region', 'report_date', 'image_one', 'image_two', 'image_three', 'image_four', 'caption_one', 'caption_two', 'caption_three', 'caption_four']);
        $data_items = $request->only(['row_index', 'tag_number', 'joc_card', 'equipment_type', 'make', 'capacity', 'location', 'last_service_date', 'next_service_date']);
        $data['ins'] = auth()->user()->ins;

        $repository = new RjcRepository();
        $repository->create(compact('data', 'data_items'));

        return new RedirectResponse(route('biller.rjcs.index'), ['flash_success' => 'Repair Job Card created Successfully']);
    }    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
