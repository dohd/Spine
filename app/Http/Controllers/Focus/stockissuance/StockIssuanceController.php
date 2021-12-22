<?php

namespace App\Http\Controllers\Focus\stockissuance;

use App\Http\Controllers\Controller;
use App\Models\quote\Quote;
use Illuminate\Http\Request;

class StockIssuanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $input = $request->only('rel_type', 'rel_id');
        
        $segment = array();
        $words = array();
        if (isset($input['rel_id']) and isset($input['rel_type'])) {
            switch ($input['rel_type']) {
                case 1:
                    $segment = Customer::find($input['rel_id']);
                    $words['name'] = trans('customers.title');
                    $words['name_data'] = $segment->name;
                    break;
                case 2:
                    $segment = Hrm::find($input['rel_id']);
                    $words['name'] = trans('hrms.employee');
                    $words['name_data'] = $segment->first_name . ' ' . $segment->last_name;
                    break;
            }
        }

        return view('focus.stockissuance.index', compact('input', 'segment', 'words'));
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

    // Stock verification data
    static function getForDataTable()
    {
        $q = Quote::query();

        $q->when(request('i_rel_type') == 1, function ($q) {
            return $q->where('customer_id', request('i_rel_id', 0));
        });

        if (request('start_date')) {
            $q->whereBetween('invoicedate', [
                date_for_database(request('start_date')), 
                date_for_database(request('end_date'))
            ]);
        }

        // approved and unveriief quotes
        $q->where(['status' => 'approved', 'verified' => 'No']);

        return $q->get([
            'id', 'notes', 'tid', 'customer_id', 'lead_id', 'invoicedate', 'invoiceduedate', 
            'total', 'status', 'bank_id', 'verified', 'revision', 'lpo_number', 'client_ref'
        ]);
    }
}
