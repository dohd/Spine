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
          $equipment=Equipment::all();
          $last_id=Equipment::orderBy('id', 'desc')->first();
        return view('focus.equipments.create',compact('equipment','last_id'));
    }
}