<?php

namespace App\Repositories\Focus\customer;

use DB;
use App\Models\customer\Customer;
use App\Exceptions\GeneralException;
use App\Http\Controllers\ClientSupplierAuth;
use App\Models\account\Account;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Storage;
use App\Models\branch\Branch;
use App\Models\Company\Company;
use App\Models\invoice\Invoice;
use App\Models\items\JournalItem;
use App\Models\manualjournal\Journal;
use App\Models\transaction\Transaction;
use App\Repositories\Accounting;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * Class CustomerRepository.
 */
class CustomerRepository extends BaseRepository
{
    use Accounting, CustomerStatement, ClientSupplierAuth;

    /**
     *customer_picture_path .
     *
     * @var string
     */
    protected $customer_picture_path;


    /**
     * Storage Class Object.
     *
     * @var \Illuminate\Support\Facades\Storage
     */
    protected $storage;

    /**
     * Associated Repository Model.
     */
    const MODEL = Customer::class;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->customer_picture_path = 'img' . DIRECTORY_SEPARATOR . 'customer' . DIRECTORY_SEPARATOR;
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

        // customer user filter
        $customer_id = auth()->user()->customer_id;
        $q->when($customer_id, fn($q) => $q->where('id', $customer_id));
        
        return $q->get(['id','name','company','email','address','picture','active','created_at']);
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
        DB::beginTransaction();

        $user_data = Arr::only($input, ['first_name', 'last_name', 'email', 'password', 'picture']);
        $user_data['email'] = @$input['user_email'];
        unset($input['first_name'], $input['last_name'], $input['user_email']);

        if (isset($input['picture'])) $input['picture'] = $this->uploadPicture($input['picture']);
            
        $is_company = Customer::where('company', $input['company'])->exists();
        if ($is_company) throw ValidationException::withMessages(['Company already exists']);
        $email_exists = Customer::where('email', $input['email'])->whereNotNull('email')->exists();
        if ($email_exists) throw ValidationException::withMessages(['Duplicate email']);

        if (@$input['taxid']) {
            $taxid_exists = Customer::where('taxid', $input['taxid'])->whereNotNull('taxid')->exists();
            if ($taxid_exists) throw ValidationException::withMessages(['Duplicate Tax Pin']);
            $is_company = Company::where(['id' => auth()->user()->ins, 'taxid' => $input['taxid']])->whereNotNull('taxid')->exists();
            if ($is_company) throw ValidationException::withMessages(['Company Tax Pin not allowed']);
            if (strlen($input['taxid']) != 11) 
                throw ValidationException::withMessages(['Customer Tax Pin should contain 11 characters!']);
            if (!in_array($input['taxid'][0], ['P','A'])) 
                throw ValidationException::withMessages(['First character of Tax Pin must be letter "P" or "A"']);
            $pattern = "/^[0-9]+$/i";
            if (!preg_match($pattern, substr($input['taxid'],1,9))) 
                throw ValidationException::withMessages(['Character between first and last letters must be numbers']);
            $letter_pattern = "/^[a-zA-Z]+$/i";
            if (!preg_match($letter_pattern, $input['taxid'][-1])) 
                throw ValidationException::withMessages(['Last character of Tax Pin must be a letter!']);
        }
        
        // create customer
        $input['open_balance'] = numberClean($input['open_balance']);
        $input['open_balance_date'] = date_for_database($input['open_balance_date']);  
        $result = Customer::create($input);
        // create branches
        $branches = [['name' => 'All Branches'], ['name' => 'Head Office']];
        foreach ($branches as $key => $branch) {
            $branches[$key]['customer_id'] = $result->id;
            $branches[$key]['ins'] = $result->ins;
        }
        Branch::insert($branches);

