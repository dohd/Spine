<?php

namespace App\Repositories\Focus\hrm;

use App\Models\Access\Permission\PermissionUser;
use App\Models\Access\Role\Role;
use App\Models\Access\User\UserProfile;
use App\Models\employee\RoleUser;
use App\Models\hrm\HrmMeta;
use DB;
use App\Models\hrm\Hrm;
use App\Exceptions\GeneralException;
use App\Models\attendance\Attendance;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Storage;
use App\Utils\MessageUtil;
use Illuminate\Support\Str;
use App\Repositories\Focus\general\RosemailerRepository;
use Illuminate\Validation\ValidationException;

/**
 * Class HrmRepository.
 */
class HrmRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */


    const MODEL = Hrm::class;
    protected $file_picture_path;
    protected $file_sign_path;
    protected $storage;
    protected $messageUtil;

    /**
     * Constructor.
     */
    public function __construct(MessageUtil $messageUtil)
    {
        $this->file_picture_path = 'img' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR;
        $this->file_sign_path = 'img' . DIRECTORY_SEPARATOR . 'signs' . DIRECTORY_SEPARATOR;
        $this->storage = Storage::disk('public');
        $this->messageUtil = $messageUtil;
    }

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query()->where('first_name', 'NOT LIKE', '%Admin%')->where('last_name', 'NOT LIKE', '%Admin%');
        
        if (request('rel_type') == 2 and request('rel_id')) {
            $q->whereHas('meta', function ($s) {
                return $s->where('department_id', '=', request('rel_id', 0));
            });
        }

        return $q->with(['monthlysalary'])->get(['id', 'email', 'picture', 'first_name', 'last_name', 'status', 'created_at']);
    }

    /**
     * Get Attendance Data
     */
    public function getForAttendanceDataTable()
    {
        $q = Attendance::query();

        $q->when(request('rel_id'), function ($q) {
            $q->where('user_id', request('rel_id'));
        });

        return $q->get();
    }


    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @return bool
     * @throws GeneralException
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if ($key == 'employee') {
                if (isset($val['picture'])) 
                    $input[$key]['picture'] = $this->uploadPicture($val['picture'], $this->file_picture_path);
                if (isset($val['signature'])) 
                    $input[$key]['signature'] = $this->uploadPicture($val['signature'], $this->file_sign_path);
            }
            if ($key == 'meta') {
                if (isset($val['id_front'])) 
                    $input[$key]['id_front'] = $this->uploadPicture($val['id_front'], $this->file_sign_path);
                if (isset($val['id_back'])) 
                    $input[$key]['id_back'] = $this->uploadPicture($val['id_back'], $this->file_sign_path);
            }
        }

        if (@$input['meta']['employee_no']) {
            $employee_exists = HrmMeta::where('employee_no', 'LIKE', "%{$input['meta']['employee_no']}%")->exists();
            if ($employee_exists) throw ValidationException::withMessages(['Duplicate Employee No.']);
        }

        $username = Str::random(4);
        $password = strval("123456");
        $email = @$input['employee']['email'];
        if ($email) {
            $init = explode('@', $email);
            if ($init[0]) $password = $init[0];
        }
        $input['employee'] = array_replace($input['employee'], compact('username', 'password'));

        $email_input = [
            'text' => 'Account Created Successfully. Username: ' . $username . ' and Password: ',
            'subject' => strtoupper('login details'),
            'mail_to' => $input['employee']['email'],
            'customer_name' => $input['employee']['first_name'],
        ];

        $input['meta'] = array_replace($input['meta'], [
            'dob' => date_for_database($input['meta']['dob']),
            'employement_date' => date_for_database($input['meta']['employement_date']),
        ]);

        $role_id = $input['employee']['role'];
        $role = Role::find($role_id);
        if ($role && $role->status == 1) {
            $input['employee'] = array_replace($input['employee'], [
                'created_by' => auth()->user()->id,
                'confirmed' => 1,
            ]);
            unset($input['employee']['role']);
            $hrm = Hrm::create($input['employee']);

            $input['meta']['user_id'] = $hrm->id;
            if (!$input['meta']['is_cronical']) $input['meta']['specify'] = 'none';
            HrmMeta::create($input['meta']);

            RoleUser::create(['user_id' => $hrm->id, 'role_id' => $role_id]);
            
            if (isset($input['permission'])) $hrm->permissions()->attach($input['permission']);

            if ($hrm) {
                DB::commit();
                // send email and text
                // $this->messageUtil->sendMessage($input['meta']['primary_contact'], $email_input['text']);
                $mailer = new RosemailerRepository;
                $mailer->send($email_input['text'], $email_input);

                return $hrm;
            }
        }

        throw new GeneralException(trans('exceptions.backend.hrms.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Hrm $hrm
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Hrm $hrm, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if ($key == 'employee') {
                if (isset($val['picture'])) {
                    if ($this->storage->exists($this->file_picture_path . $hrm->picture)) {
                        $this->storage->delete($this->file_picture_path . $hrm->picture);
                    }
                    $input[$key]['picture'] = $this->uploadPicture($val['picture'], $this->file_picture_path);
                }
                if (isset($val['signature'])) {
                    if ($this->storage->exists($this->file_sign_path . $hrm->signature)) {
                        $this->storage->delete($this->file_sign_path . $hrm->signature);
                    }
                    $input[$key]['signature'] = $this->uploadPicture($val['signature'], $this->file_sign_path);
                }
            }
            if ($key == 'meta') {
                if (isset($val['id_front'])) {
                    if ($this->storage->exists($this->file_sign_path . $hrm->id_front)) {
                        $this->storage->delete($this->file_sign_path . $hrm->id_front);
                    }
                    $input[$key]['id_front'] = $this->uploadPicture($val['id_front'], $this->file_sign_path);
                }
                if (isset($val['id_back'])) {
                    if ($this->storage->exists($this->file_sign_path . $hrm->id_back)) {
                        $this->storage->delete($this->file_sign_path . $hrm->id_back);
                    }
                    $input[$key]['id_back'] = $this->uploadPicture($val['id_back'], $this->file_sign_path);
                }
                $input[$key]['dob'] = date_for_database($val['dob']);
                $input[$key]['employement_date'] = date_for_database($val['employement_date']);
            }
        }

        if (@$input['meta']['employee_no']) {
            $employee_exists = HrmMeta::where('user_id', '!=', $hrm->id)->where('employee_no', 'LIKE', "%{$input['meta']['employee_no']}%")->exists();
            if ($employee_exists) throw ValidationException::withMessages(['Duplicate Employee No.']);
        }
        
        $role_id = $input['employee']['role'];
        $role = Role::find($role_id);
        if ($role && $role->status == 1) {
            $role_user = RoleUser::where('user_id', $hrm->id)->first();
            if ($role_user) $role_user->update(compact('role_id'));

            $hrm_meta = HrmMeta::where('user_id', $hrm->id)->first();
            if ($hrm_meta) $hrm_meta->update($input['meta']);

            unset($input['employee']['role']);
            $hrm->update($input['employee']);

            PermissionUser::where('user_id', $hrm->id)->delete();
            if (isset($input['permission'])) $hrm->permissions()->attach($input['permission']);

            DB::commit();
            return true;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\hrm\Hrm $hrm
     * @return bool
     * @throws GeneralException
     */
    public function delete(Hrm $hrm)
    {
        DB::beginTransaction();

        if (auth()->user()->id == $hrm->id)
            throw ValidationException::withMessages(['Not allowed!']);

        $params = ['user_id' => $hrm->id];
        HrmMeta::where($params)->delete();
        RoleUser::where($params)->delete();
        UserProfile::where($params)->delete();

        if ($hrm->delete()) {
            DB::commit();
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.hrms.delete_error'));
    }

    /*
    * Upload logo image
    */
    public function uploadPicture($logo, $path)
    {
        $image_name = time() . $logo->getClientOriginalName();

        $this->storage->put($path . $image_name, file_get_contents($logo->getRealPath()));

        return $image_name;
    }
}
