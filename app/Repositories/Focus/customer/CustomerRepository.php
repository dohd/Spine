<?php

namespace App\Repositories\Focus\customer;

use App\Models\customergroup\CustomerGroupEntry;
use App\Models\items\CustomEntry;
use DB;
use App\Models\customer\Customer;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Storage;
use App\Models\branch\Branch;
use App\Models\invoice\Invoice;
use App\Models\transaction\Transaction;

/**
 * Class CustomerRepository.
 */
class CustomerRepository extends BaseRepository
{

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
        $q->when(request('g_rel_type'), function ($q) {

            return $q->where('rel_id', '=',request('g_rel_id',-1));
        });
        if (!request('g_rel_type') AND request('g_rel_id')) {
            $q->whereHas('group', function ($s) {
                return $s->where('customer_group_id', '=', request('g_rel_id', 0));
            });
        }
        return $q->get(['id','name','company','email','address','picture','active','created_at']);
    }

    public function getInvoicesForDataTable($customer_id = 0)
    {
        $id = $customer_id ?: request('customer_id');
        return Invoice::where('customer_id', $id)->get();
    }

    public function getTransactionsForDataTable($customer_id = 0)
    {
        $id = $customer_id ?: request('customer_id');
        $q = Transaction::whereHas('account', function ($q) { 
            $q->where('system', 'receivable');  
        })->where(function ($q) use($id) {
            $q->whereHas('invoice', function ($q) use($id) { 
                $q->where('customer_id', $id); 
            })->orwhereHas('paidinvoice', function ($q) use($id) {
                $q->where('customer_id', $id);
            })->orwhereHas('withholding', function ($q) use($id) {
                $q->where('customer_id', $id);
            })->orwhereHas('creditnote', function ($q) use($id) {
                $q->where('customer_id', $id);
            })->orwhereHas('debitnote', function ($q) use($id) {
                $q->where('customer_id', $id);
            });
        })->whereIn('tr_type', ['rcpt', 'pmt', 'withholding', 'cnote', 'dnote']);

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
            $diff = $bf_transactions->sum('debit') - $bf_transactions->sum('credit');
            $record = (object) array(
                'id' => 0,
                'tr_date' => $prior_date,
                'tr_type' => '',
                'note' => 'Balance brought foward as of '. dateFormat($start_date),
                'debit' => $diff > 0 ? $diff : 0,
                'credit' => $diff < 0 ? $diff : 0,
            );
            $collection = collect([$record]);
            $transactions = $q2->whereBetween('tr_date', [$start_date, $end_date])->get($params);
            if ($diff > 0) $transactions = $collection->merge($transactions);

            return $transactions;
        }

        return $q->get();
    }

    public function getStatementsForDataTable($customer_id = 0)
    {
        $id = $customer_id ?: request('customer_id');
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

        // sequence of invoices and related payments
        $statements = collect();
        $index_visited = array();
        foreach ($transactions as $i => $tr_one) {
            if ($tr_one->tr_type == 'pmt') {
                // add invoice, withholding, cnote, dnote
                $statements->add($tr_one);
                $invoice_id = $tr_one->invoice->id;
                $customer_id = $tr_one->invoice->customer_id;
                foreach ($transactions as $j => $tr_two) {
                    $types = ['rcpt', 'withholding', 'cnote', 'dnote'];
                    if (in_array($tr_two->tr_type, $types, 1)) { 
                        $exists = false;                       
                        if ($tr_two->paidinvoice) {
                            $is_paidinvoice = $tr_two->paidinvoice->items->where('invoice_id', $invoice_id)->count();
                            if ($is_paidinvoice) $exists = true; 
                        } elseif ($tr_two->creditnote && $tr_two->creditnote->invoice_id == $invoice_id) {
                            $exists = true; 
                        } elseif ($tr_two->debitnote && $tr_two->debitnote->invoice_id == $invoice_id) {
                            $exists = true; 
                        } elseif ($tr_two->withholding && $tr_two->withholding->customer_id == $customer_id) {
                            $exists = false;
                            // add payment
                            $statements->add($tr_one);
                            $index_visited[] = $i;
                            // add withholding
                            $statements->add($tr_two);
                            $index_visited[] = $j;
                        }                                                                 
                        if ($exists) {
                            $statements->add($tr_two);
                            $index_visited[] = $j;
                        }
                    }
                }
            }
        }
        // add remainder transactions
        if ($index_visited) {
            foreach ($transactions as $i => $tr) {
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
        DB::beginTransaction();
        
        if (!empty($input['picture'])) 
            $input['picture'] = $this->uploadPicture($input['picture']);
        
        $customer = Customer::where('email', $input['email'])->first('id');
        if ($customer) return session()->flash('flash_error', 'Duplicate Email');

        $groups = isset($input['groups']) ? $input['groups'] : array();
        $custom_field = isset($input['custom_field']) ? $input['custom_field'] : array();
        unset($input['groups'], $input['custom_field']);
        $result = Customer::create($input);

        $branches = [['name' => 'All Branches'], ['name' => 'Head Office']];
        foreach ($branches as $k => $branch) {
            $branch['customer_id'] = $result->id;
            $branch['ins'] = $result->ins;
            $branches[$k] = $branch;
        }
        Branch::insert($branches);

        $groups = array_reduce($groups, function ($init, $val) use($result) {
            $init[] = ['customer_id' => $result->id, 'customer_group_id' => $val];
            return $init;
        }, []);
        if ($groups) CustomerGroupEntry::insert($groups);

        $fields = array();
        foreach ($custom_field as $k => $val) {
            $fields[] = [
                'custom_field_id' => $k,
                'rid' => $result->id,
                'module' => 1,
                'data' => $val,
                'ins' => $result->ins
            ];
        }
        if ($fields) CustomEntry::insert($fields);

        DB::commit();
        if ($result) return $result;
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
        // dd($input, $customer->id);
        DB::beginTransaction();

        if (!empty($input['picture'])) {
            $this->removePicture($customer, 'picture');
            $input['picture'] = $this->uploadPicture($input['picture']);
        }
        if (empty($input['password'])) unset($input['password']);

        $is_email = Customer::whereNotIn('id', [$customer->id])->where('email', $input['email'])->first('id');
        if ($is_email) {
            session()->flash('flash_error', 'Duplicate Email');
            return false;
        }

        $groups = isset($input['groups']) ? $input['groups'] : array();
        $custom_field = isset($input['custom_field']) ? $input['custom_field'] : array();
        unset($input['groups'], $input['custom_field']);
        $customer->update($input);

        if ($groups)  {
            $groups = array_reduce($groups, function ($init, $val) use($customer) {
                $init[] = ['customer_id' => $customer->id, 'customer_group_id' => $val];
                return $init;
            }, []);    
            CustomerGroupEntry::where('customer_id',  $customer->id)->delete();
            CustomerGroupEntry::insert($groups);
        }
        if ($custom_field) {
            $fields = array();
            foreach ($custom_field as $k => $val) {
                $fields[] = [
                    'custom_field_id' => $k,
                    'rid' => $customer->id,
                    'module' => 1,
                    'data' => $val,
                    'ins' => $customer->ins
                ];
                CustomEntry::where(['custom_field_id' => $k, 'rid' => $customer->id])->delete();
            }
            CustomEntry::insert($fields);
        }

        DB::commit();
        return true;

        throw new GeneralException(trans('exceptions.backend.customers.update_error'));
    }

    /*
 * Upload logo image
 */
    public function uploadPicture($logo)
    {
        $path = $this->customer_picture_path;

        $image_name = time() . $logo->getClientOriginalName();

        $this->storage->put($path . $image_name, file_get_contents($logo->getRealPath()));

        return $image_name;
    }

    /*
    * remove logo or favicon icon
    */
    public function removePicture(Customer $customer, $type)
    {
        $path = $this->customer_picture_path;

        if ($customer->$type && $this->storage->exists($path . $customer->$type)) {
            $this->storage->delete($path . $customer->$type);
        }

        $result = $customer->update([$type => null]);

        if ($result) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.settings.update_error'));
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
        if ($customer->leads()->first()) return;
        if ($customer->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.customers.delete_error'));
    }
}
