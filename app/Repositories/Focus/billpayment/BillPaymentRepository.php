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
        if ($input['amount'] == 0) 
            throw ValidationException::withMessages(['amount is required!']);

        $tid = Billpayment::where('ins', auth()->user()->ins)->max('tid');
        if ($input['tid'] <= $tid) $input['tid'] = $tid+1;

        $data = array_diff_key($input, array_flip(['balance', 'paid', 'bill_id']));
        $result = Billpayment::create($data);
       
        $data_items = Arr::only($input, ['bill_id', 'paid']);
        $data_items = array_filter(modify_array($data_items), fn($v) => $v['paid'] > 0);
        if ($data_items) {
            foreach ($data_items as $key => $val) {
                $val['bill_payment_id'] = $result->id;
                $data_items[$key] = $val;
            }
            BillpaymentItem::insert($data_items);

            // increment bill amount paid and update status
            foreach ($result->items as $item) {
                $bill = $item->supplier_bill;
                if ($bill) {
                    $bill->increment('amount_paid', $item->paid);
                    if ($bill->amount_paid == 0) $bill->update(['status' => 'due']);
                    elseif (round($bill->total) > round($bill->amount_paid)) $bill->update(['status' => 'partial']);
                    else  $bill->update(['status' => 'paid']);

                    // update purchase status
                    if ($bill->purchase && $bill->purchase->supplier_type) {
                        $purchase = $bill->purchase;
                        $purchase->increment('amountpaid', $item->paid);
                        if ($bill->amount_paid == 0) $bill->update(['status' => 'pending']);
                        elseif (round($bill->total) > round($bill->amount_paid)) $bill->update(['status' => 'partial']);
                        else  $bill->update(['status' => 'paid']);
                    }
                }
            }
        } elseif ($result->payment_type == 'per_invoice') {
            throw ValidationException::withMessages(['Allocation on line items required!']);
        }

        // set allocation type
        $result->allocation_type = '';
        $payment = Billpayment::find($result->rel_payment_id);
        if ($payment) {
            if ($payment->payment_type == 'on_account') 
                $result->allocation_type = 'on_account';
            elseif ($payment->payment_type == 'advance_payment') 
                $result->allocation_type = 'advance_payment';
        }
        
        if ($result->supplier) {
            if ($result->allocation_type) {
                $result->supplier->decrement('on_account', $result->allocate_ttl);
                if ($payment) $payment->increment('allocate_ttl', $result->allocate_ttl);
            } else {
                $unallocated = $result->amount - $result->allocate_ttl;
                $result->supplier->increment('on_account', $unallocated);
            }
        }

        /**accounting */
        $this->post_transaction($result);
                
        if ($result) {
            DB::commit();
            return $result;
        }

        DB::rollBack();
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
            if (in_array($key, ['paid'])) $input[$key] = array_map(fn($v) => numberClean($v), $val);
        }
        if (isset($input['amount']) && $input['amount'] == 0) 
            throw ValidationException::withMessages(['amount is required!']);

        // reverse supplier unallocated amount
        if ($billpayment->supplier_id) {
            $unallocated = $billpayment->amount - $billpayment->allocate_ttl;
            $billpayment->supplier->decrement('on_account', $unallocated);    
        }

        $prev_note = $billpayment->note;
        $data = array_diff_key($input, array_flip(['bill_id', 'paid']));
        $result = $billpayment->update($data);

        // update supplier unallocated amount
        if ($billpayment->supplier_id) {
            $unallocated = $billpayment->amount - $billpayment->allocate_ttl;        
            $billpayment->supplier->increment('on_account', $unallocated);    
        }

        // allocated items
        $data_items = Arr::only($input, ['id', 'bill_id', 'paid']);
        $data_items = modify_array($data_items);
        $item_ids = array_map(fn($v) => $v['id'], $data_items);
        $payment_items = BillpaymentItem::whereIn('id', $item_ids)->get();
        // reverse bill amount paid
        foreach ($payment_items as $item) {
            if ($item->supplier_bill) 
                $item->supplier_bill->decrement('amount_paid', $item->paid);
        }

        // update payment items
        $data_items = array_map(function ($v) {
            return array_replace($v, [
                'paid' => numberClean($v['paid'])
            ]);
        }, $data_items);
        Batch::update(new BillpaymentItem, $data_items, 'id');

        // update bill amount paid
        foreach ($billpayment->items as $item) {
            $bill = $item->supplier_bill;
            if ($bill) {
                $bill->increment('amount_paid', $item->paid);
                if ($bill->amountpaid == 0) $bill->update(['status' => 'due']);
                elseif (round($bill->total) > round($bill->amountpaid)) $bill->update(['status' => 'partial']);
                else $bill->update(['status' => 'paid']);

                // update purchase status
                if ($bill->purchase && $bill->purchase->supplier_type) {
                    $purchase = $bill->purchase;
                    $purchase->increment('amountpaid', $item->paid);
                    if ($bill->amount_paid == 0) $bill->update(['status' => 'pending']);
                    elseif (round($bill->total) > round($bill->amount_paid)) $bill->update(['status' => 'partial']);
                    else  $bill->update(['status' => 'paid']);
                }
            }
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

        DB::rollBack();
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
        // dd($billpayment->toArray());
        DB::beginTransaction();
       
        // decrement supplier on account balance
        if ($billpayment->supplier_id) {
            $payment_type = $billpayment->payment_type;
            if ($payment_type == 'per_invoice' && $billpayment->items->count()) {
                $unallocated = $billpayment->amount - $billpayment->allocate_ttl;
                $billpayment->supplier->decrement('on_account', $unallocated);
                // related payment
                $payment = Billpayment::find($billpayment->rel_payment_id);
                if ($payment) $payment->decrement('allocate_ttl', $billpayment->allocate_ttl);
            } else {
                $billpayment->supplier->decrement('on_account', $billpayment->amount);
            }
        }

        // decrement bill amount paid and update status
        foreach ($billpayment->items as $item) {
            $bill = $item->supplier_bill;
            if ($bill) {
                $bill->decrement('amount_paid', $item->paid);
                if ($bill->amount_paid == 0) $bill->update(['status' => 'due']);
                elseif (round($bill->total) > round($bill->amount_paid)) $bill->update(['status' => 'partial']);
                else $bill->update(['status' => 'paid']);

                // update purchase status
                if ($bill->purchase && $bill->purchase->supplier_type) {
                    $purchase = $bill->purchase;
                    $purchase->decrement('amountpaid', $item->paid);
                    if ($bill->amount_paid == 0) $bill->update(['status' => 'pending']);
                    elseif (round($bill->total) > round($bill->amount_paid)) $bill->update(['status' => 'partial']);
                    else  $bill->update(['status' => 'paid']);
                }
            }
        }

        Transaction::where(['tr_type' => 'pmt', 'note' => $billpayment->note, 'tr_ref' => $billpayment->id])->delete();
        aggregate_account_transactions();
        if ($billpayment->delete()) {
            DB::commit(); 
            return true;
        }
               
        DB::rollBack();
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
            'note' => $billpayment->note,
            'ins' => $billpayment->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $billpayment->id,
            'user_type' => 'supplier',
            'is_primary' => 1
        ];

        // advance payment allocation
        if ($billpayment->allocation_type == 'advance_payment') {
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
        } elseif ($billpayment->payment_type == 'advance_payment') {
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
        } elseif (!$billpayment->allocation_type) {
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
        aggregate_account_transactions();
    }
}