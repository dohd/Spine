<?php

namespace App\Repositories\Focus\client_vendor;

use App\Http\Controllers\ClientSupplierAuth;
use App\Models\client_vendor\ClientVendor;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;

class ClientVendorRepository extends BaseRepository
{
    use ClientSupplierAuth;

    /**
     * Associated Repository Model.
     */
    const MODEL = ClientVendor::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        $q = $this->query();

        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function create(array $input)
    {
        DB::beginTransaction();

        $user_data = Arr::only($input, ['first_name', 'last_name', 'email', 'password']);
        $vendor_data = Arr::except($input, array_flip($user_data));
        $vendor_data['email'] = $input['email'];

        $vendor = ClientVendor::create($vendor_data);
        // authorize
        $this->createAuth($vendor, $user_data, 'client_vendor');

        if ($vendor) {
            DB::commit();
            return $vendor;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(ClientVendor $client_vendor, array $input)
    {
        DB::beginTransaction();

        $user_data = Arr::only($input, ['first_name', 'last_name', 'email', 'password']);
        $vendor_data = Arr::except($input, array_flip($user_data));
        $vendor_data['email'] = $input['email'];

        $result = $client_vendor->update($vendor_data);
        // authorize
        $this->updateAuth($client_vendor, $user_data, 'client_vendor');

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete(ClientVendor $client_vendor)
    {
        if ($this->deleteAuth($client_vendor, 'client_vendor') && $client_vendor->delete()) {
            return true;
        }
    }
}
