<?php

namespace App\Http\Responses\Focus\account;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;
use App\Models\account\Account;

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
        $account_types = DB::table('account_types')->get(['id', 'name', 'category','is_opening_balance','is_multiple']);
        $account_category= Account::where('is_parent','0')->pluck('holder', 'id');
        
        return view('focus.accounts.create', compact('account_types','account_category'));
    }
}
