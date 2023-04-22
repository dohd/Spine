<?php

namespace App\Http\Responses\Focus\employee_branch;

use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\employee_branch\employee_branch
     */
    protected $employee_branch;

    /**
     * @param App\Models\employee_branch\employee_branch $employee_branchs
     */
    public function __construct($employee_branch)
    {
        $this->employee_branch = $employee_branch;
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
        return view('focus.employee_branch.edit')->with([
            'employee_branch' => $this->employee_branch
        ]);
    }
}