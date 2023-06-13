<?php

namespace App\Http\Responses\Focus\overtimepay;

use Illuminate\Contracts\Support\Responsable;
use App\Models\overtimerate\OvertimeRate;

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
        $overtimerates = OvertimeRate::all(['id','name']);
        return view('focus.overtimepay.create', compact('overtimerates'));
    }
}