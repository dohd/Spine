<?php

namespace App\Repositories\Focus\supplier;

use DB;
use App\Models\supplier\Supplier;
use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\bill\Bill;
use App\Models\billitem\BillItem;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Storage;

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

    public function getPurchaseorderBillsForDataTable($supplier_id = 0)
    {
        $id = $supplier_id ?: request('supplier_id');
        return Bill::where('supplier_id', $id)->where('po_id', '>', 0)->get();
    }

    public function getTransactionsForDataTable($supplier_id = 0)
    {
        $id = $supplier_id ?: request('supplier_id');
        $q = Transaction::whereHas('account', function ($q) { 
            $q->where('system', 'payable');  
        })->where(function ($q) use($id) {
            $q->whereHas('bill', function ($q) use($id) { 
                $q->where('supplier_id', $id); 
            })->orwhereHas('paidbill', function ($q) use($id) {
                $q->where('supplier_id', $id);
            });
        })->whereIn('tr_type', ['bill', 'pmt']);
        
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
                'note' => 'Balance brought foward as of '. dateFormat($start_date),
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
        foreach ($transactions as $tr_one) {
            if ($tr_one->tr_type == 'bill') {
                $statements->add($tr_one);
                $bill_id = $tr_one->bill->id;
                foreach ($transactions as $tr_two) {
                    if ($tr_two->tr_type == 'pmt') {
                        $tr_exists = false;
                        foreach ($statements as $tr_three) {
                            if ($tr_three->id == $tr_two->id) {
                                $tr_exists = true;
                                break;
                            }
                        }
                        if ($tr_exists) continue;
                        $is_paidbill = $tr_two->paidbill->items->where('bill_id', $bill_id)->count();
                        if ($is_paidbill) $statements->add($tr_two);
                    }
                }
            }
        }

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
        if (!empty($input['picture'])) {
            $input['picture'] = $this->uploadPicture($input['picture']);
        }
        $opening_balance = numberClean($input['opening_balance']);
        if ($opening_balance > 0) {
            $input['opening_balance'] =  $opening_balance;
            $input['opening_balance_date'] = date_for_database($input['opening_balance_date']);
        }
        $supplier_no = Supplier::max('supplier_no');
        $input['supplier_no'] = $supplier_no + 1;
        $input = array_map('strip_tags', $input);
        DB::beginTransaction();
        try {
            $result = Supplier::create($input);
            if ($result) {
                //check if opening balance exist
                if ($opening_balance > 0) {
                    //maxtransaction
                    $tid = Transaction::max('tid');
                    $tid = $tid + 1;
                    $ins = auth()->user()->ins;
                    $duetate = date_for_database($input['opening_balance_date']);
                    $date = date('Y-m-d');
                    $memo = 'Account Opening Balance';
                    //Create a bill
                    $bills = array(
                        'transaction_ref' => $tid,
                        'date' => $date,
                        'due_date' => $duetate,
                        'supplier_type' => 'supplier',
                        'supplier_id' => $result->id,
                        'expense_subtotal_amount' => $opening_balance,
                        'expense_grandtotal_amount' => $opening_balance,
                        'grand_total_amount' => $opening_balance,
                        'note' => $memo,
                        'ins' => $ins,
                        'user_id' => auth()->user()->id,
                    );
                    $bill_save = Bill::create($bills);
                    if ($bill_save) {
                        //bill items
                        $bill_items = array(
                            'bills_id' => $bill_save->id,
                            'description' => $memo,
                            'qty' => 1,
                            'rate' => $opening_balance,
                            'amount' => $opening_balance,
                            'item_type' => 'Expense',
                            'ins' => $ins,
                            'user_id' => auth()->user()->id,
                        );
                        BillItem::create($bill_items);
                    }
                    //credit supplier and debit expense
                    $pri_account = Account::where('system', 'payable')->first();
                    $seco_account = Account::where('system', 'uncategorized_expense')->first();
                    $pri_tr = Transactioncategory::where('code', 'bill')->first();
                    $date = date('Y-m-d');
                    $tr_ref = 'bill';
                    $memo = 'Account Opening Balance';
                    double_entry($tid, $pri_account->id, $seco_account->id, $opening_balance, 'cr', $pri_tr->id, 'supplier', $result->id, $date, date_for_database($input['opening_balance_date']), $tr_ref, $memo, $ins);
                }
                DB::commit();
                return $result;
            }
            //end
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            throw new GeneralException(trans('exceptions.backend.accounts.create_error'));
        }
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
        if (!empty($input['picture'])) {
            $this->removePicture($supplier, 'picture');
            $input['picture'] = $this->uploadPicture($input['picture']);
        }
        $input = array_map('strip_tags', $input);
        if ($supplier->update($input)) return true;
            
        throw new GeneralException(trans('exceptions.backend.suppliers.update_error'));
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
        if ($supplier->purchase_orders->count() || $supplier->bills->count())
            return false;
        if ($supplier->delete()) return true;
        
        throw new GeneralException(trans('exceptions.backend.suppliers.delete_error'));
    }
    /*
 * Upload logo image
 */
    public function uploadPicture($logo)
    {
        $path = $this->person_picture_path;
        $image_name = time() . $logo->getClientOriginalName();
        $this->storage->put($path . $image_name, file_get_contents($logo->getRealPath()));
        return $image_name;
    }
    /*
    * remove logo or favicon icon
    */
    public function removePicture(Supplier $supplier, $type)
    {
        $path = $this->person_picture_path;
        if ($supplier->$type && $this->storage->exists($path . $supplier->$type)) {
            $this->storage->delete($path . $supplier->$type);
        }
        $result = $supplier->update([$type => null]);
        if ($result) {
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.settings.update_error'));
    }
}
