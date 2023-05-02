<?php

namespace App\Http\Responses\Focus\supplier;

use App\Models\account\Account;
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
        $accounts = Account::where('account_type', 'Expense')->get(['id', 'holder']);
        

        return view('focus.suppliers.create', compact('accounts'));
    }
}