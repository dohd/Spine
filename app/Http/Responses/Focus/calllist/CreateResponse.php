<?php

namespace App\Http\Responses\Focus\calllist;

use App\Models\branch\Branch;
use App\Models\customer\Customer;
use App\Models\calllist\Prospect;
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
        // $ins = auth()->user()->ins;
       
        // $prefixes = prefixesArray(['calllist'], $ins);
        $branches = Branch::get();
    
        return view('focus.prospects.calllist.create', compact('branches'));
    }
}
