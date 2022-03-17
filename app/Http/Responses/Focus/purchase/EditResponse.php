<?php

namespace App\Http\Responses\Focus\purchase;

use App\Models\additional\Additional;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\purchaseorder\Purchaseorder
     */
    protected $purchase;

    /**
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorders
     */
    public function __construct($purchase)
    {
        $this->purchase = $purchase;
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
        $purchase = $this->purchase;
        $additionals = Additional::all();

        return view('focus.purchases.edit', compact('purchase', 'additionals'));
    }
}
