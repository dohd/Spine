<?php

namespace App\Repositories\Focus\account;

use App\Models\account\Account;
use App\Exceptions\GeneralException;
use App\Models\account\AccountType;
use App\Models\deposit\Deposit;
use App\Models\items\JournalItem;
use App\Models\manualjournal\Journal;
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
    return $this->query()->get(['id', 'number', 'holder', 'balance', 'account_type', 'created_at']);
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

    $input['opening_balance'] = numberClean($input['opening_balance']);
    $input['opening_balance_date'] = date_for_database($input['date']);
    unset($input['date'], $input['is_multiple']);
    // increment account number
    $account = Account::where('account_type_id', $input['account_type_id'])
      ->where('number', '>', 1)->orderBy('number', 'DESC')->first();
    if ($account && $input['number'] <= $account->number) {
      $input['number'] = $account->number + 1;
    }
    $result = Account::create($input);

    if ($result->opening_balance > 0) {
      $account_type = AccountType::find($result->account_type_id);
      $seco_account = Account::where('system', 'share_capital')->first();
      $tid = Transaction::max('tid') + 1;
      $date = date('Y-m-d');
      $memo = 'Account Opening Balance';
      $data = [
        'date' => $date,
        'note' => $memo,
        'user_id' => auth()->user()->id,
        'ins' => $result->ins,
      ];

      // debit bank and credit Equity Share Capital
      $system = $account_type->system;
      if ($system == 'bank') {
        $pri_tr = Transactioncategory::where('code', 'DEP')->first();
        $tr_ref = 'DEP';
        $data = $data + [
          'account_id' => $result->id,
          'amount' => $result->opening_balance,
          'transaction_ref' => $tid,
          'from_account_id' => $seco_account->id 
        ];
        $deposit = Deposit::create($data);

        $args = [
          $tid, $result->id, $seco_account->id, $result->opening_balance, 'dr', $pri_tr->id, '0', '0', 
          $date, $result->opening_balance_date, $tr_ref, $memo, $result->ins
        ];
        if ($deposit) double_entry(...$args);
      }
      
      // debit asset and credit Equity Share Capital
      // credit liability and debit Equity Share Capital
      $systems = [
        'fixed_asset', 'other_current_asset', 'other_asset', 
        'other_current_liability', 'long_term_liability', 'equity'
      ];
      if (in_array($system, $systems, 1)) {
        $pri_tr = Transactioncategory::where('code', 'GENJRNL')->first();  
        $tr_ref = 'GENJRNL';
        $open_bal = $result->opening_balance;
        $data = $data + [
          'tid' => Journal::max('tid') + 1, 
          'debit_ttl' => $open_bal,
          'credit_ttl' =>  $open_bal
        ];
        $journal = Journal::create($data);
        $item_data = [
          'journal_id' => $journal->id, 
          'account_id' => $result->id, 
          'debit' => $open_bal, 
        ];
        JournalItem::create($item_data);
        unset($item_data['debit']);
        $item_data['credit'] = $open_bal;
        JournalItem::create($item_data);

        $dr_pri = 'dr';
        if (in_array($system, array_splice($systems, 3, 3), 1)) $dr_pri = 'cr';
        $args = [
          $tid, $result->id, $seco_account->id, $result->opening_balance, $dr_pri, $pri_tr->id, '0', '0', 
          $date, $result->opening_balance_date, $tr_ref, $memo, $result->ins
        ];
        if ($journal) double_entry(...$args);
      }
    }

    DB::commit();
    if ($result) return $result;

    throw new GeneralException(trans('exceptions.backend.accounts.create_error'));
  }

  /**
   * For updating the respective Model in storage
   *
   * @param Account $account
   * @param  $input
   * @throws GeneralException
   * return bool
   */
  public function update($account, array $input)
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
  public function delete($account)
  {
    if ($account->delete())  return true;

    throw new GeneralException(trans('exceptions.backend.accounts.delete_error'));
  }
}