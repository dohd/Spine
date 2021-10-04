<?php

namespace App\Http\Responses\Focus\assetequipment;

use App\Models\assetequipment\Assetequipment;
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
     
         $assetequipments=Assetequipment::all();
         
        return view('focus.assetequipments.create',compact('assetequipments'));

    }
}