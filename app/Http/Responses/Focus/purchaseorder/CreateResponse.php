<?php

namespace App\Http\Responses\Focus\purchaseorder;

use App\Models\additional\Additional;
use App\Models\purchaseorder\Purchaseorder;
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
        $last_order = Purchaseorder::orderBy('tid', 'DESC')->first(['tid']);
        $additionals = Additional::all();

        return view('focus.purchaseorders.create', compact('last_order', 'additionals'));
    }
}
