<?php

namespace App\Http\Responses\Focus\purchaseorder;

use App\Models\additional\Additional;
use App\Models\pricegroup\Pricegroup;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\purchaseorder\Purchaseorder
     */
    protected $purchaseorder;

    /**
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     */
    public function __construct($purchaseorder)
    {
        $this->purchaseorder = $purchaseorder;
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
        $po = $this->purchaseorder;
        $additionals = Additional::all();
        $pricegroups = Pricegroup::all();
        // Purchase order
        $terms = Term::where('type', 4)->get();

        return view('focus.purchaseorders.edit', compact('po', 'additionals', 'pricegroups', 'terms'));
    }
}