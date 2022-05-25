<?php

namespace App\Repositories\Focus\charge;

use DB;
use App\Models\charge\Charge;
use App\Exceptions\GeneralException;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
/**
 * Class ChargeRepository.
 */
class ChargeRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Charge::class;

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

        $input = array_replace($input, [
            'date' => date_for_database($input['date']),
            'amount' => numberClean($input['amount'])
        ]);
        $result = Charge::create($input);

        /** accounts */
        $this->post_transaction($result);
        
        DB::commit();
        if ($result) return $result;
        
        throw new GeneralException(trans('exceptions.backend.charges.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Charge $charge
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Charge $charge, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($charge->update($input)) return true;
            
        throw new GeneralException(trans('exceptions.backend.charges.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Charge $charge
     * @throws GeneralException
     * @return bool
     */
    public function delete(Charge $charge)
    {
        if ($charge->delete()) return true;       
        
        throw new GeneralException(trans('exceptions.backend.charges.delete_error'));
    }

    public function post_transaction($result)
    {
        // credit bank
        $tr_category = Transactioncategory::where('code', 'chrg')->first(['id', 'code']);
        $cr_data = [
            'account_id' => $result->bank_id,
            'trans_category_id' => $tr_category->id,
            'credit' => $result['amount'],
            'tr_date' => date('Y-m-d'),
            'due_date' => $result['date'],
            'user_id' => $result['user_id'],
            'ins' => $result['ins'],
            'tr_type' => $tr_category->code,
            'tr_ref' => $result['id'],
            'user_type' => 'customer',
            'is_primary' => 1,
            'note' => $result['note'],
        ];
        Transaction::create($cr_data);

        // debit expense account (bank charge)
        unset($cr_data['credit'], $cr_data['is_primary']);
        $dr_data = array_replace($cr_data, [
            'account_id' => $result['expense_id'],
            'debit' => $result['amount'],
        ]);
        Transaction::create($dr_data);
        aggregate_account_transactions();
    }
}
