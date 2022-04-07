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
        $lastquote = Quote::orderBy('tid', 'desc')->where('bank_id', 0)->first('tid');
        $leads = Lead::where('status', 0)->orderBy('id', 'desc') ->get();
        $words = ['title' => 'Create Quote'];

        // create proforma invoice
        if (request('page') == 'pi') {
            $banks = Bank::all();
            $lastquote = Quote::orderBy('tid', 'desc')->where('bank_id', '>', 0)->first('tid');
            $words['title'] = 'Create Proforma Invoice';

            return view('focus.quotes.create')
                ->with(compact('lastquote','leads', 'words', 'banks'))
                ->with(bill_helper(2, 4));
        }
        // create default quote
        return view('focus.quotes.create')
            ->with(compact('lastquote','leads', 'words'))
            ->with(bill_helper(2, 4));
    }
}
