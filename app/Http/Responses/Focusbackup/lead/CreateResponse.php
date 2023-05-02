<?php

namespace App\Http\Responses\Focus\lead;

use App\Models\branch\Branch;
use App\Models\customer\Customer;
use App\Models\lead\Lead;
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
        $ins = auth()->user()->ins;
        $tid = Lead::where('ins', $ins)->max('reference');
        $prefixes = prefixesArray(['lead'], $ins);

        $customers = Customer::get(['id', 'company']);
        $branches = Branch::get(['id', 'name', 'customer_id']);
    
        return view('focus.leads.create', compact('tid', 'customers', 'branches', 'prefixes'));
    }
}
