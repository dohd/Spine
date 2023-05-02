<?php

namespace App\Http\Responses\Focus\charge;

use App\Models\account\Account;
use App\Models\charge\Charge;
use App\Models\transaction\Transaction;
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
        $last_charge = Charge::orderBy('id', 'desc')->first(['tid']);
        $accounts = Account::whereIn('account_type_id', [4, 6])->get(['id', 'holder', 'number', 'account_type_id']);
        $payment_modes = ['Cash', 'Bank Transfer', 'Cheque', 'Mpesa', 'Card' ];
            
        return view('focus.charges.create', compact('last_charge', 'accounts', 'payment_modes'));
    }
}
