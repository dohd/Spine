<?php

namespace App\Http\Responses\Focus\purchase;

use App\Models\purchase\Purchase;
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
        $last_id = Purchase::orderBy('id', 'desc')->first();

        return view('focus.purchases.create', compact('last_id'))->with(bill_helper(3, 9));
    }
}
