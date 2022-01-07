<?php

namespace App\Http\Controllers\Focus\lpo;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\lpo\Lpo;
use Illuminate\Http\Request;

class LpoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('focus.lpo.index');
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
        // filter input fields
        $input = $request->only('customer_id', 'branch_id', 'date', 'lpo_no', 'amount', 'remark');
        $input['amount'] = floatval($input['amount']);
        Lpo::create($input);

        return new RedirectResponse(route('biller.lpo.index'), ['flash_success' => 'LPO created successfully']);
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

    // data for LpoTableController
    static function getForDataTable()
    {
        return Lpo::query()->get();
    }
}
