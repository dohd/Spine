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
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;

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

        foreach ($input as $key => $val) {
            if ($key == 'date') $input[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'allocate_ttl'])) $input[$key] = numberClean($val);
            if (in_array($key, ['paid'])) {
                $input[$key] = array_map(function ($v) { 
                    return numberClean($v); 
                }, $val);
            }
        }
        if ($input['amount'] == 0) throw ValidationException::withMessages(['amount is required!']);
            
        $tid = Billpayment::max('tid');
        if ($input['tid'] <= $tid) $input['tid'] = $tid+1;
        $result = Billpayment::create($input);

        $data_items = Arr::only($input, ['bill_id', 'paid']);
        $data_items = array_filter(modify_array($data_items), function ($v) { return $v['paid'] > 0; });
        if (!$data_items) throw ValidationException::withMessages(['Allocate amount on payment line items!']);

        foreach ($data_items as $key => $val) {
            $val['bill_payment_id'] = $result->id;
            $data_items[$key] = $val;
        }
        BillpaymentItem::insert($data_items);

        // increment supplier on account balance
        if ($result->supplier) {
            $unallocated = $result->amount - $result->allocate_ttl;
            $result->supplier->increment('on_account', $unallocated);
        }
        
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

        if ($result) {
            DB::commit();
            return $result;
        }

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
        // dd($input);
        foreach ($input as $key => $val) {
            if ($key == 'date') $input[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'allocate_ttl'])) $input[$key] = numberClean($val);
            if (in_array($key, ['paid'])) {
                $input[$key] = array_map(function ($v) { 
                    return numberClean($v); 
                }, $val);
            }
        }
        if($input['amount'] == 0) throw ValidationException::withMessages(['amount is required!']);

        // reverse supplier unallocated amount
        if ($billpayment->supplier_id) {
            $unallocated = $billpayment->amount - $billpayment->allocate_ttl;
            $billpayment->supplier->decrement('on_account', $unallocated);    
        }

        $prev_note = $billpayment->note;
        $result = $billpayment->update($input);

        // update supplier unallocated amount
        if ($billpayment->supplier_id) {
            $unallocated = $billpayment->amount - $billpayment->allocate_ttl;        
            $billpayment->supplier->increment('on_account', $unallocated);    
        }

        // allocated items
        $data_items = Arr::only($input, ['id', 'bill_id', 'paid']);
        $data_items = modify_array($data_items);
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $payment_items = BillpaymentItem::whereIn('id', $item_ids)->get();
        // reverse bill amount paid
        foreach ($payment_items as $item) {
            if ($item->supplier_bill) $item->supplier_bill->decrement('amount_paid', $item->paid);
        }
        // update payment items
        $data_items = array_map(function ($v) {
            return array_replace($v, [
                'paid' => numberClean($v['paid'])
            ]);
        }, $data_items);
        Batch::update(new BillpaymentItem, $data_items, 'id');

        foreach ($billpayment->items as $item) {
            // update bill amount paid
            $bill = $item->supplier_bill;
            if (!$bill) continue;

            $bill->increment('amount_paid', $item->paid);
            if ($bill->amountpaid == 0) $bill->update(['status' => 'due']);
            elseif (round($bill->total) > round($bill->amountpaid)) $bill->update(['status' => 'partial']);
            else $bill->update(['status' => 'paid']);
            
            // delete items with zero payment
            if ($item->paid == 0) $item->delete();
        }

        /** accounting */
        Transaction::where(['tr_type' => 'pmt', 'note' => $prev_note, 'tr_ref' => $billpayment->id])->delete();
        $this->post_transaction($billpayment);

        if ($result) {
            DB::commit();
            return true;
        }

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
        if ($billpayment->supplier_id) {
            $unallocated = $billpayment->amount - $billpayment->allocate_ttl;
            $billpayment->supplier->decrement('on_account', $unallocated);
        }

        // decrement bill amount paid and update status
        foreach ($billpayment->items as $item) {
            $bill = $item->supplier_bill;
            if ($bill) {
                $bill->decrement('amount_paid', $item->paid);
                if ($bill->amount_paid == 0) $bill->update(['status' => 'due']);
                elseif (round($bill->total) > round($bill->amount_paid)) $bill->update(['status' => 'partial']);
                else $bill->update(['status' => 'paid']);
            }
        }

        Transaction::where([
            'tr_type' => 'pmt', 
            'note' => $billpayment->note, 
            'tr_ref' => $billpayment->id
        ])->delete();
        aggregate_account_transactions();

        if ($billpayment->delete()) {
            DB::commit(); 
            return true;
        }
                
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }

    /**
     * Post Bill payment transactions
     * 
     * @param \App\Models\billpayment\Billpayment $billpayment
     * @return void
     */
    public function post_transaction($billpayment)
    {
        // for supplier, debit Accounts Payable (creditor)
        // for employee, debit Advance Salary Account
        $account = null;
        if ($billpayment->supplier_id)
            $account = Account::where('system', 'payable')->first(['id']);
        if ($billpayment->employee_id)  
            $account = Account::where('system', 'adv_salary')->first(['id']);
            
        $tr_category = Transactioncategory::where('code', 'pmt')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $dr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'debit' => $billpayment->allocate_ttl,
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
        Transaction::create($dr_data);

        // credit bank
        unset($dr_data['debit'], $dr_data['is_primary']);
        $cr_data = array_replace($dr_data, [
            'account_id' => $billpayment->account_id,
            'credit' => $billpayment->allocate_ttl,
        ]);    
        Transaction::create($cr_data);
        aggregate_account_transactions();
    }
}