<?php

namespace App\Http\Responses\Focus\quote;

use App\Models\additional\Additional;
use App\Models\bank\Bank;
use App\Models\customer\Customer;
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
        $words = ['title' => 'Repair Quote'];
        if (request('doc_type') == 'maintenance') 
            $words['title'] = 'Maintenance Quote';
        $lastquote = Quote::orderBy('tid', 'desc')->where('bank_id', 0)->first('tid') ?: new Quote;
        $leads = Lead::where('status', 0)->orderBy('id', 'desc')->get();
        $additionals = Additional::all();
        $price_customers = Customer::whereHas('products')->get(['id', 'company']);
        
        $common_params = ['lastquote','leads', 'words', 'additionals', 'price_customers'];

        // create proforma invoice
        if (request('page') == 'pi') {
            $banks = Bank::all();
            $lastquote = Quote::orderBy('tid', 'desc')->where('bank_id', '>', 0)->first('tid') ?: new Quote;
            $words['title'] = 'Repair Proforma Invoice';
            if (request('doc_type') == 'maintenance') 
                $words['title'] = 'Maintenance Proforma Invoice';

            return view('focus.quotes.create', compact('banks', ...$common_params))
                ->with(bill_helper(2, 4));
        }
        // create quote
        return view('focus.quotes.create', compact(...$common_params))
            ->with(bill_helper(2, 4));
    }
}
