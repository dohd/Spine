<?php

namespace App\Http\Responses\Focus\hrm;

use App\Models\Access\Permission\Permission;
use App\Models\Access\Permission\PermissionUser;
use App\Models\Access\Role\Role;
use App\Models\department\Department;
use App\Models\hrm\HrmMeta;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\hrm\Hrm
     */
    protected $hrms;

    /**
     * @param App\Models\hrm\Hrm $hrms
     */
    public function __construct($hrms)
    {
        $this->hrms = $hrms;
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
        $roles=Role::where('status','<',1)->where(function ($query) {
        $query->where('ins', '=', auth()->user()->ins)->orWhereNull('ins');})->get();
        $hrm_metadata = $this->hrms->meta->toArray();
        $hrms_mod = collect([$this->hrms->toArray()])->map(function ($v) use($hrm_metadata) {
            return array_merge(array_diff_key($v, array_flip(['meta'])), $hrm_metadata);
        })->first();
        $hrms = $this->hrms->fill($hrms_mod);
         $departments = Department::all()->pluck('name','id');
         $general['create']=$this->hrms['id'];
            $emp_role=$this->hrms->role['id'];
        $permissions_all=Permission::whereHas('roles',function ($q) use ($emp_role){
            return $q->where('role_id','=',$emp_role);
        })->get()->toArray();
        $permissions=PermissionUser::all()->keyBy('id')->where('user_id','=',$general['create'])->toArray();
        $last_tid=$hrms->employee_no;
        return view('focus.hrms.edit',compact('hrms','roles','general','permissions_all','permissions','departments','last_tid'));

    }
}