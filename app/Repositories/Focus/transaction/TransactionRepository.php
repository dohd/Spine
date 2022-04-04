<?php

namespace App\Repositories\Focus\transaction;

use App\Models\account\Account;
use App\Models\Company\ConfigMeta;
use DB;
use Carbon\Carbon;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

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

        $q->when(request('p_rel_type') == 0 AND request('p_rel_id'), function ($q) {
            return $q->where('trans_category_id', '=', request('p_rel_id', 0));
        });

        $q->when(request('p_rel_type') == 1 AND request('p_rel_id'), function ($q) {
            $q->where('payer_id', '=', request('p_rel_id', 0));
            return $q->where('relation_id', '=', 1);
        });

        $q->when(request('p_rel_type') == 2 AND request('p_rel_id'), function ($q) {
            return $q->where('user_id', '=', request('p_rel_id', 0));
        });

        $q->when(request('p_rel_type') == 9 AND request('p_rel_id'), function ($q) {
            return $q->where('account_id', '=', request('p_rel_id', 0));
        });

        $q->when(request('p_rel_type') == 3 AND request('p_rel_id'), function ($q) {
            $q->where('payer_id', '=', request('p_rel_id', 0));
            return $q->where('relation_id', '=', 3);
        });

        $q->when(request('p_rel_type') ==4 AND request('p_rel_id'), function ($q) {
            $q->where('payer_id', '=', request('p_rel_id', 0));
            return $q->where('relation_id', '=', 9);
        });

        return $q->get(['id', 'tid', 'note', 'trans_category_id', 'debit', 'credit', 'account_id', 'tr_date', 'user_type']);
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

        $trans_category_id = Transactioncategory::where('code', 'm_journal')->first();
        $trans_category_id=$trans_category_id->id;
        
        DB::beginTransaction();
          $items = array();
          
 //dd($input['invoice']);
         foreach ($input['invoice']['account_id'] as $key => $value) {


            
 
         $items[] = array(
                    'is_bill' =>4,
                    'tid' => $input['invoice']['tid'],
                    'ins' => $input['invoice']['ins'],
                    'user_id' => $input['invoice']['user_id'],
                    'account_id' => $input['invoice']['account_id'][$key],
                    'trans_category_id' => $trans_category_id,
                    'transaction_type' =>'manual_journal',
                    'note' => strip_tags(@$input['invoice']['note']),
                    'debit' => numberClean(@$input['invoice']['debit'][$key]),
                    'credit' => numberClean(@$input['invoice']['credit'][$key]),
                    'transaction_date' => date_for_database(@$input['invoice']['transaction_date'])
            
                       
                );

         $result=Transaction::insert($items);

         }


        DB::commit();

        return $result;
        /*if ($result['id']) {
            $feature = feature(11);
            $alert = json_decode($feature->value2, true);
            if ($alert['new_trans']) {
                $mail = array();
                $mail['mail_to'] = $feature->value1;
                $mail['customer_name'] = trans('transactions.transaction');
                $mail['subject'] = trans('meta.new_transaction_alert') . '#' . $result['id'];
                $mail['text'] = trans('transactions.transaction') . ' #' . $result['id'] . '<br>' . trans('general.amount') . '<br>Dr ' . amountFormat($input['debit']) . ' <br>Cr ' . amountFormat($input['credit']) . '<br> - ' . $input['note'];
                business_alerts($mail);
            }
            return $result['id'];
        }*/
        throw new GeneralException(trans('exceptions.backend.transactions.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Transaction $transaction
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($transaction, array $input)
    {

        //if ($transaction->update($input))
        //    return true;

        throw new GeneralException(trans('exceptions.backend.transactions.update_error'));
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
        $feature = feature(11);
        $alert = json_decode($feature->value2, true);
        if ($alert['del_trans']) {
            $mail = array();
            $mail['mail_to'] = $feature->value1;
            $mail['customer_name'] = trans('transactions.transaction');
            $mail['subject'] = trans('meta.delete_transaction_alert') . '#' . $transaction->id;
            $mail['text'] = trans('transactions.transaction') . ' #' . $transaction->id . '<br>' . trans('general.amount') . '<br>Dr ' . amountFormat($transaction->debit) . ' <br>Cr ' . amountFormat($transaction->credit) . '<br> - ' . $transaction->note;
            business_alerts($mail);
        }
         DB::beginTransaction();
        $account = Account::find($transaction->account_id);
        $account->balance += $transaction->debit;
        $account->balance -= $transaction->credit;
        $account->save();
        if ($transaction->delete()) {
              DB::commit();
            return true;
        }
          DB::rollback();

        throw new GeneralException(trans('exceptions.backend.transactions.delete_error'));
    }
}
