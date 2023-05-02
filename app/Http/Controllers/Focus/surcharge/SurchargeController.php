<?php

namespace App\Http\Controllers\Focus\surcharge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\hrm\Hrm;
use App\Models\assetissuance\Assetissuance;
use App\Models\assetissuance\AssetissuanceItems;

class SurchargeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('focus.surcharge.index');
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
    public function select(Request $request)
    {
        $q = $request->keyword;
        $users = Hrm::where('first_name', 'LIKE', '%'.$q.'%')
            ->orWhere('email', 'LIKE', '%'.$q.'')
            ->limit(6)->get(['id', 'first_name', 'email']);

        return response()->json($users);
    }
    public function get_issuance(Request $request)
    {
        $q = $request->employee_id;
        $surcharge_value = $request->value;
        if($surcharge_value == '1'){
            $assetissuance = Assetissuance::where('employee_id', $q)->get()->sum('total_cost');
            $name = 'Lost/Broken Items';
            // foreach ($assetissuance as $key => $value) {
            //     //dd($value->id);
            //     $assetissuanceItems = AssetissuanceItems::all();
            //     //return response()->json($value->item());
            // }
            //dd($assetissuance->item());
            return response()->json(['cost'=>$assetissuance, 'name'=>$name]);

            
        }

        return response()->json($assetissuance->item());
    }
}
