<?php

namespace App\Http\Responses\Focus\djc;

use App\Models\djc\Djc;
use App\Models\hrm\Hrm;
use App\Models\lead\Lead;
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
        $last_djc = Djc::orderBy('tid', 'desc')->first();
        return view('focus.djcs.create',compact('leads','last_djc'));
    }
}