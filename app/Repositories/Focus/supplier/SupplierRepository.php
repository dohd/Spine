<?php

namespace App\Repositories\Focus\supplier;

use DB;
use Carbon\Carbon;
use App\Models\supplier\Supplier;
use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\bill\Bill;
use App\Models\billitem\BillItem;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
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
        return $this->query()
            ->get();
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
                    $pri_tr = Transactioncategory::where('code', 'BILL')->first();
                    $date = date('Y-m-d');
                    $tr_ref = 'BILL';
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
    public function update(Supplier $supplier, array $input)
    {
        if (!empty($input['picture'])) {
            $this->removePicture($supplier, 'picture');
            $input['picture'] = $this->uploadPicture($input['picture']);
        }
        $input = array_map('strip_tags', $input);
        if ($supplier->update($input))
            return true;
        throw new GeneralException(trans('exceptions.backend.suppliers.update_error'));
    }
    /**
     * For deleting the respective model from storage
     *
     * @param Supplier $supplier
     * @return bool
     * @throws GeneralException
     */
    public function delete(Supplier $supplier)
    {
        if ($supplier->delete()) {
            return true;
        }
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
