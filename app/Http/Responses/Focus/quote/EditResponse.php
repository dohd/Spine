<?php

namespace App\Http\Responses\Focus\quote;

use App\Models\bank\Bank;
use App\Models\customfield\Customfield;
use App\Models\items\CustomEntry;
use App\Models\lead\Lead;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\quote\Quote
     */
    protected $quote;

    /**
     * @var string 
     */
    protected $page;

    /**
     * @param App\Models\quote\Quote $quote
     */
    public function __construct($quote, $page)
    {
        $this->quote = $quote;
        $this->page = $page;
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
        $leads = $this->quote->lead->where('status', 0)->get();
        $products = $this->quote->products()->orderBy('row_index', 'ASC')->get();
        
        // default parameters
        $params = array('quote', 'products', 'leads');
        if ($this->quote->bank_id ) {
            $banks = Bank::all();
        }

        // condition to access copy page
        if ($this->page == 'copy') {
            $last_quote = $this->quote->orderBy('id', 'desc')->where('i_class', '=', 0)->first();
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
