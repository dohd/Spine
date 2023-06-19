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
use Error;
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
        
        return $q;
    }

    /**
     * Import Expenses from external array data
     */
    function expense_import_data($file_name = '') {
        try {
            $expense_data = [];

            $file = base_path() . '/main_creditors/' . $file_name;
            if (!file_exists($file)) return $expense_data;
            // dd($file);

            // convert csv to array
            $export = [];
            $csv_file = fopen($file, 'r');
            while ($row = fgetcsv($csv_file)) $export[] = $row;
            fclose($csv_file);
            // dd($export);

            // compatible database array
            $import = [];
            $headers = current($export);
            $data_rows = array_slice($export, 1, count($export));
            foreach ($data_rows as $i => $row) {
                $new_row = [];
                foreach ($row as $key => $val) {
                    if (stripos($val, 'null') !== false) $val = null;
                    $new_row[$headers[$key]] = $val; 
                }
                $import[] = $new_row;
            }
            // dd($import);

            // expense and expense_items
            foreach ($import as $key => $data) {
                $is_payment = (stripos($data['status'], 'pmt') !== false);
                if (!$is_payment) continue;
                unset($data['id'], $data['created_at'], $data['updated_at']);
                $data['date'] = date_for_database($data['date']);
                // dd($data);

                $account_name = current(explode(' ', $data['doc_ref_type']));
                $account = Account::whereNull('system')
                    ->whereHas('accountType', fn($q) =>  $q->where('system', 'bank'))
                    ->where('holder', 'LIKE', "%{$account_name}%")->first();
                    
                $data = array_map(fn($v) => [
                    'tid' => 1,
                    'account_id' => $account? $account->id : 0,
                    'payment_type' => 'on_account',
                    'supplier_id' => $v['supplier_id'],
                    'date' => $v['date'],
                    'amount' => $v['grandttl'],
                    'allocate_ttl' => 0,
                    'reference' => $v['doc_ref'],
                    'payment_mode' => 'eft',
                    'note' => $v['note'],
                ], [$data])[0];

                $expense_data[] = $data;
            }
            return $expense_data;
        } catch (\Throwable $th) {
            $err = $th->getMessage();
            throw new Error("{$err} on file {$file_name}");
        }
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
            if (in_array($key, ['paid'])) 
                $input[$key] = array_map(fn($v) => numberClean($v), $val);
        }

        if ($input['amount'] == 0) throw ValidationException::withMessages(['amount is required']);
        if (empty($input['rel_payment_id'])) {
            if (@$input['reference'] && @$input['account_id']) {
                $ref_exists = Billpayment::where('account_id', $input['account_id'])
                    ->where('reference', 'LIKE', "%{$input['reference']}%")  
                    ->whereNull('rel_payment_id')->exists();            
                if ($ref_exists) throw ValidationException::withMessages(['Duplicate reference no.']);
            }
        }  

        // create payment
        $tid = Billpayment::where('ins', auth()->user()->ins)->max('tid');
        if ($input['tid'] <= $tid) $input['tid'] = $tid+1;
        $data = array_diff_key($input, array_flip(['balance', 'paid', 'bill_id']));
        $result = Billpayment::create($data);

        // payment line items
        $data_items = Arr::only($input, ['bill_id', 'paid']);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['paid'] > 0);
        if (!$data_items && $result->payment_type == 'per_invoice') 
            throw ValidationException::withMessages(['amount allocation on line items required!']);
        
        foreach ($data_items as $key => $val) {
            $data_items[$key]['bill_payment_id'] = $result->id;
        }
        BillpaymentItem::insert($data_items);

        // update bill amount_paid and status
        foreach ($result->items as $item) {
            $bill = $item->supplier_bill;
            if ($bill) {
                $bill->increment('amount_paid', $item->paid);
                if ($bill->amount_paid == 0) $bill->update(['status' => 'due']);
                elseif (round($bill->total) > round($bill->amount_paid)) $bill->update(['status' => 'partial']);
                else  $bill->update(['status' => 'paid']);

                // update purchase amount_paid and status
                if ($bill->document_type == 'direct_purchase' && $bill->purchase) {
                    $purchase = $bill->purchase;
                    $purchase->increment('amountpaid', $item->paid);
                    if ($bill->amountpaid == 0) $bill->update(['status' => 'pending']);
                    elseif (round($bill->grandttl) > round($bill->amountpaid)) $bill->update(['status' => 'partial']);
                    else $bill->update(['status' => 'paid']);
                }
            }
        }

        // update supplier on_account balance
        if ($result->supplier) {
            // payment
            if (!$result->rel_payment_id) {
                if (in_array($result->payment_type, ['on_account', 'advance_payment'])) {
                    $result->supplier->increment('on_account', $result->amount);
                }
            }

            // allocated payment
            if ($result->payment_type == 'per_invoice' && $result->rel_payment_id) {
                $result->supplier->decrement('on_account', $result->allocate_ttl);
                $rel_payment = Billpayment::find($result->rel_payment_id);
                if ($rel_payment) {
                    $rel_payment->increment('allocate_ttl', $result->allocate_ttl);
                    if ($rel_payment->payment_type == 'advance_payment') $result->is_advance_allocation = true;
                    // check over allocation
                    $diff = round($rel_payment->amount - $rel_payment->allocate_ttl);
                    if ($diff < 0) throw ValidationException::withMessages(['Allocation limit reached! Please reduce allocated amount by ' . numberFormat($diff*-1)]);
                    
                }
            }
        }
        
        /**accounting */
        if (!$result->rel_payment_id || $result->is_advance_allocation) {
            $this->post_transaction($result);
        }
                
        if ($result) {
            DB::commit();
            return $result;
        }

        DB::rollBack();
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
            if (in_array($key, ['paid'])) $input[$key] = array_map(fn($v) => numberClean($v), $val);
        }

        if (@$input['amount'] == 0) throw ValidationException::withMessages(['amount is required']);
        if (empty($input['rel_payment_id'])) {
            if (@$input['reference'] && @$input['account_id']) {
                $ref_exists = Billpayment::where('id', '!=', $billpayment->id)
                ->where('account_id', $input['account_id'])
                    ->where('reference', 'LIKE', "%{$input['reference']}%")  
                    ->whereNull('rel_payment_id')->exists();            
                if ($ref_exists) throw ValidationException::withMessages(['Duplicate reference no.']);
            }  
        }

        // delete billpayment with no unallocated line items
        $data_items = Arr::only($input, ['id', 'bill_id', 'paid']);
        if (!$data_items && $billpayment->payment_type == 'per_invoice') 
            return $this->delete($billpayment);

        DB::beginTransaction();

        $prev_note = $billpayment->note;
        $prev_reference = $billpayment->reference;

        // reverse supplier on_account balance
        if ($billpayment->supplier) {
            // payment
            if (!$billpayment->rel_payment_id) {
                if (in_array($billpayment->payment_type, ['on_account', 'advance_payment'])) {
                    $billpayment->supplier->decrement('on_account', $billpayment->amount);
                }
            }

            // allocated payment
            if ($billpayment->payment_type == 'per_invoice' && $billpayment->rel_payment_id) {
                $billpayment->supplier->increment('on_account', $billpayment->allocate_ttl);
                $rel_payment = Billpayment::find($billpayment->rel_payment_id);
                if ($rel_payment) {
                    $rel_payment->decrement('allocate_ttl', $billpayment->allocate_ttl);
                    if ($rel_payment->payment_type == 'advance_payment') $billpayment->is_advance_allocation = true;
                    // check over allocation
                    $diff = round($rel_payment->amount - $rel_payment->allocate_ttl);
                    if ($diff < 0) throw ValidationException::withMessages(['Allocation limit reached! Please reduce allocated amount by ' . numberFormat($diff*-1)]);
                }
            }
        }

        // update payment
        $data = array_diff_key($input, array_flip(['bill_id', 'paid']));
        $result = $billpayment->update($data);

        // update supplier on_account balance
        if ($billpayment->supplier) {
            // payment
            if (!$billpayment->rel_payment_id) {
                if (in_array($billpayment->payment_type, ['on_account', 'advance_payment'])) {
                    $billpayment->supplier->increment('on_account', $billpayment->amount);
                }
            }

            // allocated payment
            if ($billpayment->payment_type == 'per_invoice' && $billpayment->rel_payment_id) {
                $billpayment->supplier->decrement('on_account', $billpayment->allocate_ttl);
                $rel_payment = Billpayment::find($billpayment->rel_payment_id);
                if ($rel_payment) {
                    $rel_payment->increment('allocate_ttl', $billpayment->allocate_ttl);
                    if ($rel_payment->payment_type == 'advance_payment') $billpayment->is_advance_allocation = true;
                }
            }
        }

        // update payment items, bills and related purchases
        $data_items = modify_array($data_items);
        foreach ($billpayment->items as $pmt_item) {
            $bill = $pmt_item->supplier_bill;
            $purchase = null;
            if ($bill) {
                $bill->decrement('amount_paid', $pmt_item->paid);
                if ($bill->document_type == 'direct_purchase' && $bill->purchase) {
                    $purchase = $bill->purchase;
                    $purchase->decrement('amountpaid', $pmt_item->paid);
                }
            }            

            $is_allocated = 0;
            foreach ($data_items as $data_item) {
                if ($data_item['id'] == $pmt_item->id) {
                    $is_allocated = 1;
                    $pmt_item->update(['paid' => $data_item['paid']]);
                    // update bill status
                    if ($bill) {
                        $bill->increment('amount_paid', $data_item['paid']);
                        if ($bill->amountpaid == 0) $bill->update(['status' => 'due']);
                        elseif (round($bill->total) > round($bill->amountpaid)) $bill->update(['status' => 'partial']);
                        else $bill->update(['status' => 'paid']);
                    }
                    // update purchase status
                    if ($bill && $purchase) {
                        $purchase->increment('amountpaid', $data_item['paid']);
                        if ($purchase->amountpaid == 0) $purchase->update(['status' => 'pending']);
                        elseif (round($purchase->grandttl) > round($purchase->amountpaid)) $purchase->update(['status' => 'partial']);
                        else $purchase->update(['status' => 'paid']);
                    }
                }
            }
            if (!$is_allocated) $pmt_item->delete();
        }

        // check if payment is advance_payment allocation
        if ($billpayment->rel_payment_id) {
            $rel_payment = Billpayment::find($billpayment->rel_payment_id);
            if ($rel_payment && $rel_payment->payment_type == 'advance_payment')
                $billpayment->is_advance_allocation = true;
        }
        
        /** accounting */
        if (!$billpayment->rel_payment_id || $billpayment->is_advance_allocation) {
            Transaction::whereIn('tr_type', ['pmt', 'supplier_adv_pmt']) 
            ->where(['tr_ref' => $billpayment->id, 'user_type' => 'supplier'])
            ->where(function($q) use($prev_note, $prev_reference) {
                $q->where('note', 'LIKE', "%{$prev_note}%")
                ->orwhere('note', 'LIKE', "%{$prev_reference}%");
            })
            ->delete();

            $this->post_transaction($billpayment);
        }

        if ($result) {
            DB::commit();
            return true;
        }

        DB::rollBack();
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
        // dd($billpayment->id);
        DB::beginTransaction();

        // check if contain related allocations
        $has_allocations = Billpayment::where('rel_payment_id', $billpayment->id)->exists();
        if ($has_allocations) throw ValidationException::withMessages([
            'Delete related payment allocations to proceed'
        ]);

        // reverse supplier on_account balance
        if ($billpayment->supplier_id) {
            if (!$billpayment->rel_payment_id) {
                if (in_array($billpayment->payment_type, ['on_account', 'advance_payment'])){
                    $billpayment->supplier->decrement('on_account', $billpayment->amount);
                }
            } else {
                $billpayment->supplier->increment('on_account', $billpayment->allocate_ttl);
                $payment = Billpayment::find($billpayment->rel_payment_id);
                if ($payment) $payment->decrement('allocate_ttl', $billpayment->allocate_ttl);
            }
        }

        // reverse bill amount_paid and status
        foreach ($billpayment->items as $item) {
            $bill = $item->supplier_bill;
            if ($bill) {
                $bill->decrement('amount_paid', $item->paid);
                if ($bill->amount_paid == 0) $bill->update(['status' => 'due']);
                elseif (round($bill->total) > round($bill->amount_paid)) $bill->update(['status' => 'partial']);
                else $bill->update(['status' => 'paid']);

                // update purchase status
                if ($bill->document_type == 'direct_purchase' && $bill->purchase) {
                    $purchase = $bill->purchase;
                    $purchase->decrement('amountpaid', $item->paid);
                    if ($bill->amountpaid == 0) $bill->update(['status' => 'pending']);
                    elseif (round($bill->total) > round($bill->amountpaid)) $bill->update(['status' => 'partial']);
                    else  $bill->update(['status' => 'paid']);
                }
            }
        }

        Transaction::whereIn('tr_type', ['pmt', 'supplier_adv_pmt']) 
        ->where(['tr_ref' => $billpayment->id, 'user_type' => 'supplier'])
        ->where(function($q) use($billpayment) {
            $q->where('note', 'LIKE', "%{$billpayment->reference}%")
            ->orwhere('note', 'LIKE', "%{$billpayment->note}%");
        })
        ->delete();
        aggregate_account_transactions();
        
        if ($billpayment->delete()) {
            DB::commit(); 
            return true;
        }
    
        DB::rollBack();
    }

    /**
     * Post Bill payment transactions
     * 
     * @param \App\Models\billpayment\Billpayment $billpayment
     * @return void
     */
    public function post_transaction($billpayment)
    {   
        // default liability accounts
        $account = Account::where('system', 'payable')->first(['id']);
        if ($billpayment->employee_id) $account = Account::where('system', 'adv_salary')->first(['id']);
            
        $tr_category = Transactioncategory::where('code', 'pmt')->first(['id', 'code']);
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid')+1;
        $dr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'debit' => $billpayment->amount,
            'tr_date' => $billpayment->date,
            'due_date' => $billpayment->date,
            'user_id' => $billpayment->user_id,
            'note' => ($billpayment->note ?: "{$billpayment->payment_mode} - {$billpayment->reference}"),
            'ins' => $billpayment->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $billpayment->id,
            'user_type' => 'supplier',
            'is_primary' => 1
        ];

        if ($billpayment->is_advance_allocation) {
            // debit payables (liability)
            Transaction::create($dr_data);
            
            // credit supplier advance payment 
            unset($dr_data['debit'], $dr_data['is_primary']);
            $account = Account::where('system', 'supplier_adv_pmt')->first(['id']);
            $cr_data = array_replace($dr_data, [
                'account_id' => $account->id,
                'credit' => $billpayment->amount,
            ]);    
            Transaction::create($cr_data);
        } else {
            if ($billpayment->payment_type == 'advance_payment') {
                // debit supplier advance payment 
                $account = Account::where('system', 'supplier_adv_pmt')->first(['id']);
                $dr_data['account_id'] = $account->id;
                Transaction::create($dr_data);
                
                // credit bank
                unset($dr_data['debit'], $dr_data['is_primary']);
                $cr_data = array_replace($dr_data, [
                    'account_id' => $billpayment->account_id,
                    'credit' => $billpayment->amount,
                ]);    
                Transaction::create($cr_data);
            } else {
                // debit payables (liability)
                Transaction::create($dr_data);
                            
                // credit bank
                unset($dr_data['debit'], $dr_data['is_primary']);
                $cr_data = array_replace($dr_data, [
                    'account_id' => $billpayment->account_id,
                    'credit' => $billpayment->amount,
                ]);    
                Transaction::create($cr_data);
            }
        }
        aggregate_account_transactions();
    }
}