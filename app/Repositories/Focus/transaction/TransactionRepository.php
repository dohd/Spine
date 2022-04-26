<?php

namespace App\Repositories\Focus\transaction;

use App\Models\account\Account;
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
        $rel_type = request('rel_type', 0);

        if ($rel_id) {
            switch ($rel_type) {
                case 2: $q->where('user_id', $rel_id); break;
                case 9: $q->where('account_id', $rel_id); break;
            }
        }

        return $q->get([
            'id', 'tid', 'note', 'trans_category_id', 'debit', 'credit', 'account_id', 'tr_date', 'user_type',
            'tr_type', 'tr_ref'
        ]);
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
        if ($transaction->reconciliation_id) return $transaction;
        $transaction->delete();
        return false;

        throw new GeneralException(trans('exceptions.backend.transactions.delete_error'));
    }
}
