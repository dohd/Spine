<?php

namespace App\Http\Responses\Focus\charge;

use Illuminate\Contracts\Support\Responsable;
use App\Models\customer\Customer;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\productcategory\Productcategory
     */
    protected $charge;

    /**
     * @param App\Models\productcategory\Productcategory $productcategories
     */
    public function __construct($charge)
    {
        $this->charge = $charge;
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
         $customers=Customer::all();
        return view('focus.charges.edit')->with([
            'charge' => $this->charge,'customers'=>$customers
        ]);
    }
}