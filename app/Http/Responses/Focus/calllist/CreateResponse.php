<?php

namespace App\Http\Responses\Focus\calllist;

use App\Models\prospect\Prospect;

use App\Models\calllist\CallList;
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
        
        $direct = Prospect::get(['id','title','company/name','category'])->where('category','direct');  
        $excel = Prospect::get(['id','title','company/name','category'])->where('category','excel')->unique('title');  
        
        return view('focus.prospects.calllist.create', compact('direct','excel'));
    }
}
