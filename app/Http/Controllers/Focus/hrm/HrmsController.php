<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

namespace App\Http\Controllers\Focus\hrm;

use App\Http\Requests\Focus\department\ManageDepartmentRequest;
use App\Models\Access\Permission\Permission;
use App\Models\Access\Permission\PermissionRole;
use App\Models\Access\Permission\PermissionUser;
use App\Models\account\Account;
use App\Models\Company\ConfigMeta;
use App\Models\employee\RoleUser;
use App\Models\hrm\Attendance;
use App\Models\hrm\Hrm;
use App\Models\transactioncategory\Transactioncategory;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\hrm\CreateResponse;
use App\Http\Responses\Focus\hrm\EditResponse;
use App\Repositories\Focus\hrm\HrmRepository;
use App\Http\Requests\Focus\hrm\ManageHrmRequest;
use Illuminate\Validation\ValidationException;

/**
 * HrmsController
 */
class HrmsController extends Controller
{
    /**
     * variable to store the repository object
     * @var HrmRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param HrmRepository $repository ;
     */
    public function __construct(HrmRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\hrm\ManageHrmRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageHrmRequest $request)
    {
        $title = trans('labels.backend.hrms.management');
        $flag = true;
        if (request('rel_type') == 3) {
            $title = trans('hrms.payroll');
            $flag = false;
        }

        // $users = \App\Models\hrm\Hrm::where('id', '!=', 1)->get();
        // foreach ($users as $user) {
        //     if ($user->email) {
        //         $init = explode('@', $user->email);
        //         if (@$init[0]) {
        //             $user->password = $init[0];
        //             $user->save();
        //         }               
        //     }
        // }

        return new ViewResponse('focus.hrms.index', compact('title', 'flag'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateHrmRequestNamespace $request
     * @return \App\Http\Responses\Focus\hrm\CreateResponse
     */
    public function create(ManageHrmRequest $request)
    {
        return new CreateResponse('focus.hrms.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreHrmRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(ManageHrmRequest $request)
    {
        $input['employee'] = $request->only(['first_name', 'last_name', 'email', 'picture', 'signature', 'role']);
        $input['meta'] = $request->except(['_token', 'first_name', 'last_name', 'email', 'picture', 'signature', 'role', 'permission', 'check_all']);
        $input = array_merge($input, $request->only(['permission']));

        // validate
        foreach ($input as $key => $val) {
            if ($key == 'employee') {
                if (isset($val['picture'])) $request->validate(['picture' => 'required|mimes:jpeg,png']);
                if (isset($val['signature'])) $request->validate(['signature' => 'required|mimes:jpeg,png']);
            }
            if ($key == 'meta') {
                if (isset($val['id_front'])) $request->validate(['id_front' => 'required|mimes:jpeg,png']);
                if (isset($val['id_back'])) $request->validate(['id_front' => 'required|mimes:jpeg,png']);
            }
        }

        $input['employee']['ins'] = auth()->user()->ins;

        try {
            $this->repository->create($input);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Creating Employee', $th);
        }

        return new RedirectResponse(route('biller.hrms.index'), ['flash_success' => trans('alerts.backend.hrms.created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\hrm\Hrm $hrm
     * @param EditHrmRequestNamespace $request
     * @return \App\Http\Responses\Focus\hrm\EditResponse
     */
    public function edit(Hrm $hrm, ManageHrmRequest $request)
    {
        return new EditResponse($hrm);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateHrmRequestNamespace $request
     * @param Hrm $hrm
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(ManageHrmRequest $request, Hrm $hrm)
    {
        $input['employee'] = $request->only(['first_name', 'last_name', 'email', 'picture', 'signature', 'role']);
        $input['meta'] = $request->except(['_token', '_method', 'first_name', 'last_name', 'email', 'picture', 'signature', 'role', 'permission', 'check_all']);
        $input = array_merge($input, $request->only(['permission']));

        // validate
        foreach ($input as $key => $val) {
            if ($key == 'employee') {
                if (isset($val['picture'])) $request->validate(['picture' => 'required|mimes:jpeg,png']);
                if (isset($val['signature'])) $request->validate(['signature' => 'required|mimes:jpeg,png']);
            }
            if ($key == 'meta') {
                if (isset($val['id_front'])) $request->validate(['id_front' => 'required|mimes:jpeg,png']);
                if (isset($val['id_back'])) $request->validate(['id_front' => 'required|mimes:jpeg,png']);
            }
        }

        $input['employee']['ins'] = auth()->user()->ins;

        try {
            $this->repository->update($hrm, $input);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Updating Employee', $th);
        }

        return new RedirectResponse(route('biller.hrms.index'), ['flash_success' => trans('alerts.backend.hrms.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteHrmRequestNamespace $request
     * @param \App\Models\hrm\Hrm $hrm
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Hrm $hrm)
    {
        try {
            $this->repository->delete($hrm);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Employee', $th);
        }
        
        return new RedirectResponse(route('biller.hrms.index'), ['flash_success' => trans('alerts.backend.hrms.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteHrmRequestNamespace $request
     * @param App\Models\hrm\Hrm $hrm
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Hrm $hrm, ManageHrmRequest $request)
    {
        $emp_role = $hrm->role['id'];
        $permissions_all = Permission::orderBy('display_name', 'asc')->whereHas('roles', function ($q) use ($emp_role) {
            return $q->where('role_id', '=', $emp_role);
        })->get()->toArray();
        $permissions = PermissionUser::all()->keyBy('id')->where('user_id', '=', $hrm->id)->toArray();

        // $rolePermissions =PermissionRole::all()->keyBy('id')->where('role_id','=',$emp_role)->toArray();

        //returning with successfull message
        return new ViewResponse('focus.hrms.view', compact('hrm', 'permissions', 'permissions_all'));
    }

    /**
     * Update Permission from Hrm Employee Permission Tab
     */
    public function set_permission(ManageHrmRequest $request)
    {
        // dd($request->all());
        $user_role = RoleUser::where('user_id', $request->user_id)->first();
        if ($user_role) {
            $role_permission = PermissionRole::where('role_id', $user_role->role_id)
                ->where('permission_id', $request->permission_id)->first();
            if ($role_permission) {
                $data = ['permission_id' => $request->permission_id, 'user_id' => $request->user_id,];
                if ($request->is_checked) {
                    // create permission
                    $permission_user = new PermissionUser;
                    foreach ($data as $key => $val) {
                        $permission_user[$key] = $val;
                    }
                    $permission_user->save();
                } elseif ($user_role->role_id != 2) {
                    // delete permission (non-business owner)
                    PermissionUser::where($data)->delete();
                }
            }
        }
    }

    public function payroll(ManageDepartmentRequest $request)
    {
        $accounts = Account::all();
        $transaction_categories = Transactioncategory::all();
        $payroll = true;
        $dual_entry = ConfigMeta::withoutGlobalScopes()->where('feature_id', '=', 13)->first('feature_value');
        return view('focus.transactions.create', compact('accounts', 'transaction_categories', 'payroll', 'dual_entry'));
    }


    public function attendance(ManageDepartmentRequest $request)
    {
        $payroll = true;
        return view('focus.hrms.attendance', compact('payroll'));
    }

    public function attendance_store(ManageDepartmentRequest $request)
    {
        // dd($request);
        $user = Hrm::find($request->payer_id);
        $present = date_for_database($request->att_date);
        $act_h = (strtotime($request->time_from) - strtotime($request->time_to)) / 3600;
        if ($user->id) Attendance::create(array('user_id' => $user->id, 'present' => $present, 't_from' => $request->time_from, 't_to' => $request->time_to, 'note' => $request->note, 'actual_hours' => $act_h, 'ins' => $user->ins));
        return new RedirectResponse(route('biller.hrms.attendance'), ['flash_success' => trans('hrms.attendance_recorded')]);
    }

    public function attendance_list(ManageDepartmentRequest $request)
    {
        $payroll = true;
        return view('focus.hrms.attendance_list', compact('payroll'));
    }


    public function attendance_destroy(ManageDepartmentRequest $request)
    {
        //Calling the delete method on repository
        Attendance::where('id', '=', $request->object_id)->delete();
        return json_encode(array('status' => 'Success', 'message' => trans('general.delete_success'), 't_type' => 1, 'meta' => $request->object_id));
    }


    public function related_permission(ManageHrmRequest $request)
    {
        $emp_role = $request->post('rid');
        $create = $request->post('create');
        $permissions = '';
        $permissions_all = \App\Models\Access\Permission\Permission::orWhereHas('roles', function ($q) use ($emp_role) {
            return $q->where('role_id', '=', $emp_role);
        })->get()->toArray();
        if ($create > 1) $permissions = \App\Models\Access\Permission\PermissionUser::all()->keyBy('id')->where('user_id', '=', $create)->toArray();
        return view('focus.hrms.partials.permissions')->with(compact('permissions_all', 'create', 'permissions'));
    }


    public function role_permission(ManageHrmRequest $request)
    {
        $emp_role = $request->post('rid');
        $create = $request->post('create');

        $permissions_all = \App\Models\Access\Permission\Permission::orWhereHas('roles', function ($q) use ($emp_role) {
            return $q->where('role_id', '=', $emp_role);
        })->get()->toArray();

        $permissions = [];
        if ($create) $permissions = \App\Models\Access\Permission\PermissionUser::all()->keyBy('id')->where('user_id', '=', $create)->toArray();

        return view('focus.hrms.partials.role_permissions')->with(compact('permissions_all', 'create', 'permissions'));
    }


    public function active(ManageHrmRequest $request)
    {
        $cid = $request->post('cid');
        $active = $request->post('active');
        $active = !(bool)$active;
        if ($cid != auth()->user()->id) {
            \App\Models\hrm\Hrm::where('id', '=', $cid)->update(array('status' => $active));
        }
    }


    public function admin_permissions(ManageHrmRequest $request)
    {
        $emp_role = $request->post('rid');
        $create = $request->post('create');
        $permissions = '';
        $permissions_all = \App\Models\Access\Permission\Permission::orWhereHas('roles', function ($q) use ($emp_role) {
            return $q->where('role_id', '=', $emp_role);
        })->get()->toArray();
        if ($create) $permissions = \App\Models\Access\Permission\PermissionUser::all()->keyBy('id')->where('user_id', '=', $create)->toArray();
        return view('focus.hrms.partials.admin_permissions')->with(compact('permissions_all', 'create', 'permissions'));
    }
}
