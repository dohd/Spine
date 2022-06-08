<?php

namespace App\Http\Responses\Focus\equipment;

use App\Models\equipment\Equipment;
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
        $last_tid = Equipment::max('tid');
        return view('focus.equipments.create', compact('last_tid'));
    }
}