<?php

namespace App\Http\Responses\Focus\surcharge;

use App\Models\additional\Additional;
use App\Models\pricegroup\Pricegroup;
use App\Models\supplier\Supplier;
use App\Models\surcharge\SurchargeItems;
use App\Models\warehouse\Warehouse;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\surcharge\surcharge
     */
    protected $surcharge;

    /**
     * @param App\Models\surcharge\surcharge $surcharge
     */
    public function __construct($surcharge)
    {
        $this->surcharge = $surcharge;
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
        $surcharge = $this->surcharge;
        
        $surcharge_items = surchargeItems::where('surcharge_id',$surcharge->id)->get();
        //dd($surcharge_items);
        return view('focus.surcharge.edit', compact('surcharge','surcharge_items'));
    }
}
