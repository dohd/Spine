<?php

namespace App\Http\Controllers\Focus\taskschedule;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\contract\Contract;
use App\Repositories\Focus\taskschedule\TaskScheduleRepository;
use Illuminate\Http\Request;

class TaskSchedulesController extends Controller
{
    /**
     * variable to store the repository object
     * @var TaskScheduleRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param TaskScheduleRepository $repository ;
     */
    public function __construct(TaskScheduleRepository $repository)
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
        return new ViewResponse('focus.taskschedules.index');        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $contracts = Contract::all();

        return new ViewResponse('focus.taskschedules.create', compact('contracts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        // extract request input
        $data = $request->only('contract_id', 'schedule_id');
        $data_items = $request->only('equipment_id');

        $data_items = modify_array($data_items);

        $this->repository->create(compact('data', 'data_items'));
        
        return new RedirectResponse(route('biller.taskschedules.index'), ['flash_success' => 'Task Schedule Equipments loaded successfully']);
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
