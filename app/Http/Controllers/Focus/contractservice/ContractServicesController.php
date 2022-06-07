<?php

namespace App\Http\Controllers\Focus\contractservice;

use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Models\contract\Contract;
use App\Models\contractservice\ContractService;
use App\Models\task_schedule\TaskSchedule;
use Illuminate\Http\Request;

class ContractServicesController extends Controller
{
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
