<?php

namespace App\Http\Responses\Focus\lead;

use App\Models\lead\Lead;
use App\Models\hrm\Hrm;
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
          $leads=Lead::all();
          $employees = Hrm::all();
          $last_lead = Lead::orderBy('reference', 'desc')->first();
        return view('focus.leads.create',compact('leads','employees','last_lead'));
    }
}