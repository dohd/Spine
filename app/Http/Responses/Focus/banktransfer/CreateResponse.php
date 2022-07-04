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
        $last_tid = Transaction::max('tid');
        $accounts = Account::whereHas('accountType', function ($q) {
            $q->where('system', 'bank');
        })->get();
        
        return view('focus.banktransfers.create', compact('last_tid', 'accounts'));;
    }
}
