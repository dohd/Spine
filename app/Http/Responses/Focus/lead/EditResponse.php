<?php

namespace App\Http\Responses\Focus\lead;

use Illuminate\Contracts\Support\Responsable;
use App\Models\lead\Lead;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\productcategory\Productcategory
     */
    protected $leads;

    /**
     * @param App\Models\productcategory\Productcategory $productcategories
     */
    public function __construct($leads)
    {
        $this->branches = $leads;
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
         $leads=Lead::all();
        return view('focus.leads.edit')->with([
            'leads' => $this->leads,'leads'=>$leads
        ]);
    }
}