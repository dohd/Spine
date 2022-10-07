<?php

namespace App\Repositories\Focus\banktransfer;

use App\Models\banktransfer\Banktransfer;
use App\Exceptions\GeneralException;
use App\Models\transaction\Transaction;
use App\Repositories\BaseRepository;
use App\Models\transactioncategory\Transactioncategory;

/**
 * Class BankRepository.
 */
class BanktransferRepository extends BaseRepository
{
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
        return $this->query()->where('tr_type', 'xfer')->get();
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
        // dd($input);
        $input['transaction_date'] = date_for_database($input['transaction_date']);
        $input['amount'] = numberClean($input['amount']);
        $input['note'] = "{$input['method']} - {$input['refer_no']} {$input['note']}";
        $data = (object) $input;
        
        // credit Transfer Account (Bank)
        $tr_category = Transactioncategory::where('code', 'xfer')->first(['id', 'code']);
        $tr_data = [];
        $tr_data[] = [
            'tid' => Transaction::max('tid') + 1,
            'account_id' => $data->account_id,
            'trans_category_id' => $tr_category->id,
            'tr_date' => $data->transaction_date,
            'due_date' => $data->transaction_date,
            'user_id' => auth()->user()->id,
            'note' => $data->note,
            'ins' => auth()->user()->ins,
            'tr_type' => $tr_category->code,
            'user_type' => 'employee',
            'credit' => $data->amount,
            'debit' => 0,
            'is_primary' => 1,
        ];

        // debit Recepient Account (Bank)
        $tr_data[] = array_replace($tr_data[0], [
            'account_id' => $data->debit_account_id, 
            'debit' => $data->amount,
            'credit' => 0,
            'is_primary' => 0
        ]);

        $result = Banktransfer::insert($tr_data);
        aggregate_account_transactions();
        if ($result) return true;

        throw new GeneralException(trans('exceptions.backend.charges.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Bank $bank
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Charge $charge, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($charge->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.charges.update_error'));
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
        $result = Banktransfer::where(['tr_type' => 'xfer', 'note' => $banktransfer->note])->delete();
        aggregate_account_transactions();
        if ($result) return true;

        throw new GeneralException(trans('exceptions.backend.charges.delete_error'));
    }
}
