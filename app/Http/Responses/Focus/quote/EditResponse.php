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
        $leads = $this->quote->lead->get();

        // edit proformer invoice
        if ($this->quote->bank_id ) {
            $banks = Bank::all();
            
            return view('focus.quotes.edit_pi')
                ->with(compact('quote', 'leads', 'banks'))
                ->with(bill_helper(2, 4));
        }

        return view('focus.quotes.edit')
            ->with(compact('quote','leads'))
            ->with(bill_helper(2, 4));
    }
}
