<?php

namespace App\Http\Controllers\Focus\contractservice;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\contract\Contract;
use App\Models\contractservice\ContractService;
use App\Repositories\Focus\contractservice\ContractServiceRepository;
use Illuminate\Http\Request;

class ContractServicesController extends Controller
{
    /**
     * variable to store the repository object
     * @var ContractServiceRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ContractServiceRepository $repository ;
     */
    public function __construct(ContractServiceRepository $repository)
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
        return new ViewResponse('focus.contractservices.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // 
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ContractService $contractservice)
    {
        return new ViewResponse('focus.contractservices.view', compact('contractservice'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(ContractService $contractservice)
    {
        $contracts = Contract::all();

        return new ViewResponse('focus.contractservices.edit', compact('contractservice', 'contracts'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ContractService $contractservice)
    {
        // extract request input
        $data_items = $request->only('id', 'jobcard_no', 'jobcard_date', 'status', 'note', 'is_charged', 'technician');

        $data_items = modify_array($data_items);

        $this->repository->update($contractservice, $data_items);

        return new RedirectResponse(route('biller.contractservices.index'), ['flash_success' => 'Service updated successfully']);
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
     * Service poduct search
     */
    public function service_product_search()
    {
        $q = request('term');

        $services = ContractService::where('name', 'LIKE', '%'. $q .'%')->limit(10)
            ->get(['id', 'name', 'amount as price'])->toArray();
            
        $services = array_map(function ($v) {
            $v['unit'] = 'Pc';
            $v['purchase_price'] = '0.00';
            return $v;
        }, $services);

        return response()->json($services);
    }
}
