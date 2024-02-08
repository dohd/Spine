<?php

namespace App\Repositories\Focus\account;

use App\Models\account\Account;
use App\Exceptions\GeneralException;
use App\Models\items\JournalItem;
use App\Models\manualjournal\Journal;
use App\Models\project\Project;
use App\Models\transaction\Transaction;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Class AccountRepository.
 */
class AccountRepository extends BaseRepository
{
  use Accounting;

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
      $q->whereBetween('start_date', array_map(fn ($v) => date_for_database($v), [request('start_date'), request('end_date')]));
    });

    // status filter 
    switch (request('status')) {
      case 'active':
        $q->whereHas('quotes', fn ($q) =>  $q->whereHas('budget')->where('verified', 'No'));
        break;
      case 'complete':
        $q->whereHas('quotes', fn ($q) =>  $q->whereHas('budget')->where('verified', 'Yes'));
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
    DB::beginTransaction();

    $input['opening_balance'] = numberClean($input['opening_balance']);
    $input['opening_balance_date'] = date_for_database($input['date']);
    unset($input['date'], $input['is_multiple']);
    // increment account number
    $number = Account::where('account_type_id', $input['account_type_id'])->max('number');
    if ($input['number'] <= $number) $input['number'] = $number + 1;

    $result = Account::create($input);

    /** opening balance accounting */
    if ($result->opening_balance > 0) {
      $tr_data = $this->opening_balance($result, 'create');
      $this->post_ledger_opening_balance((object) $tr_data);
    }

    if ($result) {
      DB::commit();
      return $result;
    }
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
    DB::beginTransaction();
    $input['opening_balance'] = numberClean($input['opening_balance']);
    $input['opening_balance_date'] = date_for_database($input['date']);
    unset($input['date'], $input['is_multiple']);

    $result = $account->update($input);

    /** account opening balance */
    if ($account->opening_balance > 0) {
      $tr_data = $this->opening_balance($account, 'update');
      $journal = new Journal($tr_data);
      $journal->id = $tr_data['id'];
      $this->post_ledger_opening_balance($journal);
    } else {
      $journal = $account->gen_journal;
      if ($journal) {
        $journal->transactions()->delete();
        $journal->delete();
      }
    }

    if ($result) {
      DB::commit();
      return true;
    }
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
    if ($account->transactions()->exists()) throw ValidationException::withMessages(['Account has attached transactions']);
    if ($account->system) throw ValidationException::withMessages(['System account cannot be deleted!']);
    DB::beginTransaction();
    $account->gen_journal()->delete();
    $result =  $account->delete();
    aggregate_account_transactions();
    if ($result) {
      DB::commit();
      return true;
    }
  }

  /**
   * Ledger Account Opening Balance 
   * @param mixed $ledger_account
   * @param string $method
   */
  public function opening_balance($ledger_account, $method)
  {
    $tr_data = [];
    $opening_balance = $ledger_account->opening_balance;
    $opening_balance_date = $ledger_account->opening_balance_date;
    if ($method == 'create') {
      // create journal
      $journal = Journal::create([
        'tid' => Journal::max('tid') + 1,
        'date' => $opening_balance_date,
        'note' => $ledger_account->note,
        'debit_ttl' => $opening_balance,
        'credit_ttl' => $opening_balance,
        'ins' => $ledger_account->ins,
        'user_id' => $ledger_account->user_id,
        'account_id' => $ledger_account->id,
      ]);
      $retained_earning_acc = Account::where('system', 'retained_earning')->first();
      foreach ([1, 2] as $v) {
        $data = ['journal_id' => $journal->id, 'account_id' => $retained_earning_acc->id];
        if (in_array($ledger_account->account_type, ['Asset', 'Expense'])) {
          if ($v == 1) {
            $data['account_id'] = $ledger_account->id;
            $data['debit'] = $opening_balance;
          } else {
            $data['credit'] = $opening_balance;
          }
        } else {
          if ($v == 1) {
            $data['debit'] = $opening_balance;
          } else {
            $data['account_id'] = $ledger_account->id;
            $data['credit'] = $opening_balance;
          }
        }
        JournalItem::create($data);
      }
      $tr_data = array_replace($journal->toArray(), ['opening_balance' => $opening_balance]);
    } else {
      $journal = Journal::where('account_id', $ledger_account->id)->first();
      if (!$journal) return $this->opening_balance($ledger_account, 'create');
      // update manual journal
      $journal->update([
        'note' => $ledger_account->note,
        'date' => $opening_balance_date,
        'debit_ttl' => $opening_balance,
        'credit_ttl' => $opening_balance,
      ]);
      foreach ($journal->items as $item) {
        if ($item->debit > 0) $item->update(['debit' => $opening_balance]);
        elseif ($item->credit > 0) $item->update(['credit' => $opening_balance]);
      }
      $tr_data = array_replace($journal->toArray(), ['opening_balance' => $opening_balance]);
      $journal->transactions()->delete();
    }
    return $tr_data;
  }
}
