<?php

namespace App\Http\Responses\Focus\salary;

use Illuminate\Contracts\Support\Responsable;
use App\Models\department\Department;
use App\Models\workshift\Workshift;

class CreateResponse implements Responsable
{
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
        return view('focus.salary.create', compact('workshifts'));
    }
}