        // opening balance
        $open_balance = $result->open_balance;
        if ($open_balance > 0) {
            $open_balance_date = $result->open_balance_date;
            // create journal
            $journal = Journal::create([
                'tid' => Journal::where('ins', auth()->user()->ins)->max('tid') + 1,
                'date' => $open_balance_date,
                'note' => $result->open_balance_note,
                'debit_ttl' => $open_balance,
                'credit_ttl' => $open_balance,
                'ins' => $result->ins,
                'user_id' => $result->user_id,
                'customer_id' => $result->id,
            ]);
            $debtor_account = Account::where('system', 'receivable')->first(['id']);
            foreach ([1,2] as $v) {
                $data = ['journal_id' => $journal->id,'account_id' => $debtor_account->id];
                if ($v == 1) {
                    $data['debit'] = $open_balance;
                } else {
                    $balance_account = Account::where('system', 'retained_earning')->first(['id']);
                    $data['account_id'] = $balance_account->id;
                    $data['credit'] = $open_balance;
                }   
                JournalItem::create($data);
            }
            Invoice::create([
                'invoicedate' => $open_balance_date,
                'invoiceduedate' => $open_balance_date,
                'subtotal' => $open_balance,
                'total' => $open_balance,
                'notes' => $result->open_balance_note,
                'customer_id' => $result->id,
                'user_id' => $result->user_id,
                'ins' => $result->ins,
                'man_journal_id' => $journal->id
            ]);
            /** accounting */
            $tr_data = array_replace($journal->toArray(), ['open_balance' => $open_balance,'account_id' => $debtor_account->id]);
            $this->post_customer_opening_balance((object) $tr_data);
        }

