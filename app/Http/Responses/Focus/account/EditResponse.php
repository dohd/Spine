<?php

namespace App\Http\Responses\Focus\account;

use App\Models\account\Account;
use App\Models\account\AccountType;
use Illuminate\Contracts\Support\Responsable;

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
        $account_types = AccountType::all();
        $account_categories = Account::where('is_parent', 0)->pluck('holder', 'id');

        return view('focus.accounts.edit', compact('account_types', 'account', 'account_categories'));
    }
}