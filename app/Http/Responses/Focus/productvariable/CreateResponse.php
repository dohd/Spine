<?php

namespace App\Http\Responses\Focus\productvariable;

use App\Models\productvariable\Productvariable;
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
        $categories = Productvariable::select('category')->distinct()->pluck('category')->toArray();
      
        return view('focus.productvariables.create')->with(compact('categories'));
    }
}