<?php

namespace App\Http\Controllers\Focus\bills;

use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Models\bill\Bill;
use Illuminate\Http\Request;

class BillsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.bills.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return new ViewResponse('focus.bills.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        dd($request->all());
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
     * Fetch Supplier bills
     */
    public function supplier_bills(Request $request)
    {
        $bills = Bill::where('supplier_id', $request->id)
            ->whereIn('status', ['Pending', 'Partial'])
            ->get(['id', 'tid', 'supplier_id', 'note', 'status', 'grandttl']);

        return response()->json($bills);
    }

    /**
     * dataTable method
     */
    static function getForDataTable()
    {
        $q = Bill::query();

        return $q->get();
    }
}
