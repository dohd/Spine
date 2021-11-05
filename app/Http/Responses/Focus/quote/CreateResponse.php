<?php

namespace App\Http\Responses\Focus\quote;

use App\Models\bank\Bank;
use App\Models\quote\Quote;
use Illuminate\Contracts\Support\Responsable;
use App\Models\lead\Lead;

class CreateResponse implements Responsable
{
    protected $page;

    /**
     * @param string $quote
     */
    public function __construct($page)
    {
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
        $last_invoice = Quote::orderBy('id', 'desc')->where('i_class', '=', 0)->first();
        $leads = Lead::all();

        // create proformer invoice
        if ($this->page == 'pi') {
            $banks = Bank::all();
            
            return view('focus.quotes.create_pi')
                ->with(compact('last_invoice', 'leads', 'banks'))
                ->with(bill_helper(2, 4));
        }

        return view('focus.quotes.create')
            ->with(compact('last_invoice','leads'))
            ->with(bill_helper(2, 4));
    }
}
