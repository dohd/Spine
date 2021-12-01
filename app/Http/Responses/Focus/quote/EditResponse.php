<?php

namespace App\Http\Responses\Focus\quote;

use App\Models\bank\Bank;
use App\Models\customfield\Customfield;
use App\Models\items\CustomEntry;
use App\Models\lead\Lead;
use Illuminate\Contracts\Support\Responsable;

use function GuzzleHttp\json_encode;

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
        $leads = Lead::all();

        // default parameters
        $params = array('quote', 'products', 'leads');
        if ($quote->bank_id ) $banks = Bank::all();
        
        // condition to access copy page
        if (request('page') == 'copy') {
            $last_quote = $quote->orderBy('id', 'desc')->where('i_class', '=', 0)->first();
            // copy proforma invoice
            if (isset($banks)) {
                return view('focus.quotes.edit_pi')
                    ->with(compact('banks', 'last_quote', ...$params))
                    ->with(bill_helper(2, 4));
            }
            // copy default quote
            return view('focus.quotes.edit')
                ->with(compact('last_quote', ...$params))
                ->with(bill_helper(2, 4));
        }

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
