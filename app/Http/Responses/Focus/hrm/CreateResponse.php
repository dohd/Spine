<?php

namespace App\Http\Responses\Focus\hrm;

use App\Models\Access\Role\Role;
use App\Models\department\Department;
use App\Models\hrm\HrmMeta;
use Illuminate\Contracts\Support\Responsable;

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
        $roles=Role::where('status','<',1)->where(function ($q) {
            $q->where('ins', auth()->user()->ins)->orWhereNull('ins');
        })->get();

        $departments = Department::all()->pluck('name','id');
        $last_tid = HrmMeta::max('employee_no') + 1;
        $general['create'] = 1;

        return view('focus.hrms.create', compact('roles','general','departments','last_tid'));
    }
}