        // authorize
        $this->createAuth($result, $user_data, 'client');

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Customer $customer
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($customer, array $input)
    { 
        DB::beginTransaction();

        $user_data = Arr::only($input, ['first_name', 'last_name', 'password', 'picture']);
        $user_data['email'] = @$input['user_email'];
        unset($input['first_name'], $input['last_name'], $input['user_email']);

        if (isset($input['picture'])) {
            $this->removePicture($customer, 'picture');
            $input['picture'] = $this->uploadPicture($input['picture']);
        }
        if (empty($input['password'])) unset($input['password']);

        $is_company = Customer::where('id', '!=', $customer->id)->where('company', $input['company'])->exists();
        if ($is_company) throw ValidationException::withMessages(['Company already exists']);
        $email_exists = Customer::where('id', '!=', $customer->id)->where('email', $input['email'])->whereNotNull('email')->exists();
        if ($email_exists) throw ValidationException::withMessages(['Email already in use']);

        if (@$input['taxid']) {
            $taxid_exists = Customer::where('id', '!=', $customer->id)->where('taxid', $input['taxid'])->whereNotNull('taxid')->exists();
            if ($taxid_exists) throw ValidationException::withMessages(['Duplicate Tax Pin']);
            $is_company = Company::where(['id' => auth()->user()->ins, 'taxid' => $input['taxid']])->whereNotNull('taxid')->exists();
            if ($is_company) throw ValidationException::withMessages(['Company Tax Pin not allowed']);
            if (strlen($input['taxid']) != 11) 
                throw ValidationException::withMessages(['Customer Tax Pin should contain 11 characters']);
            if (!in_array($input['taxid'][0], ['P','A'])) 
                throw ValidationException::withMessages(['First character of Tax Pin must be letter "P" or "A"']);
            $pattern = "/^[0-9]+$/i";
            if (!preg_match($pattern, substr($input['taxid'],1,9))) 
                throw ValidationException::withMessages(['Character between first and last letters must be numbers']);
            $letter_pattern = "/^[a-zA-Z]+$/i";
            if (!preg_match($letter_pattern, $input['taxid'][-1])) 
                throw ValidationException::withMessages(['Last character of Tax Pin must be a letter!']);
        }
        
        $input = array_replace($input, [
            'open_balance' => numberClean($input['open_balance']),
            'open_balance_date' =>  date_for_database($input['open_balance_date'])
        ]);
        $result = $customer->update($input);

        // opening balance
        $open_balance = $customer->open_balance;
        if ($open_balance > 0) {
            $data = [];
            $open_balance_date = $customer->open_balance_date;
            $note = $customer->open_balance_note;
            $journal = Journal::where('customer_id', $customer->id)->first();
            if ($journal) {
                // update invoice
                $invoice = Invoice::where('man_journal_id', $journal->id)->first();
                if ($invoice) {
                    $invoice->update([
                        'notes' => $note, 
                        'subtotal' => $open_balance, 
                        'total' => $open_balance,
                    ]);   
                }
                // update manual journal
                $journal->update([
                    'note' => $note,
                    'date' => $open_balance_date,
                    'debit_ttl' => $open_balance,
                    'credit_ttl' => $open_balance,
                ]);
                foreach ($journal->items as $item) {
                    if ($item->debit > 0) $item->update(['debit' => $open_balance]);
                    elseif ($item->credit > 0) $item->update(['credit' => $open_balance]);
                }
                $debtor_account = Account::where('system', 'receivable')->first(['id']); 
                $tr_data = array_replace($journal->toArray(), [
                    'open_balance' => $open_balance,
                    'account_id' => $debtor_account->id,
                ]);
                Transaction::where('man_journal_id', $journal->id)->delete();
            } else {
                // create manual journal
                $journal = Journal::create([
                    'tid' => Journal::where('ins', auth()->user()->ins)->max('tid') + 1,
                    'date' => $open_balance_date,
                    'note' => $note,
                    'debit_ttl' => $open_balance,
                    'credit_ttl' => $open_balance,
                    'ins' => $customer->ins,
                    'user_id' => $customer->user_id,
                    'customer_id' => @$customer->id,
                ]);
                $debtor_account = Account::where('system', 'receivable')->first(['id']);
                foreach ([1,2] as $v) {
                    $data = ['journal_id' => $journal->id,'account_id' => $debtor_account->id];
                    if ($v == 1) {
                        $data['debit'] = $open_balance;
                    } else {
                        $balance_account = Account::where('system', 'retained_earning')->first(['id']);
                        $data['account_id'] = $balance_account->id;
                        $data['credit'] = $open_balance;
                    }   
                    JournalItem::create($data);
                }
                Invoice::create([
                    'invoicedate' => $open_balance_date,
                    'invoiceduedate' => $open_balance_date,
                    'subtotal' => $open_balance,
                    'total' => $open_balance,
                    'notes' => $note,
                    'customer_id' => $customer->id,
                    'user_id' => $customer->user_id,
                    'ins' => $customer->ins,
                    'man_journal_id' => @$journal->id,
                ]);
                $tr_data = array_replace($journal->toArray(), [
                    'open_balance' => $open_balance,
                    'account_id' => $debtor_account->id
                ]);
            }
            /**accounting */    
            if ($tr_data) $this->post_customer_opening_balance((object) $tr_data);    
        }    
        
        // authorize
        $this->updateAuth($customer, $user_data, 'client');
        
        if ($result) {
            DB::commit();
            return true;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Customer $customer
     * @return bool
     * @throws GeneralException
     */
    public function delete($customer)
    {
        if ($customer->id == 1) throw ValidationException::withMessages(['Cannot delete default customer']);
        if ($customer->leads->exists()) throw ValidationException::withMessages(['Customer has attached Tickets']);
        DB::beginTransaction();

        $this->deleteAuth($customer, 'client');
        $result = $customer->delete();

        if ($result) {
            DB::commit();
            return true;
        }
    }

    /*
    * Upload logo image
    */
    public function uploadPicture($file)
    {
        $image = time() . $file->getClientOriginalName();
        $this->storage->put($this->customer_picture_path . $image, file_get_contents($file->getRealPath()));
        return $image;
    }

    /*
    * Remove logo or favicon icon
    */
    public function removePicture(Customer $customer, $type)
    {
        $path = $this->customer_picture_path;
        $storage_exists = $this->storage->exists($path . $customer->$type);
        if ($customer->$type && $storage_exists) {
            $this->storage->delete($path . $customer->$type);
        }
        return $customer->update([$type => '']);    
    }
}
