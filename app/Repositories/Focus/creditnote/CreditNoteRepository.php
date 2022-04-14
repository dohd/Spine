<?php

namespace App\Repositories\Focus\creditnote;

use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Repositories\BaseRepository;
use App\Models\creditnote\CreditNote;
use App\Models\invoice\Invoice;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use Illuminate\Support\Facades\DB;

/**
 * Class PurchaseorderRepository.
 */
class CreditNoteRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = CreditNote::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = CreditNote::query();
        $q->where('is_debit', request('is_debit'));

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

        foreach ($input as $key => $val) {
            if ($key == 'date') $input[$key] = date_for_database($val);
            if (in_array($key, ['subtotal', 'tax', 'total'], 1)) {
                $input[$key] = numberClean($val);
            }
        }
        $result = CreditNote::create($input);

        /** accounts  */
        $this->post_transaction($result);
        // update account ledgers debit and credit totals
        aggregate_account_transactions();

        DB::commit();
        if ($result) return $result;

        throw new GeneralException(trans('exceptions.backend.purchaseorders.create_error'));
    }


    /**
     * For deleting the respective model from storage
     *
     * @param CreditNote $creditnote
     * @throws GeneralException
     * @return bool
     */
    public function delete($creditnote)
    {
        // 
    }

    static function post_transaction($creditnote)
    {
        $data = [
            'tr_date' => date('Y-m-d'),
            'due_date' => $creditnote->date,
            'user_id' => $creditnote->user_id,
            'note' => $creditnote->note,
            'ins' => $creditnote->ins,
            'tr_ref' => $creditnote->id,
            'user_type' => 'customer',
            'is_primary' => 1
        ];
        $account = Account::where('system', 'receivable')->first(['id']);
        
        // debit note, else credit note
        if ($creditnote->is_debit) {
            // credit income
            $tr_category = Transactioncategory::where('code', 'RCPT')->first(['id', 'code']);
            $cr_data = array_replace($data, [
                'account_id' => Invoice::find($creditnote->invoice_id)->account_id,
                'trans_category_id' => $tr_category->id,
                'debit' => $creditnote->subtotal,
                'tr_type' => $tr_category->code,
            ]);
            Transaction::create($cr_data);

            // debit accounts receivable
            unset($cr_data['debit'], $cr_data['is_primary']);
            $dr_data = array_replace($cr_data, [
                'account_id' => $account->id,
                'credit' => $creditnote->total,
            ]);
            Transaction::create($dr_data);
        } else {
            // credit accounts receivable
            $tr_category = Transactioncategory::where('code', 'RCPT')->first(['id', 'code']);
            $cr_data = $data + [
                'account_id' => $account->id,
                'trans_category_id' => $tr_category->id,
                'credit' => $creditnote->total,
                'tr_type' => $tr_category->code,
            ];
            Transaction::create($cr_data);

            // debit accounts income 
            unset($cr_data['credit'], $cr_data['is_primary']);
            $dr_data = array_replace($cr_data, [
                'account_id' => Invoice::find($creditnote->invoice_id)->account_id,
                'debit' => $creditnote->subtotal,
            ]);
            Transaction::create($dr_data);
        }
    }
}
