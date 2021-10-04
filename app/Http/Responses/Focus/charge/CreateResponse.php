<?php

namespace App\Http\Responses\Focus\charge;

use App\Models\transaction\Transaction;
use Illuminate\Contracts\Support\Responsable;

class CreateResponse implements Responsable
{
    /**
     * To Response
     *
     * @param \App\Http\Requests\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function toResponse($request)
    {
         // $customers=Customer::all();
           $last_id=Transaction::orderBy('id', 'desc')->first();
        return view('focus.charges.create')->with(array('last_id'=>$last_id))->with(bill_helper(3,9));;
    }
}