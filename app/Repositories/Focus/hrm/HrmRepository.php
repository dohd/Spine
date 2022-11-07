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
        $q = $this->query();
        if (request('rel_type') == 2 AND request('rel_id')) {
            $q->whereHas('meta', function ($s) {
                return $s->where('department_id', '=', request('rel_id', 0));
            });
        }
        return $q->with(['monthlysalary'])->get(['id','email','picture','first_name','last_name','status','created_at']);
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

        if (!empty($input['employee']['picture'])) {
            $input['employee']['picture'] = $this->uploadPicture($input['employee']['picture'], $this->file_picture_path);
        }
        if (!empty($input['employee']['signature'])) {
            $input['employee']['signature'] = $this->uploadPicture($input['employee']['signature'], $this->file_sign_path);
        }
        if (!empty($input['meta']['id_front'])) {
            $input['meta']['id_front'] = $this->uploadPicture($input['meta']['id_front'], $this->file_sign_path);
        }
        if (!empty($input['meta']['id_back'])) {
            $input['meta']['id_back'] = $this->uploadPicture($input['meta']['id_back'], $this->file_sign_path);
        }
        $username=Str::random(4);
        $password=Str::random(6);
        $input['employee']['password']= $password;
        $input['employee']['username']= $username;
        $email_input=array();
        $email_input['text']='ERPSPINE  account created Successfully!!Your  username is : '.$username.' and password is : '.$password .'';
        $email_input['subject']='ERPSPINE LOGIN DETAILS';
        $email_input['mail_to']=$input['employee']['email'];
        $email_input['customer_name']=$input['employee']['first_name'];
        $input['meta']['dob'] =date_for_database($input['meta']['dob']);
        $input['meta']['employement_date'] =date_for_database($input['meta']['employement_date']);

        DB::beginTransaction();
        $role = $input['employee']['role'];
        $role_valid = Role::where(function ($query) {
            $query->where('ins', '=', auth()->user()->ins)->orWhereNull('ins');
        })->where('id', '=', $role)->first();
        if ($role_valid->status < 1) {
            unset($input['employee']['role']);
            $input['employee']['created_by'] = auth()->user()->id;
            $input['employee']['confirmed'] = 1;
           // $input['profile'] = array_map( 'strip_tags', $input['profile']);
            $input['meta'] = array_map( 'strip_tags', $input['meta']);
            $input['employee'] = array_map( 'strip_tags', $input['employee']);
            $hrm = Hrm::create($input['employee']);

           // $input['profile']['user_id'] = $hrm->id;
            $input['meta']['user_id'] = $hrm->id;

           // UserProfile::create($input['profile']);
            HrmMeta::create($input['meta']);
            RoleUser::create(array('user_id' => $hrm->id, 'role_id' => $role));

            if (isset($input['permission']['permission'])) $hrm->permissions()->attach($input['permission']['permission']);

            DB::commit();
            if ($hrm->id) {
                //send email and text
               // $this->messageUtil->sendMessage($input['meta']['primary_contact'],$email_input['text']);
                $mailer = new RosemailerRepository;
                $mailer->send($email_input['text'], $email_input);
                return $hrm->id;
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
        if (!empty($input['employee']['picture'])) {
            if ($this->storage->exists($this->file_picture_path . $hrm->picture)) {
                $this->storage->delete($this->file_picture_path . $hrm->picture);
            }
            $input['employee']['picture'] = $this->uploadPicture($input['employee']['picture'], $this->file_picture_path);
        }
        if (!empty($input['employee']['signature'])) {
            if ($this->storage->exists($this->file_sign_path . $hrm->signature)) {
                $this->storage->delete($this->file_sign_path . $hrm->signature);
            }
            $input['employee']['signature'] = $this->uploadPicture($input['employee']['signature'], $this->file_sign_path);
        }
        if (!empty($input['meta']['id_front'])) {
            if ($this->storage->exists($this->file_sign_path . $hrm->id_front)) {
                $this->storage->delete($this->file_sign_path . $hrm->id_front);
            }
            $input['meta']['id_front'] = $this->uploadPicture($input['meta']['id_front'], $this->file_sign_path);
        }
        if (!empty($input['meta']['id_back'])) {
            if ($this->storage->exists($this->file_sign_path . $hrm->id_back)) {
                $this->storage->delete($this->file_sign_path . $hrm->id_back);
            }
            $input['meta']['id_back'] = $this->uploadPicture($input['meta']['id_back'], $this->file_sign_path);
        }
        $input['meta']['dob'] =date_for_database($input['meta']['dob']);
        $input['meta']['employement_date'] =date_for_database($input['meta']['employement_date']);


       


        $role = $input['employee']['role'];
        $role_valid = Role::where(function ($query) {
            $query->where('ins', '=', auth()->user()->ins)->orWhereNull('ins');
        })->where('id', '=', $role)->first();
        if (@$role_valid->status < 1) {
            DB::beginTransaction();
            // $input['profile'] = array_map( 'strip_tags', $input['profile']);
           // $user = UserProfile::where('user_id', $hrm->id)->update($input['profile']);
            $role = $input['employee']['role'];
            unset($input['employee']['role']);
            RoleUser::where('user_id', $hrm->id)->update(array('role_id' => $role));

              $input['meta'] = array_map( 'strip_tags', $input['meta']);

            HrmMeta::where('user_id', $hrm->id)->update($input['meta']);

            //$hrm->permissions()->delete(['user_id'=>$hrm->id]);
            PermissionUser::where('user_id', $hrm->id)->delete();
                   $input['employee'] = array_map( 'strip_tags', $input['employee']);
            if ($hrm->update($input['employee'])) DB::commit();
            if (isset($input['permission']['permission'])) {
                $hrm->permissions()->attach($input['permission']['permission']);
            }
            return true;
        }


        throw new GeneralException(trans('exceptions.backend.hrms.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Hrm $hrm
     * @return bool
     * @throws GeneralException
     */
    public function delete(Hrm $hrm)
    {
        UserProfile::where('user_id', $hrm->id)->delete();
        if ($hrm->delete()) {
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
