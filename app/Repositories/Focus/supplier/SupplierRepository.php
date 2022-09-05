<?php

namespace App\Repositories\Focus\supplier;

use DB;
use App\Models\supplier\Supplier;
use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\bill\Bill;
use App\Models\items\JournalItem;
use App\Models\items\UtilityBillItem;
use App\Models\manualjournal\Journal;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Class SupplierRepository.
 */
class SupplierRepository extends BaseRepository
{
    /**
     *customer_picture_path .
     *
     * @var string
     */
    protected $person_picture_path;
    /**
     * Storage Class Object.
     *
     * @var \Illuminate\Support\Facades\Storage
     */
    protected $storage;
    /**
     * Associated Repository Model.
     */
    const MODEL = Supplier::class;
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->person_picture_path = 'img' . DIRECTORY_SEPARATOR . 'supplier' . DIRECTORY_SEPARATOR;
        $this->storage = Storage::disk('public');
    }
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

    public function getBillsForDataTable($supplier_id = 0)
    {
        $id = $supplier_id ?: request('supplier_id');

        return Bill::where('supplier_id', $id)->get();
    }

    public function getTransactionsForDataTable($supplier_id = 0)
    {
        $id = $supplier_id ?: request('supplier_id');

        $q = Transaction::whereHas('account', function ($q) {
            $q->where('system', 'payable');
        })->where(function ($q) use ($id) {
            $q->whereHas('bill', function ($q) use ($id) {
                $q->where('supplier_id', $id);
            });
        })->where('tr_type', 'bill')
            ->orWhere(function ($q) use ($id) {
                $q->whereHas('paidbill', function ($q) use ($id) {
                    $q->where('supplier_id', $id);
                });
            })->whereHas('account', function ($q) {
                $q->where('system', 'payable');
            })->where('tr_type', 'pmt');

        // on date filter
        $start_date = request('start_date');
        $end_date = request('end_date');
        if ($start_date && $end_date && request('is_transaction')) {
            $start_date = date_for_database($start_date);
            $end_date = date_for_database($end_date);
            $prior_date = date('Y-m-d', strtotime($start_date . ' - 1 day'));
            $q1 = clone $q;
            $q2 = clone $q;

            $params = ['id', 'tr_date', 'tr_type', 'note', 'debit', 'credit'];
            $bf_transactions = $q1->where('tr_date', '<', $start_date)->get($params);
            $diff = $bf_transactions->sum('credit') - $bf_transactions->sum('debit');
            $record = (object) array(
                'id' => 0,
                'tr_date' => $prior_date,
                'tr_type' => '',
                'note' => 'Balance brought foward as of ' . dateFormat($start_date),
                'debit' => $diff < 0 ? $diff : 0,
                'credit' => $diff > 0 ? $diff : 0,
            );
            $collection = collect([$record]);
            $transactions = $q2->whereBetween('tr_date', [$start_date, $end_date])->get($params);
            if ($diff > 0) $transactions = $collection->merge($transactions);

            return $transactions;
        }

        return $q->get();
    }

    public function getStatementsForDataTable($supplier_id = 0)
    {
        $id = $supplier_id ?: request('supplier_id');

        $transactions = $this->getTransactionsForDataTable($id);

        // on date filter
        $start_date = request('start_date');
        $end_date = request('end_date');
        if ($start_date && $end_date) {
            $transactions = $transactions->whereBetween('tr_date', [
                date_for_database($start_date),
                date_for_database($end_date)
            ]);
        }

        // sequence of bill and related payments
        $statements = collect();
        $index_visited = array();
        foreach ($transactions as $i => $tr_one) {
            // add bill
            if ($tr_one->tr_type == 'bill') {
                $bill_id = $tr_one->bill->id;
                $statements->add($tr_one);
                $index_visited[] = $i;
                // add payment
                foreach ($transactions as $j => $tr_two) {
                    if ($tr_two->tr_type == 'pmt' && $tr_two->paidbill) {
                        $is_paidbill = $tr_two->paidbill->items->where('bill_id', $bill_id)->count();
                        if ($is_paidbill) {
                            $statements->add($tr_two);
                            $index_visited[] = $j;
                        }
                    }
                }
            }
        }
        // add remaining transactions
        if ($index_visited) {
            foreach ($transactions as $i => $tr) {
                // check if already added and skip
                if (in_array($i, $index_visited, 1)) continue;
                $statements->add($tr);
            }
        } else $statements = $transactions;

        return $statements;
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
        // dd($input);
        $data = $input['data'];
        if (!empty($data['picture'])) $data['picture'] = $this->uploadPicture($data['picture']);

        DB::beginTransaction();

        $account_data = $input['account_data'];
        $data['open_balance'] = numberClean($account_data['open_balance']);
        $data['open_balance_date'] = date_for_database($account_data['open_balance_date']);
        $result = Supplier::create($data);

        $open_balance = $result->open_balance;
        $open_balance_date = $result->open_balance_date;
        if ($open_balance > 0) {
            $note = $result->id . '-supplier Account Opening Balance' . $result->open_balance_note;
            $user_id = auth()->user()->id;

            // unrecognised expense bill
            $bill_data = [
                'supplier_id' => $result->id,
                'document_type' => 'opening_balance',
                'date' => $open_balance_date,
                'due_date' => $open_balance_date,
                'subtotal' => $open_balance,
                'total' => $open_balance,
                'note' => $note,
                'user_id' => $user_id,
                'ins' => auth()->user()->ins,                
            ];
            $bill = UtilityBill::create($bill_data);

            UtilityBillItem::create([
                'bill_id' => $bill->id,
                'note' => $note,
                'qty' => 1,
                'subtotal' => $bill->subtotal,
                'total' => $bill->total
            ]);

            // recognise expense as journal entry
            if ($result->expense_account_id) {
                $data = [
                    'tid' => Journal::max('tid') + 1,
                    'date' => $open_balance_date,
                    'note' => $note,
                    'debit_ttl' => $open_balance,
                    'credit_ttl' => $open_balance,
                    'ins' => $result->ins,
                    'user_id' => $user_id
                ];
                $journal = Journal::create($data);
    
                $creditor_account = Account::where('system', 'payable')->first(['id']);
                foreach ([1, 2] as $v) {
                    $data = [
                        'journal_id' => $journal->id,
                        'account_id' => $creditor_account->id,
                    ];
                    if ($v == 1) $data['credit'] = $open_balance;
                    else {
                        $balance_account = Account::where('system', 'retained_earning')->first(['id']);
                        $data['account_id'] = $balance_account->id;
                        $data['debit'] = $open_balance;
                    }
                    JournalItem::create($data);
                }

                /**accounting */
                $data = array_replace($journal->toArray(), [
                    'open_balance' => $open_balance,
                    'account_id' => $creditor_account->id
                ]);
                $this->post_transaction((object) $data);
            } 
        }

        DB::commit();
        if ($result) return $result;
    }


    /**
     * For updating the respective Model in storage
     *
     * @param Supplier $supplier
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($supplier, array $input)
    {
        // dd($input);
        $data = $input['data'];

        if (!empty($input['picture'])) {
            $this->removePicture($supplier, 'picture');
            $data['picture'] = $this->uploadPicture($data['picture']);
        }

        DB::beginTransaction();

        $account_data = $input['account_data'];
        $data = array_replace($data, [
            'open_balance' => numberClean($account_data['open_balance']),
            'open_balance_date' => date_for_database($account_data['open_balance_date']),
            'open_balance_note' => $account_data['open_balance_note'],
            'expense_account_id' => $account_data['expense_account_id'],
        ]);
        $result = $supplier->update($data);

        $open_balance = $supplier->open_balance;
        $open_balance_date = $supplier->open_balance_date;
        if ($open_balance > 0) {
            $user_id = auth()->user()->id;
            $note = $supplier->id .  '-customer Account Opening Balance ' . $supplier->open_balance_note;

            $data = array();
            $journal = Journal::where('note', 'LIKE', '%' . $supplier->id .  '-customer Account Opening Balance ' . '%')->first();
            if ($journal) {
                // remove previous transactions
                Transaction::where(['tr_ref' => $journal->id, 'note' => $journal->note])->delete();

                $bill = BillUtility::where('note', $journal->note)->first();
                if ($bill) {
                    $bill->update([
                        'date' => $open_balance_date,
                        'due_date' => $open_balance_date,
                        'subtotal' => $open_balance,
                        'total' => $open_balance,
                        'note' => $note,
                    ]);   
                    $bill->item->update([
                        'subtotal' => $open_balance,
                        'total' => $open_balance,
                        'note' => $note,
                    ]);
                }
                
                if ($supplier->expense_account_id) {
                    $journal->update([
                        'note' => $note,
                        'date' => $open_balance_date,
                        'debit_ttl' => $open_balance,
                        'credit_ttl' => $open_balance,
                    ]);

                    $account = Account::where('system', 'payable')->first(['id']);
                    $data = array_replace($journal->toArray(), [
                        'open_balance' => $open_balance,
                        'account_id' => $account->id
                    ]);

                    foreach ($journal->items as $item) {
                        if ($item->debit > 0) $item->update(['debit' => $open_balance]);
                        elseif ($item->credit > 0) $item->update(['credit' => $open_balance]);
                    }
                } else $journal->delete();
                
            } else {
                // unrecognised expense
                $bill_data = [
                    'supplier_id' => $supplier->id,
                    'document_type' => 'opening_balance',
                    'date' => $open_balance_date,
                    'due_date' => $open_balance_date,
                    'subtotal' => $open_balance,
                    'total' => $open_balance,
                    'note' => $note,
                    'user_id' => $user_id,
                    'ins' => auth()->user()->ins,                
                ];
                $bill = UtilityBill::create($bill_data);
    
                UtilityBillItem::create([
                    'bill_id' => $bill->id,
                    'note' => $note,
                    'qty' => 1,
                    'subtotal' => $bill->subtotal,
                    'total' => $bill->total
                ]);

                // recognise expense as a journal entry
                if ($supplier->expense_account_id) {
                    $data = [
                        'tid' => Journal::max('tid') + 1,
                        'date' => $open_balance_date,
                        'note' => $note,
                        'debit_ttl' => $open_balance,
                        'credit_ttl' => $open_balance,
                        'ins' => $supplier->ins,
                        'user_id' => $user_id,
                    ];
                    $journal = Journal::create($data);

                    $creditor_account = Account::where('system', 'payable')->first(['id']);
                    foreach ([1, 2] as $v) {
                        $data = [
                            'journal_id' => $journal->id,
                            'account_id' => $creditor_account->id,
                        ];
                        if ($v == 1) $data['credit'] = $open_balance;
                        else {
                            $balance_account = Account::where('system', 'retained_earning')->first(['id']);
                            $data['account_id'] = $balance_account->id;
                            $data['debit'] = $open_balance;
                        }
                        JournalItem::create($data);
                    }

                    $data = array_replace($journal->toArray(), [
                        'open_balance' => $open_balance,
                        'account_id' => $creditor_account->id
                    ]);
                }                
            }

            /**accounting */
            if ($data) $this->post_transaction((object) $data);
        }

        DB::commit();
        if ($result) return $result;

        throw new GeneralException(trans('exceptions.backend.suppliers.update_error'));
    }

    /**
     * Supplier Opening Balance Transaction
     * @param object $result
     */
    public function post_transaction($result)
    {
        // credit Accounts Payable (Creditor)
        $tr_category = Transactioncategory::where('code', 'genjr')->first(['id', 'code']);
        $cr_data = [
            'tid' => Transaction::max('tid') + 1,
            'account_id' => $result->account_id,
            'trans_category_id' => $tr_category->id,
            'tr_date' => $result->date,
            'due_date' => $result->date,
            'user_id' => $result->user_id,
            'note' => $result->note,
            'credit' => $result->open_balance,
            'ins' => $result->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $result->id,
            'user_type' => 'supplier',
            'is_primary' => 1,
        ];
        Transaction::create($cr_data);

        // debit Retained Earning (Equity)
        unset($cr_data['credit'], $cr_data['is_primary']);
        $account = Account::where('system', 'retained_earning')->first(['id']);
        $dr_data = array_replace($cr_data, ['account_id' => $account->id, 'debit' => $result->open_balance]);
        Transaction::create($dr_data);

        aggregate_account_transactions();
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Supplier $supplier
     * @return bool
     * @throws GeneralException
     */
    public function delete($supplier)
    {

        if ($supplier->bills->count())
            throw ValidationException::withMessages(['Supplier has attached Bill!']);
        if ($supplier->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.suppliers.delete_error'));
    }

    /*
    * Upload logo image
    */
    public function uploadPicture($logo)
    {
        $image_name = $this->person_picture_path . time() . $logo->getClientOriginalName();
        $this->storage->put($image_name, file_get_contents($logo->getRealPath()));

        return $image_name;
    }

    /*
    * remove logo or favicon icon
    */
    public function removePicture(Supplier $supplier, $type)
    {
        if ($supplier->$type) {
            $image = $this->person_picture_path . $supplier->type;
            if ($this->storage->exists($image)) $this->storage->delete($image);
        }
        if ($supplier->update([$type => null])) return true;

        throw new GeneralException(trans('exceptions.backend.settings.update_error'));
    }
}
