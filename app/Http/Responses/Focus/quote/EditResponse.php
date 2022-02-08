<?php

namespace App\Http\Responses\Focus\quote;

use App\Models\bank\Bank;
use App\Models\lead\Lead;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\quote\Quote
     */
    protected $quote;

    /**
     * @param App\Models\quote\Quote $quote
     */
    public function __construct($quote)
    {
        $this->quote = $quote;
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
        $quote = $this->quote;
        $products = $quote->products()->orderBy('row_index')->get();

        // open leads (status = 0)
        $leads = Lead::where('status', 0)->orderBy('id', 'desc')->get();

        // default parameters
        $params = array('quote', 'products', 'leads');
        if ($quote->bank_id ) $banks = Bank::all();
        
        // copy page 
        if (request('page') == 'copy') {
            // copy proforma invoice
            if (isset($banks)) {
                $last_quote = $quote->orderBy('id', 'desc')->where('bank_id', '>', 0)->first('tid');

                return view('focus.quotes.edit_pi')
                    ->with(compact('banks', 'last_quote', ...$params))
                    ->with(bill_helper(2, 4));
            }

            // copy quote
            $last_quote = $quote->orderBy('id', 'desc')->where('bank_id', 0)->first('tid');
            return view('focus.quotes.edit')
                ->with(compact('last_quote', ...$params))
                ->with(bill_helper(2, 4));
        }

        // copy quote to pi page
        if (request('page') == 'copy_to_pi') {
            $last_quote = $quote->orderBy('id', 'desc')->where('bank_id', '>', 0)->first('tid');
            $banks = Bank::all();

            return view('focus.quotes.edit_pi')
                ->with(compact('banks', 'last_quote', ...$params))
                ->with(bill_helper(2, 4));
        }

        // copy pi to quote page
        if (request('page') == 'copy_to_qt') {
            $last_quote = $quote->orderBy('id', 'desc')->where('bank_id', 0)->first('tid');
            $copy_from_pi = true;

            return view('focus.quotes.edit')
                ->with(compact('last_quote', 'copy_from_pi', ...$params))
                ->with(bill_helper(2, 4));        
        }

        // append previous lead when editing
        $leads[] = Lead::find($quote->lead_id);

        // edit proforma invoice
        if (isset($banks)) {            
            return view('focus.quotes.edit_pi')
                ->with(compact('banks', ...$params))
                ->with(bill_helper(2, 4));
        }
        // edit quote
        return view('focus.quotes.edit')
            ->with(compact(...$params))
            ->with(bill_helper(2, 4));
    }
}
