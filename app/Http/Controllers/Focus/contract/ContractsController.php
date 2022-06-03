<?php

namespace App\Http\Controllers\Focus\contract;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\contract\Contract;
use App\Models\equipment\Equipment;
use App\Repositories\Focus\contract\ContractRepository;
use Illuminate\Http\Request;

class ContractsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ContractRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ContractRepository $repository ;
     */
    public function __construct(ContractRepository $repository)
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
        return new ViewResponse('focus.contracts.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $last_tid = Contract::max('tid');

        return new ViewResponse('focus.contracts.create', compact('last_tid'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // extract input fields
        $contract_data = $request->only([
            'tid', 'customer_id', 'title', 'start_date', 'end_date', 'amount', 'period', 'schedule_period', 'note'
        ]);
        $schedule_data = $request->only('s_title', 's_start_date', 's_end_date');
        $equipment_data = $request->only('equipment_id');

        $contract_data['ins'] = auth()->user()->ins;
        $contract_data['user_id'] = auth()->user()->id;
        $schedule_data = modify_array($schedule_data);
        $equipment_data = modify_array($equipment_data);

        $this->repository->create(compact('contract_data', 'schedule_data', 'equipment_data'));

        return new RedirectResponse(route('biller.contracts.index'), ['flash_success' => 'Contract created successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Contract $contract)
    {
        return new ViewResponse('focus.contracts.view', compact('contract'));
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

    /**
     * Contract task schedules
     */
    public function task_schedules()
    {
        $contract = Contract::find(request('id'));
        $schedules = $contract ? $contract->task_schedules : array();

        return response()->json($schedules);
    }

    /**
     * Customer equipments
     */
    public function customer_equipment()
    {
        $equipments = Equipment::where('customer_id', request('id'))->with(['branch' => function($q) {
            $q->get(['id', 'name']);
        }])->limit(10)->get()->toArray();
        // filter columns
        foreach ($equipments as $i => $item) {
            $val = [];
            foreach ($item as $k => $v) {
                if (in_array($k, ['id', 'unique_id', 'branch', 'location', 'make_type'], 1))
                $val[$k] = $v;
            }
            $equipments[$i] = $val;
        }

        return response()->json($equipments);
    }

    /**
     * Contract equipments
     */
    public function contract_equipment()
    {
        $equipments = Equipment::whereIn('id', function ($q) {
            $q->select('equipment_id')->from('contract_equipments')->where([
                'contract_id' => request('id'), 
                'schedule_id' => 0
            ]);
        })
        ->with(['branch' => function($q) {
            $q->get(['id', 'name']);
        }])->limit(10)->get()->toArray();
        // filter columns
        foreach ($equipments as $i => $item) {
            $val = [];
            foreach ($item as $k => $v) {
                if (in_array($k, ['id', 'unique_id', 'branch', 'location', 'make_type'], 1))
                $val[$k] = $v;
            }
            $equipments[$i] = $val;
        }

        return response()->json($equipments);
    }    
}
