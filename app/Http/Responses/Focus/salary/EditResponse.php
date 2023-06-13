<?php

namespace App\Http\Responses\Focus\salary;

use Illuminate\Contracts\Support\Responsable;
use App\Models\workshift\Workshift;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\salary\salary
     */
    protected $salary;

    /**
     * @param App\Models\salary\salary $salary
     */
    public function __construct($salary)
    {
        $this->salary = $salary;
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
        $workshifts = Workshift::all(['id','name']);
        return view('focus.salary.edit')->with([
            'salary' => $this->salary,
            'workshifts' => $workshifts
        ]);
    }
}