<?php

namespace App\Repositories\Focus\customer;

use App\Models\customergroup\CustomerGroupEntry;
use App\Models\items\CustomEntry;
use DB;
use App\Models\customer\Customer;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Storage;
use App\Models\branch\Branch;

/**
 * Class CustomerRepository.
 */
class CustomerRepository extends BaseRepository
{

    /**
     *customer_picture_path .
     *
     * @var string
     */
    protected $customer_picture_path;


    /**
     * Storage Class Object.
     *
     * @var \Illuminate\Support\Facades\Storage
     */
    protected $storage;
    /**
     * Associated Repository Model.
     */
    const MODEL = Customer::class;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->customer_picture_path = 'img' . DIRECTORY_SEPARATOR . 'customer' . DIRECTORY_SEPARATOR;
        $this->storage = Storage::disk('public');
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
        $q->when(request('g_rel_type'), function ($q) {

            return $q->where('rel_id', '=',request('g_rel_id',-1));
        });
        if (!request('g_rel_type') AND request('g_rel_id')) {
            $q->whereHas('group', function ($s) {
                return $s->where('customer_group_id', '=', request('g_rel_id', 0));
            });
        }
        return $q->get(['id','name','company','email','address','picture','active','created_at']);
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
        DB::beginTransaction();
        
        if (!empty($input['picture'])) 
            $input['picture'] = $this->uploadPicture($input['picture']);
        
        $customer = Customer::where('email', $input['email'])->first('id');
        if ($customer) return session()->flash('flash_error', 'Duplicate Email');

        $groups = isset($input['groups']) ? $input['groups'] : array();
        $custom_field = isset($input['custom_field']) ? $input['custom_field'] : array();
        unset($input['groups'], $input['custom_field']);
        $result = Customer::create($input);

        $branches = [['name' => 'All Branches'], ['name' => 'Head Office']];
        foreach ($branches as $k => $branch) {
            $branch['customer_id'] = $result->id;
            $branch['ins'] = $result->ins;
            $branches[$k] = $branch;
        }
        Branch::insert($branches);

        $groups = array_reduce($groups, function ($init, $val) use($result) {
            $init[] = ['customer_id' => $result->id, 'customer_group_id' => $val];
            return $init;
        }, []);
        if ($groups) CustomerGroupEntry::insert($groups);

        $fields = array();
        foreach ($custom_field as $k => $val) {
            $fields[] = [
                'custom_field_id' => $k,
                'rid' => $result->id,
                'module' => 1,
                'data' => $val,
                'ins' => $result->ins
            ];
        }
        if ($fields) CustomEntry::insert($fields);

        DB::commit();
        if ($result) return $result;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Customer $customer
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($customer, array $input)
    {
        // dd($input, $customer->id);
        DB::beginTransaction();

        if (!empty($input['picture'])) {
            $this->removePicture($customer, 'picture');
            $input['picture'] = $this->uploadPicture($input['picture']);
        }
        if (empty($input['password'])) unset($input['password']);

        $is_email = Customer::whereNotIn('id', [$customer->id])->where('email', $input['email'])->first('id');
        if ($is_email) {
            session()->flash('flash_error', 'Duplicate Email');
            return false;
        }

        $groups = isset($input['groups']) ? $input['groups'] : array();
        $custom_field = isset($input['custom_field']) ? $input['custom_field'] : array();
        unset($input['groups'], $input['custom_field']);
        $customer->update($input);

        if ($groups)  {
            $groups = array_reduce($groups, function ($init, $val) use($customer) {
                $init[] = ['customer_id' => $customer->id, 'customer_group_id' => $val];
                return $init;
            }, []);    
            CustomerGroupEntry::where('customer_id',  $customer->id)->delete();
            CustomerGroupEntry::insert($groups);
        }
        if ($custom_field) {
            $fields = array();
            foreach ($custom_field as $k => $val) {
                $fields[] = [
                    'custom_field_id' => $k,
                    'rid' => $customer->id,
                    'module' => 1,
                    'data' => $val,
                    'ins' => $customer->ins
                ];
                CustomEntry::where(['custom_field_id' => $k, 'rid' => $customer->id])->delete();
            }
            CustomEntry::insert($fields);
        }

        DB::commit();
        return true;

        throw new GeneralException(trans('exceptions.backend.customers.update_error'));
    }

    /*
 * Upload logo image
 */
    public function uploadPicture($logo)
    {
        $path = $this->customer_picture_path;

        $image_name = time() . $logo->getClientOriginalName();

        $this->storage->put($path . $image_name, file_get_contents($logo->getRealPath()));

        return $image_name;
    }

    /*
    * remove logo or favicon icon
    */
    public function removePicture(Customer $customer, $type)
    {
        $path = $this->customer_picture_path;

        if ($customer->$type && $this->storage->exists($path . $customer->$type)) {
            $this->storage->delete($path . $customer->$type);
        }

        $result = $customer->update([$type => null]);

        if ($result) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.settings.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Customer $customer
     * @return bool
     * @throws GeneralException
     */
    public function delete($customer)
    {
        if ($customer->leads()->first()) return;
        if ($customer->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.customers.delete_error'));
    }
}
