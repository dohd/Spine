<?php

namespace App\Http\Responses\Focus\purchase;

use App\Models\additional\Additional;
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
        $last_tid = Purchase::where('is_po', 0)->orderBy('tid', 'desc')->first(['tid']);
        $additionals = Additional::all();

        return view('focus.purchases.create', compact('last_tid', 'additionals'));
    }
}
