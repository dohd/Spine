<?php

namespace App\Repositories\Focus\account;

use App\Models\account\Account;
use App\Exceptions\GeneralException;
use App\Models\deposit\Deposit;
use App\Models\manualjournal\ManualJournal;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

/**
 * Class AccountRepository.
 */
class AccountRepository extends BaseRepository
{
  /**
   * Associated Repository Model.
   */
  const MODEL = Account::class;
  /**
   * This method is used by Table Controller
   * For getting the table data to show in
   * the grid
   * @return mixed
   */
  public function getForDataTable()
  {
    return $this->query()
      ->get(['id', 'number', 'holder', 'balance', 'account_type', 'created_at']);
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
    // increament account number
    $account_type_id = $input['account_type_id'];
    $ins = auth()->user()->ins;
    $input['ins'] =  $ins;
    unset($input['is_multiple']);
    $account = Account::where(compact('account_type_id'))
      ->where('number', '>', 1)
      ->orderBy('number', 'DESC')->first();
    if ($account && $input['number'] <= $account->number) {
      $input['number'] = $account->number + 1;
    }
    $opening_balance = numberClean($input['opening_balance']);
    if ($opening_balance > 0) {
      $input['opening_balance'] =  $opening_balance;
      $input['opening_balance_date'] = date_for_database($input['opening_balance_date']);
    }
    DB::beginTransaction();
    try {
      $result = Account::create($input);
      if ($result->id > 0) {
        if ($opening_balance > 0) {
          //find asystem
          $account_types = DB::table('account_types')->find($account_type_id);
          //bank transactions
          $tid = Transaction::max('tid');
          $tid = $tid + 1;
          if ($account_types->system == 'bank') {
            //deposit bank and credit Equity Share Capital
            $seco_account = Account::where('system', 'share_capital')->first();
            $pri_tr = Transactioncategory::where('code', 'DEP')->first();
            $date = date('Y-m-d');
            $tr_ref = 'DEP';
            $memo = 'Account Opening Balance';
            //Insert to deposit table
            $transaction = array(
              'account_id' => $result->id,
              'from_account_id' => $seco_account->id,
              'is_user' => 0,
              'received_from' => 0,
              'amount' => $opening_balance,
              'transaction_ref' => $tid,
              'date' => $date,
              'note' => $memo,
              'user_id' => auth()->user()->id,
              'ins' => $ins,
            );
            $savedeposit = Deposit::create($transaction);
            if ($savedeposit->id) {
              $insert_double = double_entry($tid, $result->id, $seco_account->id, $opening_balance, 'dr', $pri_tr->id, '0', '0', $date, $result->opening_balance_date, $tr_ref, $memo, $ins);
              if ($insert_double) {
                DB::commit();
                return true;
              }
            }
          } else if ($account_types->system == 'fixed_asset' || $account_types->system == 'other_current_asset' || $account_types->system == 'other_asset') {
            //deposit asset and credit Equity Share Capital
            $seco_account = Account::where('system', 'share_capital')->first();
            $pri_tr = Transactioncategory::where('code', 'GENJRNL')->first();
            $date = date('Y-m-d');
            $tr_ref = 'GENJRNL';
            $memo = 'Account Opening Balance';
            //Insert to deposit table
            $transaction = array(
              'account_id' => $result->id,
              'from_account_id' => $seco_account->id,
              'amount' => $opening_balance,
              'transaction_ref' => $tid,
              'date' => $date,
              'note' => $memo,
              'user_id' => auth()->user()->id,
              'ins' => $ins,
            );
            $savedeposit = ManualJournal::create($transaction);
            if ($savedeposit->id) {
              $insert_double = double_entry($tid, $result->id, $seco_account->id, $opening_balance, 'dr', $pri_tr->id, '0', '0', $date, $result->opening_balance_date, $tr_ref, $memo, $ins);
              if ($insert_double) {
                DB::commit();
                return true;
              }
            }
          } else if ($account_types->system == 'other_current_liability' || $account_types->system == 'long_term_liability' || $account_types->system == 'equity') {
            //deposit asset and credit Equity Share Capital
            $seco_account = Account::where('system', 'share_capital')->first();
            $pri_tr = Transactioncategory::where('code', 'GENJRNL')->first();
            $date = date('Y-m-d');
            $tr_ref = 'GENJRNL';
            $memo = 'Account Opening Balance';
            //Insert to deposit table
            $transaction = array(
              'account_id' => $result->id,
              'from_account_id' => $seco_account->id,
              'amount' => $opening_balance,
              'transaction_ref' => $tid,
              'date' => $date,
              'note' => $memo,
              'user_id' => auth()->user()->id,
              'ins' => $ins,
            );
            $savedeposit = ManualJournal::create($transaction);
            if ($savedeposit->id) {
              $insert_double = double_entry($tid, $result->id, $seco_account->id, $opening_balance, 'cr', $pri_tr->id, '0', '0', $date, $result->opening_balance_date, $tr_ref, $memo, $ins);
              if ($insert_double) {
                DB::commit();
                return true;
              }
            }
          }
          $input['opening_balance'] =  $opening_balance;
          $input['opening_balance_date'] = date_for_database($input['opening_balance_date']);
        } else {
          DB::commit();
          return true;
        }
      }
    } catch (\Illuminate\Database\QueryException $e) {
      DB::rollback();
      throw new GeneralException(trans('exceptions.backend.accounts.create_error'));
    }
  }
  /**
   * For updating the respective Model in storage
   *
   * @param Account $account
   * @param  $input
   * @throws GeneralException
   * return bool
   */
  public function update(Account $account, array $input)
  {
    if ($account->update($input)) return true;
    throw new GeneralException(trans('exceptions.backend.accounts.update_error'));
  }
  /**
   * For deleting the respective model from storage
   *
   * @param Account $account
   * @throws GeneralException
   * @return bool
   */
  public function delete(Account $account)
  {
    if ($account->delete())  return true;
    throw new GeneralException(trans('exceptions.backend.accounts.delete_error'));
  }
}
