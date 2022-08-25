<?php

namespace App\Http\Controllers\Focus\lpo;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\branch\Branch;
use App\Models\customer\Customer;
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
        // extract input fields
        $input = $request->only('customer_id', 'branch_id', 'date', 'lpo_no', 'amount', 'remark');

        // check for duplicate lpo number per client
        $lpo_exists = Lpo::where(['customer_id' => $input['customer_id'], 'lpo_no' => $input['lpo_no']])->count();
        if ($lpo_exists) return redirect(route('biller.lpo.index'))->with(['flash_error' => 'Duplicate Customer LPO Number!']);

        $input['date'] = date_for_database($input['date']);
        $input['amount'] = numberClean($input['amount']);
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
        $lpo = Lpo::find($id);
        $customer = Customer::find($lpo->customer_id, ['id', 'name']);
        $branch = Branch::find($lpo->branch_id, ['id', 'name']);

        return response()->json(compact('lpo', 'customer', 'branch'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_lpo(Request $request)
    {
        // extract input fields
        $lpo_id = request('lpo_id');
        $input = $request->only('customer_id', 'branch_id', 'date', 'lpo_no', 'amount', 'remark');

        $input['amount'] = (float) $input['amount'];
        Lpo::find($lpo_id)->update($input);

        return new RedirectResponse(route('biller.lpo.index'), ['flash_success' => 'LPO updated successfully']);
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete_lpo($id)
    {
        $lpo = Lpo::find($id);
        if (count($lpo->quotes))
            return response()->json(['status' => 'Error', 'message' => ' LPO attached to Quote / Proforma Invoice'], 403);
        
        $lpo->delete();        
        return response()->noContent();
    }

    // data for LpoTableController
    static function getForDataTable()
    {
        $q = Lpo::query();
        return $q->get();
    }
}
