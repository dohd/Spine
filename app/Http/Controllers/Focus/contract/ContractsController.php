<?php

namespace App\Http\Controllers\Focus\contract;

use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Models\equipment\Equipment;
use Illuminate\Http\Request;

class ContractsController extends Controller
{
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
        return new ViewResponse('focus.contracts.create');
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
     * Task Schedules
     */
    public function task_schedule_index()
    {
        return new ViewResponse('focus.contracts.task_schedule_index');
    }

    /**
     * Load Task Schedule Machines
     */
    public function create_schedule_equipment()
    {
        return new ViewResponse('focus.contracts.create_schedule_equipment');
    }
}
