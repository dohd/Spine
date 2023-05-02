<?php

namespace App\Http\Responses\Focus\account;

use Illuminate\Contracts\Support\Responsable;
use App\Models\account\Account;
use App\Models\account\AccountType;

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
        $account_types = AccountType::all();
        $account_categories = Account::where('is_parent', 0)->pluck('holder', 'id');
        
        return view('focus.accounts.create', compact('account_types', 'account_categories'));
    }
}
