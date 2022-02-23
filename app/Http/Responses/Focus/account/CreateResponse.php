<?php

namespace App\Http\Responses\Focus\account;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;

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
        $account_types = DB::table('account_types')->get(['id', 'name', 'category']);
        
        return view('focus.accounts.create', compact('account_types'));
    }
}
