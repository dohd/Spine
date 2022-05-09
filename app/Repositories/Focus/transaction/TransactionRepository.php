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

        // related transactions
        $tr_id = request('tr_id', 0);
        if ($tr_id) {
            $tr = Transaction::find($tr_id, ['id', 'note', 'tr_type']);
            $q->where(['tr_type' => $tr->tr_type, 'note' => $tr->note]);
            $q->where('id', '!=', $tr_id);
        }

        return $q->get([
            'id', 'tid', 'note', 'trans_category_id', 'debit', 'credit', 'account_id', 
            'tr_date', 'user_type', 'tr_type', 'tr_ref', 'created_at'
        ]);
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

        $input['debit'] = numberClean($input['debit']);
        $input['credit'] = numberClean($input['credit']);

        $transaction->update($input);

        // update account ledgers debit and credit totals
        aggregate_account_transactions();

        DB::commit();
        return true;

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
