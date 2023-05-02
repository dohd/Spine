<?php

namespace App\Http\Responses\Focus\overtimerate;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\overtimerate\overtimerate
     */
    protected $overtimerates;

    /**
     * @param App\Models\overtimerate\overtimerate $overtimerates
     */
    public function __construct($overtimerates)
    {
        $this->overtimerates = $overtimerates;
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
        return view('focus.overtimerate.edit')->with([
            'overtimerates' => $this->overtimerates
        ]);
    }
}