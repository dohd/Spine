<?php

namespace App\Repositories\Focus\billpayment;

use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\billpayment\Billpayment;
use App\Models\items\BillpaymentItem;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * Class ProductcategoryRepository.
 */
class BillPaymentRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Billpayment::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('supplier_id'), function ($q) {
            $q->where('supplier_id', request('supplier_id'));
        });
        
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return \App\Models\billpayment\Billpayment $billpayment
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();
        // sanitize
        foreach ($input as $key => $val) {
            if ($key == 'date') $input[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'allocate_ttl'])) $input[$key] = numberClean($val);
            if (in_array($key, ['paid'])) {
                $input[$key] = array_map(function ($v) { 
                    return numberClean($v); 
                }, $val);
            }
        }
        if($input['amount'] == 0) throw ValidationException::withMessages(['amount required!']);
        $result = Billpayment::create($input);

        $data_items = Arr::only($input, ['bill_id', 'paid']);
        $data_items = array_filter(modify_array($data_items), function ($v) { return $v['paid'] > 0; });
        if (!$data_items) throw ValidationException::withMessages(['Allocate amount on bill!']);

        foreach ($data_items as $key => $val) {
            $val['bill_payment_id'] = $result->id;
            $data_items[$key] = $val;
        }
        BillpaymentItem::insert($data_items);

        // increment supplier on account balance
        $unallocated = $result->amount - $result->allocate_ttl;
        $result->supplier->increment('on_account', $unallocated);

        // increment bill amount paid and update status
        foreach ($result->items as $item) {
            $bill = $item->supplier_bill;
            $bill->increment('amount_paid', $item->paid);
            if ($bill->amount_paid == 0) $bill->update(['status' => 'due']);
            elseif (round($bill->total) > round($bill->amount_paid)) $bill->update(['status' => 'partial']);
            else  $bill->update(['status' => 'paid']);
        }

        /**accounting */
        $this->post_transaction($result);

        DB::commit();
        if ($result) return $result;


        throw new GeneralException('Error Creating Lead');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param \App\Models\billpayment\Billpayment $billpayment
     * @param  array $input
     * @throws GeneralException
     * @return \App\Models\billpayment\Billpayment $billpayment
     */
    public function update(Billpayment $billpayment, array $input)
    {
        dd($input);

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\billpayment\Billpayment $billpayment
     * @throws GeneralException
     * @return bool
     */
    public function delete(Billpayment $billpayment)
    {     
        DB::beginTransaction();
       
        // decrement supplier on account balance
        $unallocated = $billpayment->amount - $billpayment->allocate_ttl;
        $billpayment->supplier->decrement('on_account', $unallocated);

        // decrement bill amount paid and update status
        foreach ($billpayment->items as $item) {
            $bill = $item->supplier_bill;
            $bill->decrement('amount_paid', $item->paid);
            if ($bill->amount_paid == 0) $bill->update(['status' => 'due']);
            elseif (round($bill->total) > round($bill->amount_paid)) $bill->update(['status' => 'partial']);
            else $bill->update(['status' => 'paid']);
        }

        Transaction::where(['tr_type' => 'pmt', 'note' => $billpayment->note, 'tr_ref' => $billpayment->id])->delete();
        aggregate_account_transactions();
        $result = $billpayment->delete();
        if ($result) {
            DB::commit(); 
            return true;
        }
                
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }

    /**
     * Post Bill payment transactions
     * @param \App\Models\billpayment\Billpayment $billpayment
     * @return void
     */
    public function post_transaction($billpayment)
    {
        // credit Accounts Payable (creditor)
        $account = Account::where('system', 'payable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'pmt')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $billpayment->allocate_ttl,
            'tr_date' => $billpayment->date,
            'due_date' => $billpayment->date,
            'user_id' => $billpayment->user_id,
            'note' => $billpayment->note,
            'ins' => $billpayment->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $billpayment->id,
            'user_type' => 'supplier',
            'is_primary' => 1
        ];
        Transaction::create($cr_data);

        // debit bank
        unset($cr_data['credit'], $cr_data['is_primary']);
        $cr_data = array_replace($cr_data, [
            'account_id' => $billpayment->account_id,
            'debit' => $billpayment->allocate_ttl,
        ]);    
        Transaction::create($cr_data);
        aggregate_account_transactions();
    }
}