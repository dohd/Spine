<?php

namespace App\Http\Responses\Focus\withholding;

use App\Models\withholding\Withholding;
use DB;
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
        $last_tid = Withholding::where('ins', auth()->user()->ins)->max('tid');
        $withholdings = Withholding::where('certificate', 'tax')->whereColumn('amount', '>', 'allocate_ttl')->get();
        
        return view('focus.withholdings.create', compact('last_tid', 'withholdings'));
    }
}
