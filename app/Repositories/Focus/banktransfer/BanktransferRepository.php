<?php

namespace App\Repositories\Focus\banktransfer;

use App\Models\banktransfer\Banktransfer;
use App\Exceptions\GeneralException;
use App\Models\bank\Bank;
use App\Repositories\BaseRepository;
use App\Repositories\Accounting;
use DB;
use Illuminate\Support\Arr;

/**
 * Class BanktransferRepository.
 */
class BanktransferRepository extends BaseRepository
{
    use Accounting;
    /**
     * Associated Repository Model.
     */
    const MODEL = Banktransfer::class;

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
        $input['transaction_date'] = date_for_database($input['transaction_date']);
        $input['amount'] = numberClean($input['amount']);

        $banktransfer = Banktransfer::create($input);
        /** accounting */
        $this->post_bank_transfer($banktransfer);

        if ($banktransfer) {
            DB::commit();
            return $banktransfer;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Bank $bank
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Banktransfer $banktransfer, array $input)
    {
        DB::beginTransaction();
        $input['transaction_date'] = date_for_database($input['transaction_date']);
        $input['amount'] = numberClean($input['amount']);

        $result = $banktransfer->update($input);

        /** accounting */
        $banktransfer->transactions()->delete();
        $this->post_bank_transfer($banktransfer);

        if ($result) {
            DB::commit();
            return true;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Bank $bank
     * @throws GeneralException
     * @return bool
     */
    public function delete($banktransfer)
    {
        DB::beginTransaction();
        $banktransfer->transactions()->delete();
        $result = $banktransfer->delete();
        if ($result) {
            DB::commit();
            return true;
        }
    }
}
