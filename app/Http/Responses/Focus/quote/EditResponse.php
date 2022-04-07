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
        // open leads (status = 0)
        $leads = Lead::where('status', 0)->orderBy('id', 'DESC')->get();
        $banks = Bank::all();
        $lastquote = $quote->orderBy('id', 'desc')->where('bank_id', 0)->first('tid');
        $lastpi = $quote->orderBy('id', 'desc')->where('bank_id', '>', 0)->first('tid');
        
        // copy page 
        if (request('page') == 'copy') {
            // copy pi
            if ($quote->bank_id) {
                $lastquote = $lastpi;

                return view('focus.quotes.edit_pi')
                    ->with(compact('banks', 'lastquote', 'quote', 'leads'))
                    ->with(bill_helper(2, 4));
            }

            // copy quote
            return view('focus.quotes.edit')
                ->with(compact('lastquote', 'quote', 'leads'))
                ->with(bill_helper(2, 4));
        }

        // copy quote to pi page
        if (request('page') == 'copy_to_pi') {
            $lastquote = $lastpi;

            return view('focus.quotes.edit_pi')
                ->with(compact('banks', 'lastquote', 'quote', 'leads'))
                ->with(bill_helper(2, 4));
        }

        // copy pi to quote page
        if (request('page') == 'copy_to_qt') {
            $copy_from_pi = true;

            return view('focus.quotes.edit')
                ->with(compact('lastquote', 'copy_from_pi', 'quote', 'leads'))
                ->with(bill_helper(2, 4));        
        }

        // append previous lead when editing
        $leads[] = Lead::find($quote->lead_id);

        // edit proforma invoice
        if ($quote->bank_id) {    
            $words['title'] = 'Edit Proforma Invoice';
       
            return view('focus.quotes.edit')
                ->with(compact('banks', 'leads', 'quote', 'lastquote', 'words'))
                ->with(bill_helper(2, 4));
        }
        // edit quote
        $words['title'] = 'Edit Quote';
        return view('focus.quotes.edit')
            ->with(compact('leads', 'quote', 'lastquote', 'words'))
            ->with(bill_helper(2, 4));
    }
}
