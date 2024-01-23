<?php

namespace App\Repositories\Focus\supplier;

use DB;
use App\Models\supplier\Supplier;
use App\Exceptions\GeneralException;
use App\Http\Controllers\ClientSupplierAuth;
use App\Models\account\Account;
use App\Models\billpayment\Billpayment;
use App\Models\Company\Company;
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
    use ClientSupplierAuth;
    
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
        $q = $this->query();

        // supplier user filter
        $supplier_id = auth()->user()->supplier_id;
        $q->when($supplier_id, fn($q) => $q->where('id', $supplier_id));

        return $q->get();
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
        $data = $input['data'];
        if (isset($data['picture'])) $data['picture'] = $this->uploadPicture($data['picture']);

        if (@$data['taxid']) {
            $taxid_exists = Supplier::where('taxid', $data['taxid'])->whereNotNull('taxid')->exists();
            if ($taxid_exists) throw ValidationException::withMessages(['Duplicate Tax Pin']);
            $is_company = Company::where(['id' => auth()->user()->ins, 'taxid' => $data['taxid']])->exists();
            if ($is_company) throw ValidationException::withMessages(['Company Tax Pin not allowed!']);
            if (strlen($data['taxid']) != 11) 
                throw ValidationException::withMessages(['Supplier Tax Pin should contain 11 characters']);
            if (!in_array($data['taxid'][0], ['P','A'])) 
                throw ValidationException::withMessages(['First character of Tax Pin must be letter "P" or "A"']);
            $pattern = "/^[0-9]+$/i";
            if (!preg_match($pattern, substr($data['taxid'],1,9))) 
                throw ValidationException::withMessages(['Characters between 2nd and 10th letters must be numbers']);
            $letter_pattern = "/^[a-zA-Z]+$/i";
            if (!preg_match($letter_pattern, $data['taxid'][-1])) 
                throw ValidationException::withMessages(['Last character of Tax Pin must be a letter']);
        }

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
                    'tid' => Journal::where('ins', auth()->user()->ins)->max('tid') + 1,
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

        // authorize
        $this->createAuth($result, $input['user_data'], 'supplier');

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
        DB::beginTransaction();

        $data = $input['data'];
        if (isset($data['picture'])) {
            $this->removePicture($supplier, 'picture');
            $data['picture'] = $this->uploadPicture($data['picture']);
        }

        if (@$data['taxid']) {
            $taxid_exists = Supplier::where('id', '!=', $supplier->id)->where('taxid', $data['taxid'])->whereNotNull('taxid')->exists();
            if ($taxid_exists) throw ValidationException::withMessages(['Duplicate Tax Pin']);
            $is_company = Company::where(['id' => auth()->user()->ins, 'taxid' => $data['taxid']])->exists();
            if ($is_company) throw ValidationException::withMessages(['Company Tax Pin is not allowed!']);
            if (strlen($data['taxid']) != 11) 
                throw ValidationException::withMessages(['Supplier Tax Pin should contain 11 characters!']);
            if (!in_array($data['taxid'][0], ['P','A'])) 
                throw ValidationException::withMessages(['Initial character of Tax Pin must be letter "P" or "A"']);
            $pattern = "/^[0-9]+$/i";
            if (!preg_match($pattern, substr($data['taxid'],1,9))) 
                throw ValidationException::withMessages(['Character between first and last letters must be numbers']);
            $letter_pattern = "/^[a-zA-Z]+$/i";
            if (!preg_match($letter_pattern, $data['taxid'][-1])) 
                throw ValidationException::withMessages(['Last character of Tax Pin must be a letter!']);
        }

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
        $journal_data = [];
        if ($open_balance > 0) {
            $user_id = auth()->user()->id;
            $note = $supplier->id .  '-supplier Account Opening Balance ' . $supplier->open_balance_note;
            $journal = Journal::where('supplier_id', $supplier->id)
            ->orWhere('note', 'LIKE', "%{$supplier->id}-supplier Account Opening Balance %")->first();
            if ($journal) {
                // remove previous transactions
                Transaction::where('man_journal_id', $journal->id)
                ->orWhere(function($q) use($journal) {
                    $q->where('tr_ref', $journal->id)->where('note', 'LIKE', "%{$journal->note}%");
                })->delete();

                // update bill
                $bill = UtilityBill::where('man_journal_id', $journal->id)
                ->orWhere('note', 'LIKE', "{$journal->note}")->first();
                if ($bill) {
                    $bill->update([
                        'date' => $open_balance_date,
                        'due_date' => $open_balance_date,
                        'subtotal' => $open_balance,
                        'total' => $open_balance,
                        'note' => $note,
                    ]);   
                    if ($bill->item) {
                        $bill->item->update([
                            'subtotal' => $open_balance,
                            'total' => $open_balance,
                            'note' => $note,
                        ]);
                    }
                }

                // update journal
                $journal->update([
                    'note' => $note,
                    'date' => $open_balance_date,
                    'debit_ttl' => $open_balance,
                    'credit_ttl' => $open_balance,
                ]);
                $account = Account::where('system', 'payable')->first(['id']);
                $journal_data = array_replace($journal->toArray(), [
                    'open_balance' => $open_balance,
                    'account_id' => $account->id
                ]);
                foreach ($journal->items as $item) {
                    if ($item->debit > 0) $item->update(['debit' => $open_balance]);
                    elseif ($item->credit > 0) $item->update(['credit' => $open_balance]);
                }
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
                    'ins' => $supplier->ins,                
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
                        'tid' => Journal::where('ins', auth()->user()->ins)->max('tid')+1,
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

                    $journal_data = array_replace($journal->toArray(), [
                        'open_balance' => $open_balance,
                        'account_id' => $creditor_account->id
                    ]);
                }               
            }
            /**accounting */
            if ($journal_data) $this->post_transaction((object) $journal_data);
        }

        // authorize
        $this->updateAuth($supplier, $input['user_data'], 'supplier');

        if ($result) {
            DB::commit();
            return $result;
        }
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
            'tid' => Transaction::where('ins', auth()->user()->ins)->max('tid') + 1,
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
            'supplier_id' => @$result->supplier_id,
            'man_journal_id' => @$result->id,
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
        if ($supplier->id == 1) throw ValidationException::withMessages(['Cannot delete default supplier']);
        if ($supplier->bills->count())
            throw ValidationException::withMessages(['Supplier has attached Bill!']);
        if ($this->deleteAuth($supplier, 'supplier') && $supplier->delete()) return true;

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
