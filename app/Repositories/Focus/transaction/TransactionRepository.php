<?php

namespace App\Repositories\Focus\transaction;

use DB;
use App\Models\transaction\Transaction;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;

/**
 * Class TransactionRepository.
 */
class TransactionRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Transaction::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $rel_id = request('rel_id', 0);
        if ($rel_id) {
            $rel_type = request('rel_type', 0);
            switch ($rel_type) {
                case 2: $q->where('user_id', $rel_id); break;
                case 9: $q->where('account_id', $rel_id); break;
            }
        }

        // filter by date
        $q->when(request('start_date') && request('end_date'), function ($q) {
            $q->whereBetween('tr_date', [
                date_for_database(request('start_date')), 
                date_for_database(request('end_date'))
            ]);
        });
        
        // fetch related double-entry transactions
        if (request('tr_id', 0)) {
            $q->where('tid', request('tr_tid', 0))->where('id', '!=', request('tr_id', 0));
        }

        return $q;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param App\Models\Transaction $transaction
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($transaction, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $input['due_date'] = $input['tr_date'];
        foreach ($input as $key => $val) {
            if (in_array($key, ['debit', 'credit']))
                $input[$key] = numberClean($val);
            if (in_array($key, ['tr_date', 'due_date'])) {
                $input[$key] = date_for_database($val);
            }
        }

        $result = $transaction->update($input);
        aggregate_account_transactions();

        DB::commit();
        if ($result) return true;

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Transaction $transaction
     * @return bool
     * @throws GeneralException
     */
    public function delete($transaction)
    {
        if ($transaction->reconciliation_id) return false;
        return $transaction->delete();

        throw new GeneralException(trans('exceptions.backend.transactions.delete_error'));
    }
}
