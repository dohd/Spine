<?php

namespace App\Http\Responses\Focus\purchaseorder;

use App\Models\purchaseorder\Purchaseorder;
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

        $last_invoice = Purchaseorder::orderBy('id', 'desc')->where('i_class', 0)->first();

        return view('focus.purchaseorders.create')
            ->with(['last_invoice' => $last_invoice])
            ->with(bill_helper(3, 9));
    }
}
