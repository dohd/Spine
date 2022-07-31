<?php

namespace App\Http\Controllers\Focus\contract;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\contract\Contract;
use App\Models\equipment\Equipment;
use App\Repositories\Focus\contract\ContractRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
    public function edit(Contract $contract)
    {
        return new ViewResponse('focus.contracts.edit', compact('contract'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Contract $contract)
    {
        // extract input fields
        $contract_data = $request->only([
            'tid', 'customer_id', 'title', 'start_date', 'end_date', 'amount', 'period', 'schedule_period', 'note'
        ]);
        $schedule_data = $request->only('s_id', 's_title', 's_start_date', 's_end_date');
        $equipment_data = $request->only('contracteq_id', 'equipment_id');

        $contract_data['ins'] = auth()->user()->ins;
        $contract_data['user_id'] = auth()->user()->id;
        
        $schedule_data = modify_array($schedule_data);
        $equipment_data = modify_array($equipment_data);
        
        if (!$schedule_data || !$equipment_data) 
            throw ValidationException::withMessages(['Task Schedule or Equipment required!']);
            
        $this->repository->update($contract, compact('contract_data', 'schedule_data', 'equipment_data'));

        return new RedirectResponse(route('biller.contracts.index'), ['flash_success' => 'Contract edited successfully']);
    }


    /**
     * Remove resource from storage
     */
    public function destroy(Contract $contract)
    {
        $this->repository->delete($contract);

        return new RedirectResponse(route('biller.contracts.index'), ['flash_success' => 'Contract deleted successfully']);
    }

    /**
     * Load Additional Equipments
     */
    public function create_add_equipment()
    {
        return new ViewResponse('focus.contracts.create_add_equipment');
    }

    public function store_add_equipment(Request $request)
    {
        // extract request input
        $contract_id = $request->contract_id;
        $data_items = $request->only('equipment_id');

        $data_items = modify_array($data_items);
        $data_items = array_map(function ($v) use($contract_id) {
            return $v + compact('contract_id');
        }, $data_items);

        $this->repository->add_equipment($data_items);

        return new ViewResponse('focus.contracts.index', ['flash_success' => 'Contract Equipment added successfully']);
    }

    /**
     * Customer Contracts
     */
    public function customer_contracts(Request $request)
    {
        $contracts = Contract::where(['customer_id' => $request->customer_id])->get();

        return response()->json($contracts);
    }

    /**
     * Contract task schedules
     */
    public function task_schedules(Request $request)
    {
        $contract = Contract::find($request->contract_id);

        return response()->json($contract->task_schedules);
    }

    /**
     * Customer equipments
     */
    public function customer_equipment(Request $request)
    {
        $customer = $request->only('customer_id');
        $branch = $request->only('branch_id');

        printlog($customer, $branch);

        $equipments = Equipment::when($customer, function ($q) use($customer) {
            $q->where($customer);
        })->when($branch, function ($q) use($branch) {
            $q->where($branch);
        })->with(['branch' => function($q) {
            $q->get(['id', 'name']);
        }])->get()
        ->map(function ($v) {
            return [
                'id' => $v->id, 
                'unique_id' => $v->unique_id, 
                'branch' => $v->branch, 
                'location' => $v->location, 
                'make_type' => $v->make_type
            ];
        });
        
        return response()->json($equipments);
    }

    /**
     * Contract equipments
     */
    public function contract_equipment(Request $request)
    {
        $contract = Contract::find($request->contract_id);
        $equipments = $contract->equipments()->get()->map(function ($v) {
            return [
                'id' => $v->id, 
                'unique_id' => $v->unique_id, 
                'branch' => $v->branch, 
                'location' => $v->location, 
                'make_type' => $v->make_type,
                'service_rate' => $v->service_rate
            ];
        });

        return response()->json($equipments);
    }    
}
