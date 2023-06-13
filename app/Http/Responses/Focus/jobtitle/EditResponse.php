<?php

namespace App\Http\Responses\Focus\jobtitle;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\jobtitle\JobTitle
     */
    protected $jobtitles;

    /**
     * @param App\Models\jobtitle\jobtitle $jobtitles
     */
    public function __construct($jobtitles)
    {
        $this->jobtitles = $jobtitles;
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
        return view('focus.jobtitle.edit')->with([
            'jobtitles' => $this->jobtitles
        ]);
    }
}