<?php

namespace App\Http\Responses\Focus\lead;

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
        $lead = Lead::orderBy('reference', 'desc')->first('reference');
        $reference = $lead->reference + 1;

        return view('focus.leads.create', compact('reference'));
    }
}
