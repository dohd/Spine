<?php

namespace App\Http\Responses\Focus\quote;

use App\Models\bank\Bank;
use App\Models\quote\Quote;
use Illuminate\Contracts\Support\Responsable;
use App\Models\lead\Lead;

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
        $last_quote = Quote::orderBy('tid', 'desc')->where('bank_id', 0)->first('tid');
        $leads = Lead::where('status', 0)->orderBy('id', 'desc') ->get();
        
        // create proforma invoice
        if (request('page') == 'pi') {
            $banks = Bank::all();
            $last_quote = Quote::orderBy('tid', 'desc')->where('bank_id', '>', 0)->first('tid');
            
            return view('focus.quotes.create_pi')
                ->with(compact('last_quote', 'leads', 'banks'))
                ->with(bill_helper(2, 4));
        }
        // create default quote
        return view('focus.quotes.create')
            ->with(compact('last_quote','leads'))
            ->with(bill_helper(2, 4));
    }
}
