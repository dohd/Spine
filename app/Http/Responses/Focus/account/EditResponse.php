<?php

namespace App\Http\Responses\Focus\account;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\account\Account
     */
    protected $accounts;

    /**
     * @param App\Models\account\Account $accounts
     */
    public function __construct($accounts)
    {
        $this->accounts = $accounts;
    }

    /**
     * To Response
     *
     * @param \App\Http\Requests\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function toResponse($request)
    {
        $account = $this->accounts;
        $account_types = DB::table('account_types')->get(['id', 'name', 'category']);

        return view('focus.accounts.edit', compact('account_types', 'account'));
    }
}