<?php

namespace App\Repositories\Focus\account;

use App\Models\account\Account;
use App\Exceptions\GeneralException;
use App\Models\account\AccountType;
use App\Models\deposit\Deposit;
use App\Models\items\JournalItem;
use App\Models\manualjournal\Journal;
use App\Models\project\Project;
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
   * Project Gross Profit data set
   */
  public function getForProjectGrossProfit()
  {
    $q = Project::query();

    // date filter
    $q->when(request('start_date') && request('end_date'), function ($q) {
      $q->whereBetween('start_date', array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]));
    });

    // status filter 
    switch (request('status')) {
      case 'active':
        $q->whereHas('quotes', fn($q) =>  $q->whereHas('budget')->where('verified', 'No'));
        break;
      case 'complete':
        $q->whereHas('quotes', fn($q) =>  $q->whereHas('budget')->where('verified', 'Yes'));
        break;
    }

    if (request('customer_id')) {
      $q->where('customer_id', request('customer_id'));
      if (request('branch_id')) $q->where('branch_id', request('branch_id'));
    } else $q->limit(500);

    return $q->with(['customer_project', 'quotes', 'purchase_items'])->get();
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

    // increment account number
    $number = Account::where('account_type_id', $input['account_type_id'])->max('number');
    if ($input['number'] <= $number) $input['number'] = $number + 1;

    unset($input['date'], $input['is_multiple']);
    $result = Account::create($input);

    // opening balance
    $opening_balance = $result->opening_balance;
    if ($opening_balance > 0) {
      $seco_account = Account::where('system', 'retained_earning')->first();
      $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
      $date = $result->opening_balance_date;
      $note = $result->number . '-' . $result->holder . ' Account Opening Balance';
      $data = [
        'date' => $date,
        'note' => $note,
        'user_id' => auth()->user()->id,
        'ins' => $result->ins,
      ];

      $entry_type = 'dr';
      $account_type = AccountType::find($result->account_type_id);
      // debit Bank and credit Retained Earnings
      if ($account_type->system == 'bank') {
        $pri_tr = Transactioncategory::where('code', 'dep')->first(['id', 'code']);
        $data = $data + [
          'account_id' => $result->id,
          'amount' => $opening_balance,
          'transaction_ref' => $tid,
          'from_account_id' => $seco_account->id
        ];
        $deposit = Deposit::create($data);

        // transaction
        double_entry(
          $tid,
          $result->id,
          $seco_account->id,
          $opening_balance,
          $entry_type,
          $pri_tr->id,
          'company',
          $deposit->user_id,
          $date,
          $result->opening_balance_date,
          $pri_tr->code,
          $note,
          $result->ins
        );
      } else {
        // debit asset Account and credit Retained Earning
        $pri_tr = Transactioncategory::where('code', 'genjr')->first(['id', 'code']);
        $opening_balance = $opening_balance;
        $data = $data + [
          'tid' => Journal::where('ins', auth()->user()->ins)->max('tid') + 1,
          'debit_ttl' => $opening_balance,
          'credit_ttl' =>  $opening_balance
        ];
        $journal = Journal::create($data);

        foreach ([1, 2] as $v) {
          $item_data = [
            'journal_id' => $journal->id,
            'account_id' => $result->id,
          ];
          if ($v == 1) $item_data['debit'] = $opening_balance;
          else $item_data['credit'] = $opening_balance;
          JournalItem::create($item_data);
        }

        // credit liability Account and debit Retained Earning
        if (in_array($account_type->system, ['other_current_liability', 'long_term_liability', 'equity',]))
          $entry_type = 'cr';

        // transaction
        double_entry(
          $tid,
          $result->id,
          $seco_account->id,
          $opening_balance,
          $entry_type,
          $pri_tr->id,
          'company',
          $journal->user_id,
          $date,
          $result->opening_balance_date,
          $pri_tr->code,
          $note,
          $result->ins
        );
      }
    }

    if ($result) {
      DB::commit();
      return $result;
    }

    throw new GeneralException(trans('exceptions.backend.accounts.create_error'));
  }

  /**
   * For updating the respective Model in storage
   *
   * @param Account $account
   * @param  $input
   * @throws GeneralException
   * @return bool
   */
  public function update($account, array $input)
  {
    // dd($input);
    $input['opening_balance'] = numberClean($input['opening_balance']);
    $input['opening_balance_date'] = date_for_database($input['date']);
    unset($input['date'], $input['is_multiple']);

    $result = $account->update($input);

    // opening balance
    $opening_balance = $account->opening_balance;
    if ($opening_balance > 0) {
      $seco_account = Account::where('system', 'retained_earning')->first();
      $tid = 0;
      $date = $account->opening_balance_date;
      $note = $account->number . '-' . $account->holder . ' Account Opening Balance';
      $data = [
        'date' => $date,
        'note' => $note,
        'user_id' => auth()->user()->id,
        'ins' => $account->ins,
      ];

      $entry_type = 'dr';
      $account_type = AccountType::find($account->account_type_id);
      if ($account_type->system == 'bank') {
        // remove previous transactions
        Transaction::where(['tr_ref' => $account->id, 'tr_type' => 'dep'])->delete();
        Transaction::where(['tr_ref' => $seco_account->id, 'tr_type' => 'dep'])->delete();

        // debit Bank and credit Retained Earnings
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
        $pri_tr = Transactioncategory::where('code', 'dep')->first(['id', 'code']);
        $data = $data + [
          'account_id' => $account->id,
          'amount' => $opening_balance,
          'transaction_ref' => $tid,
          'from_account_id' => $seco_account->id
        ];

        // create or update deposit
        $deposit = Deposit::firstOrNew(['account_id' => $account->id]);
        $deposit->fill($data);
        $deposit->save();

        double_entry(
          $tid,
          $account->id,
          $seco_account->id,
          $opening_balance,
          $entry_type,
          $pri_tr->id,
          'employee',
          $deposit->user_id,
          $date,
          $account->opening_balance_date,
          $pri_tr->code,
          $note,
          $account->ins
        );
      } else {
        // remove previous transactions
        Transaction::where(['tr_ref' => $account->id, 'tr_type' => 'genjr'])->delete();
        Transaction::where(['tr_ref' => $seco_account->id, 'tr_type' => 'genjr'])->delete();

        // debit Asset Account and credit Retained Earning
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
        $pri_tr = Transactioncategory::where('code', 'genjr')->first(['id', 'code']);
        $data = $data + [
          'tid' => Journal::where('ins', auth()->user()->ins)->max('tid') + 1,
          'debit_ttl' => $opening_balance,
          'credit_ttl' =>  $opening_balance
        ];

        // create or update journal
        $journal = Journal::firstOrNew(['note' => $data['note']]);
        if ($journal->tid) unset($data['tid']);
        $journal->fill($data);
        $journal->save();

        // journal items
        if ($journal->items->count()) {
          foreach ($journal->items as $item) {
            if ($item->debit > 0) $item->update(['debit' => $opening_balance]);
            elseif ($item->credit > 0) $item->update(['credit' => $opening_balance]);
          }
        } else {
          foreach ([1, 2] as $v) {
            $item_data = [
              'journal_id' => $journal->id,
              'account_id' => $account->id,
            ];
            if ($v == 1) $item_data['debit'] = $opening_balance;
            else $item_data['credit'] = $opening_balance;
            JournalItem::create($item_data);
          }
        }

        // credit Liability Account and debit Retained Earning
        if (in_array($account_type->system, ['other_current_liability', 'long_term_liability', 'equity']))
          $entry_type = 'cr';

        double_entry(
          $tid,
          $account->id,
          $seco_account->id,
          $opening_balance,
          $entry_type,
          $pri_tr->id,
          'company',
          $journal->user_id,
          $date,
          $account->opening_balance_date,
          $pri_tr->code,
          $note,
          $account->ins
        );
      }
    }

    if ($result) {
      DB::commit();
      return true;
    }

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
    if ($account->transactions->count()) throw ValidationException::withMessages(['Account has attached transactions!']);
    if ($account->system) throw ValidationException::withMessages(['System account cannot be deleted!']);
    if ($account->delete()) {
      aggregate_account_transactions();
      return true;
    }

    throw new GeneralException(trans('exceptions.backend.accounts.delete_error'));
  }
}
