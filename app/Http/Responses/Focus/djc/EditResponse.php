<?php

namespace App\Http\Responses\Focus\djc;

use Illuminate\Contracts\Support\Responsable;
use App\Models\djc\Djc;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\productcategory\Productcategory
     */
    protected $djcs;

    /**
     * @param App\Models\productcategory\Productcategory $productcategories
     */
    public function __construct($djcs)
    {
        $this->djcs = $djcs;
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
         $djcs=Djc::all();
        return view('focus.djcs.edit')->with([
            'djcs' => $this->djcs,'djcs'=>$djcs
        ]);
    }
}