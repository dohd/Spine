<?php

namespace App\Repositories\Focus\banktransfer;

use App\Models\banktransfer\Banktransfer;
use App\Exceptions\GeneralException;
use App\Models\bank\Bank;
use App\Repositories\BaseRepository;
use App\Models\transactioncategory\Transactioncategory;
use DB;

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
        DB::beginTransaction();

        $input['transaction_date'] = date_for_database($input['transaction_date']);
        $input['amount'] = numberClean($input['amount']);
        $input['note'] = "{$input['method']} - {$input['refer_no']} {$input['note']}";

        $result = $this->post_transaction((object) $input);
        if ($result) {
            DB::commit();
            return true;
        }

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
    public function update(Banktransfer $banktransfer, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $input['transaction_date'] = date_for_database($input['transaction_date']);
        $input['amount'] = numberClean($input['amount']);
        $input['note'] = "{$input['method']} - {$input['refer_no']} {$input['note']}";

        $input['id'] = $banktransfer->id;
        $result = $this->post_transaction((object) $input);
        if ($result) {
            DB::commit();
            return true;
        }

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
        $result = Banktransfer::where('tid', $banktransfer->tid)->delete();
        aggregate_account_transactions();
        if ($result) return true;

        throw new GeneralException(trans('exceptions.backend.charges.delete_error'));
    }

    /**
     * Money Transfer Transactons
     * 
     */
    public function post_transaction($data)
    {
        // credit Transfer Account (Bank)
        $tr_category = Transactioncategory::where('code', 'xfer')->first(['id', 'code']);

        $tr_data = [];
        $tr_data[] = [
            'tid' => $data->tid,
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
        $tr_data[] = array_replace(current($tr_data), [
            'account_id' => $data->debit_account_id,
            'debit' => $data->amount,
            'credit' => 0,
            'is_primary' => 0
        ]);

        if (isset($data->id)) {
            // update
            $banktransfers = Banktransfer::where(['tid' => $data->tid, 'tr_type' => 'xfer'])->get();
            foreach ($banktransfers as $item) {
                $item_rel = $item;
                $new_data = [];
                if ($item->debit > 0) {
                    $new_data = array_replace($item_rel->toArray(), end($tr_data));
                } elseif ($item->credit > 0) {
                    $new_data = array_replace($item_rel->toArray(), current($tr_data));
                }
                $item->update($new_data);
            }
        } else {
            // create
            Banktransfer::insert($tr_data);
        }
        
        aggregate_account_transactions();
        return true;
    }
}
