<?php

namespace App\Http\Responses\Focus\benefit;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\benefit\benefit
     */
    protected $benefits;

    /**
     * @param App\Models\benefit\benefit $benefits
     */
    public function __construct($benefits)
    {
        $this->benefits = $benefits;
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
        return view('focus.benefit.edit')->with([
            'benefits' => $this->benefits
        ]);
    }
}