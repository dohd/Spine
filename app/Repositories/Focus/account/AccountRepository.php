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
use Illuminate\Validation\ValidationException;

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
    return $this->query()->get();
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
    $number = Account::where('account_type_id', $input['account_type_id'])
      ->where('number', '>', 1)->max('number');
    if ($input['number'] <= $number) $input['number'] = $number + 1;
    $result = Account::create($input);

    // if opening balance exists
    if ($result->opening_balance > 0) {
      $account_type = AccountType::find($result->account_type_id);
      $seco_account = Account::where('system', 'retained_earning')->first();
      $tid = Transaction::max('tid') + 1;
      $date = $result->opening_balance_date;
      $note = $result->number . '-' . $result->holder .' Account Opening Balance';
      $data = [
        'date' => $date,
        'note' => $note,
        'user_id' => auth()->user()->id,
        'ins' => $result->ins,
      ];

      // debit Bank and credit Retained Earnings
      $system = $account_type->system;
      if ($system == 'bank') {
        $pri_tr = Transactioncategory::where('code', 'dep')->first(['id', 'code']);
        $data = $data + [
          'account_id' => $result->id,
          'amount' => $result->opening_balance,
          'transaction_ref' => $tid,
          'from_account_id' => $seco_account->id
        ];
        $deposit = Deposit::create($data);

        // transaction
        double_entry(
          $tid, $result->id, $seco_account->id, $result->opening_balance, 'dr', $pri_tr->id,
          'employee', $deposit->user_id, $date, $result->opening_balance_date, $pri_tr->code, $note, $result->ins
        );
      }

      // debit Asset Account and credit Retained Earning
      // credit liability Account and debit Retained Earning
      $systems = [
        'fixed_asset', 'other_current_asset', 'other_asset',
        'other_current_liability', 'long_term_liability', 'equity'
      ];
      if (in_array($system, $systems, 1)) {
        $pri_tr = Transactioncategory::where('code', 'genjr')->first(['id', 'code']);
        $open_bal = $result->opening_balance;
        $data = $data + [
          'tid' => Journal::max('tid') + 1,
          'debit_ttl' => $open_bal,
          'credit_ttl' =>  $open_bal
        ];
        $journal = Journal::create($data);

        foreach ([1,2] as $v) {
          $item_data = [
            'journal_id' => $journal->id,
            'account_id' => $result->id,
          ];
          if ($v == 1) $item_data['debit'] = $open_bal;
          else $item_data['credit'] = $open_bal;
          JournalItem::create($item_data);
        }

        $entry_type = 'dr';
        if (in_array($system, array_splice($systems, 3, 3), 1)) $entry_type = 'cr';
        // transaction
        double_entry(
          $tid, $result->id, $seco_account->id, $result->opening_balance, $entry_type, $pri_tr->id,
          'employee', $journal->user_id, $date, $result->opening_balance_date, $pri_tr->code, $note, $result->ins
        );
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
    $input['opening_balance'] = numberClean($input['opening_balance']);
    $input['opening_balance_date'] = date_for_database($input['date']);
    unset($input['date'], $input['is_multiple']);
    $result = $account->update($input);

    // if opening balance exists
    if ($account->opening_balance > 0) {
      $account_type = AccountType::find($account->account_type_id);
      $seco_account = Account::where('system', 'retained_earning')->first();
      $tid = 0;
      $date = $account->opening_balance_date;
      $note = $account->number . '-' . $account->holder .' Account Opening Balance';
      $data = [
        'date' => $date,
        'note' => $note,
        'user_id' => auth()->user()->id,
        'ins' => $account->ins,
      ];

      // debit Bank and credit Retained Earnings
      $system = $account_type->system;
      if ($system == 'bank') {
        Transaction::where(['tr_ref' => $account->id, 'tr_type' => 'dep', 'note' => $note])->delete();
        Transaction::where(['tr_ref' => $seco_account->id, 'tr_type' => 'dep', 'note' => $note])->delete();
        $tid = Transaction::max('tid') + 1;

        $pri_tr = Transactioncategory::where('code', 'dep')->first(['id', 'code']);
        $data = $data + [
          'account_id' => $account->id,
          'amount' => $account->opening_balance,
          'transaction_ref' => $tid,
          'from_account_id' => $seco_account->id
        ];
        
        // create or update deposit
        $deposit = Deposit::firstOrNew(['account_id' => $account->id]);
        foreach ($data as $key => $val) {
          $deposit[$key] = $val;
        }
        $deposit->save();

        double_entry(
          $tid, $account->id, $seco_account->id, $account->opening_balance, 'dr', $pri_tr->id,
          'employee', $deposit->user_id, $date, $account->opening_balance_date, $pri_tr->code, $note, $account->ins
        );
      }

      // debit Asset Account and credit Retained Earning
      // credit Liability Account and debit Retained Earning
      $systems = [
        'fixed_asset', 'other_current_asset', 'other_asset',
        'other_current_liability', 'long_term_liability', 'equity'
      ];
      if (in_array($system, $systems, 1)) {
        Transaction::where(['tr_ref' => $account->id, 'tr_type' => 'genjr', 'note' => $note])->delete();
        Transaction::where(['tr_ref' => $seco_account->id, 'tr_type' => 'genjr', 'note' => $note])->delete();
        $tid = Transaction::max('tid') + 1;
        
        $pri_tr = Transactioncategory::where('code', 'genjr')->first(['id', 'code']);
        $open_bal = $account->opening_balance;
        $data = $data + [
          'tid' => Journal::max('tid') + 1,
          'debit_ttl' => $open_bal,
          'credit_ttl' =>  $open_bal
        ];

        // create or update journal
        $journal = Journal::firstOrNew(['note' => $data['note']]);
        foreach ($data as $key => $val) {
          if ($key == 'tid' && $journal->tid) continue;
          else $journal[$key] = $val;
        }

        if ($journal->items) {
          foreach ($journal->items as $item) {
            if ($item->debit > 0) $item->update(['debit' => $open_bal]);
            elseif ($item->credit > 0) $item->update(['credit' => $open_bal]);
          }
        } else {
          foreach ([1,2] as $v) {
            $item_data = [
              'journal_id' => $journal->id,
              'account_id' => $account->id,
            ];
            if ($v == 1) $item_data['debit'] = $open_bal;
            else $item_data['credit'] = $open_bal;
            JournalItem::create($item_data);
          }
        }        

        $entry_type = 'dr';
        if (in_array($system, array_splice($systems, 3, 3), 1)) $entry_type = 'cr';
        
        double_entry(
          $tid, $account->id, $seco_account->id, $account->opening_balance, $entry_type, $pri_tr->id,
          'employee', $journal->user_id, $date, $account->opening_balance_date, $pri_tr->code, $note, $account->ins
        );
      }
    }

    DB::commit();
    if ($result) return true;

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
    if ($account->transactions->count()) 
      throw ValidationException::withMessages(['Account has attached transactions']);
    if ($account->system) 
      throw ValidationException::withMessages(['System Account cannot be deleted']);
    if ($account->delete()) {
      aggregate_account_transactions();
      return true;
    }

    throw new GeneralException(trans('exceptions.backend.accounts.delete_error'));
  }
}
