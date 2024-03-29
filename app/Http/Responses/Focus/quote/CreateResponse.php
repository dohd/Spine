<?php

namespace App\Http\Responses\Focus\quote;
use App\Models\quote\Quote;
use Illuminate\Contracts\Support\Responsable;
use App\Models\lead\Lead;

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

         $last_invoice=Quote::orderBy('id', 'desc')->where('i_class','=',0)->first();

         $leads=Lead::all();
        return view('focus.quotes.create')->with(array('last_invoice'=>$last_invoice,'leads'=>$leads))->with(bill_helper(2,4));
    }
}