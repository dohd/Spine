<?php

namespace App\Http\Responses\Focus\banktransfer;

use App\Models\account\Account;
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
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid');
        $accounts = Account::whereHas('accountType', fn($q) =>  $q->where('system', 'bank'))->get();
        
        return view('focus.banktransfers.create', compact('tid', 'accounts'));;
    }
}
