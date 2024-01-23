<?php

namespace App\Repositories\Focus\invoice_payment;

use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\invoice\Invoice;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\items\InvoicePaymentItem;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Validation\ValidationException;

class InvoicePaymentRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = InvoicePayment::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('customer_id'), function ($q) {
            $q->where('customer_id', request('customer_id'));
        });
            
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return InvoicePayment $payment
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        foreach ($data as $key => $val) {
            if ($key == 'date') $data[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'allocate_ttl'])) 
                $data[$key] = numberClean($val);
        }

        if ($data['amount'] == 0) throw ValidationException::withMessages(['amount is required']);
        // check duplicate Reference No.
        $is_allocation = isset($data['rel_payment_id']);
        if (!$is_allocation && @$data['reference'] && @$data['account_id']) {
            $is_duplicate_ref = InvoicePayment::where('account_id', $data['account_id'])
            ->where('reference', 'LIKE', "%{$data['reference']}%")  
            ->whereNull('rel_payment_id')
            ->exists();            
            if ($is_duplicate_ref) throw ValidationException::withMessages(['Duplicate reference no.']);
        }

        // payment line items
        $data_items = $input['data_items'];
        $data_items = array_filter($data_items, fn($v) => $v['paid'] > 0);
        if (!$data_items && @$data['payment_type'] == 'per_invoice') {
            throw ValidationException::withMessages(['amount allocation on line items required!']);
        }

        // create payment
        $tid = InvoicePayment::max('tid');
        if ($data['tid'] <= $tid) $data['tid'] = $tid+1;
        $result = InvoicePayment::create($data);
        foreach ($data_items as $key => $val) {
            $data_items[$key]['paidinvoice_id'] = $result->id;
            $data_items[$key]['paid'] = numberClean($val['paid']);
        }
        InvoicePaymentItem::insert($data_items);

        // update invoice amountpaid and status
        foreach ($result->items as $item) {
            $invoice = $item->invoice;
            if ($invoice) {
                $invoice->increment('amountpaid', $item->paid);
                if ($invoice->amountpaid == 0) $invoice->update(['status' => 'due']);
                elseif (round($invoice->total) > round($invoice->amountpaid)) $invoice->update(['status' => 'partial']);
                else  $invoice->update(['status' => 'paid']);
            }
        }

        // update customer on_account balance
        if ($result->customer) {
            // non-allocation lumpsome payment 
            if (!$is_allocation && in_array($result->payment_type, ['on_account', 'advance_payment'])) {
                $result->customer->increment('on_account', $result->amount);
            }
            // allocation payment
            if ($is_allocation && $result->payment_type == 'per_invoice') {
                $lumpsome_pmt = InvoicePayment::find($result->rel_payment_id);
                if ($lumpsome_pmt) {
                    if ($lumpsome_pmt->payment_type == 'advance_payment') $result->is_advance_allocation = true;
                    $result->customer->decrement('on_account', $result->allocate_ttl);
                    $lumpsome_pmt->increment('allocate_ttl', $result->allocate_ttl);
                    // check over allocation
                    $diff = round($lumpsome_pmt->amount - $lumpsome_pmt->allocate_ttl);
                    if ($diff < 0) throw ValidationException::withMessages(['Allocation limit reached! Please reduce allocated amount by ' . numberFormat($diff*-1)]);
                } 
            }
        }
    
        /**accounting */
        if (!$is_allocation || $result->is_advance_allocation) {
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
     * @param InvoicePayment $invoice_payment
     * @param array $input
     * @throws GeneralException
     * return bool
     */
    public function update($invoice_payment, array $input)
    {
        // dd($input); 
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if ($key == 'date') $data[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'allocate_ttl'])) $data[$key] = numberClean($val);
        }

        if ($data['amount'] == 0) throw ValidationException::withMessages(['amount is required']);
        // check duplicate Reference No.
        $is_allocation = isset($data['rel_payment_id']);
        if (!$is_allocation && @$data['reference'] && @$data['account_id']) {
            $is_duplicate_ref = InvoicePayment::where('id', '!=', $invoice_payment->id)
            ->where('account_id', $data['account_id'])
            ->where('reference', 'LIKE', "%{$data['reference']}%")  
            ->whereNull('rel_payment_id')
            ->exists();            
            if ($is_duplicate_ref) throw ValidationException::withMessages(['Duplicate reference no.']);
        }
            
        // delete invoice_payment having unallocated line items
        $data_items = $input['data_items'];
        if (!$data_items && $invoice_payment->payment_type == 'per_invoice') {
            return $this->delete($invoice_payment);
        }
            
        DB::beginTransaction(); 

        $prev_reference = $invoice_payment->reference;
        $prev_note = $invoice_payment->note;

        // update payment
        $result = $invoice_payment->update($data);
        foreach ($invoice_payment->items as $pmt_item) {
            $invoice = $pmt_item->invoice;                
            $is_allocated = false;
            foreach ($data_items as $data_item) {
                if ($data_item['id'] == $pmt_item->id) {
                    $is_allocated = true;
                    $data_item['paid'] = numberClean($data_item['paid']);
                    $pmt_item->update(['paid' => $data_item['paid']]);
                }
            }
            if (!$is_allocated) $pmt_item->delete();
        }

        // compute customer on account balance
        if ($invoice_payment->customer) {
            $customer_id = $invoice_payment->customer->id;
            $lumpsome_amount = InvoicePayment::where('customer_id', $customer_id)
            ->whereIn('payment_type', ['on_account', 'advance_payment'])->sum('amount');
            $lumpsome_allocated = InvoicePayment::whereIn('rel_payment_id', function($q) use($customer_id) {
                $q->select('id')->from('paid_invoices')
                ->where('customer_id', $customer_id)
                ->whereIn('payment_type', ['on_account', 'advance_payment']);
            })
            ->where('customer_id', $customer_id)
            ->where('payment_type', 'per_invoice')
            ->sum('allocate_ttl');
            $invoice_payment->customer->update('on_account', round($lumpsome_amount-$lumpsome_allocated));            
        }

        // compute lumpsome payment balance
        $lumpsome_pmt = InvoicePayment::find($invoice_payment->rel_payment_id);
        if ($lumpsome_pmt) {
            $lumpsome_allocated = InvoicePayment::where('rel_payment_id', $invoice_payment->rel_payment_id)
            ->where('payment_type', 'per_invoice')
            ->sum('allocate_ttl');
            $lumpsome_pmt->update('allocate_ttl', $lumpsome_allocated);

            if ($lumpsome_pmt->payment_type == 'advance_payment') $invoice_payment->is_advance_allocation = true;
            // check over allocation
            $diff = round($lumpsome_pmt->amount - $lumpsome_pmt->allocate_ttl);
            if ($diff < 0) throw ValidationException::withMessages(['Allocation limit reached! Please reduce allocated amount by ' . numberFormat($diff*-1)]);
        }

        // compute invoice amount_paid balance
        $invoice_ids = $invoice_payment->items()->pluck('invoice_id')->toArray();
        $invoices = Invoice::whereIn('id', $invoice_ids)->get();
        foreach ($invoices as $key => $invoice) {
            $amount_paid = InvoicePaymentItem::whereHas('paid_invoice')->where('invoice_id', $invoice->id)->sum('paid');
            $invoice->update('amountpaid', $amount_paid);
            if ($invoice->amountpaid == 0) $invoice->update(['status' => 'due']);
            elseif (round($invoice->total) > round($invoice->amountpaid)) $invoice->update(['status' => 'partial']);
            else $invoice->update(['status' => 'paid']);
        }
        
        /** accounting */
        if (!$is_allocation || $invoice_payment->is_advance_allocation) {
            Transaction::whereIn('tr_type', ['dep', 'adv_dep'])
            ->where(['tr_ref' => $invoice_payment->id, 'user_type' => 'customer'])
            ->where(function($q) use($prev_reference, $prev_note, $invoice_payment) {
                $q->where('note', 'LIKE', "%{$prev_reference}%")
                ->orWhere('note', 'LIKE', "%{$prev_note}%")
                ->orWehere('deposit_id', $invoice_payment->id);
            })
            ->delete();
            $this->post_transaction($invoice_payment);
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
     * @param InvoicePayment $payment
     * @throws GeneralException
     * @return bool
     */
    public function delete(InvoicePayment $invoice_payment)
    {
        // check if lumpsome payment has allocations
        $payment_type = $invoice_payment->payment_type;
        if (in_array($payment_type, ['on_account', 'advance_payment'])) {
            $has_allocations = InvoicePayment::where('rel_payment_id', $invoice_payment->id)->exists();
            if ($has_allocations) throw ValidationException::withMessages(['Delete related payment allocations to proceed']);    
        }

        DB::beginTransaction();
        $invoice_payment_id = $invoice_payment->id;
        $allocation_id = $invoice_payment->rel_payment_id;
        $is_allocation = isset($invoice_payment->rel_payment_id);
        $invoice_payment_ref = $invoice_payment->reference;
        $invoice_payment_note = $invoice_payment->note;
        $customer = $invoice_payment->customer;
        $invoice_ids = $invoice_ids = $invoice_payment->items()->pluck('invoice_id')->toArray();
    
        $invoice_payment->items()->delete();
        $result =  $invoice_payment->delete();

        // customer on account balances
        if ($customer && in_array($payment_type, ['on_account', 'advance_payment'])) {
            $customer_id = $customer->id;
            $lumpsome_amount = InvoicePayment::where('customer_id', $customer_id)
            ->whereIn('payment_type', ['on_account', 'advance_payment'])
            ->sum('amount');
            $lumpsome_allocated = InvoicePayment::whereIn('rel_payment_id', function($q) use($customer_id) {
                $q->select('id')->from('paid_invoices')
                ->where('customer_id', $customer_id)
                ->whereIn('payment_type', ['on_account', 'advance_payment']);
            })
            ->where('customer_id', $customer_id)
            ->where('payment_type', 'per_invoice')
            ->sum('allocate_ttl');
            $customer->update('on_account', round($lumpsome_amount-$lumpsome_allocated));            
        }
        
        // invoice balances
        if ($payment_type == 'per_invoice') {
            $invoices = Invoice::whereIn('id', $invoice_ids)->get();
            foreach ($invoices as $key => $invoice) {
                $amount_paid = InvoicePaymentItem::whereHas('paid_invoice')->where('invoice_id', $invoice->id)->sum('paid');
                $invoice->update('amountpaid', $amount_paid);
                if ($invoice->amountpaid == 0) $invoice->update(['status' => 'due']);
                elseif (round($invoice->total) > round($invoice->amountpaid)) $invoice->update(['status' => 'partial']);
                else $invoice->update(['status' => 'paid']);
            }
        }

        // lumpsome payment balances
        $is_advance_allocation = false;
        if (in_array($payment_type, ['on_account', 'advance_payment'])) {
            $lumpsome_pmt = InvoicePayment::find($allocation_id);
            if ($lumpsome_pmt) {
                $lumpsome_allocated = InvoicePayment::where('rel_payment_id', $allocation_id)
                ->where('payment_type', 'per_invoice')
                ->sum('allocate_ttl');
                $lumpsome_pmt->update('allocate_ttl', $lumpsome_allocated);
                if ($payment_type == 'advance_payment') $is_advance_allocation = true;
            } 
        }
       
        /** accounting */
        if (!$is_allocation || $is_advance_allocation) {
            Transaction::whereIn('tr_type', ['dep', 'adv_dep'])
            ->where(['tr_ref' => $invoice_payment_id, 'user_type' => 'customer'])
            ->where(function($q) use($invoice_payment_id, $invoice_payment_ref, $invoice_payment_note) {
                $q->where('note', 'LIKE', "%{$invoice_payment_ref}%")
                ->orWhere('note', 'LIKE', "%{$invoice_payment_note}%")
                ->orWhere('deposit_id', $invoice_payment_id);
            })
            ->delete();
            aggregate_account_transactions();
        }
        
        if ($result) {
            DB::commit(); 
            return true;
        }         
    }

    /**
     * Post Invoice Payment Transaction
     */
    public function post_transaction($invoice_payment)
    {
        $account = Account::where('system', 'receivable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'dep')->first(['id', 'code']);
        $tid = Transaction::where('ins', $invoice_payment->ins)->max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $invoice_payment->amount,
            'tr_date' => $invoice_payment->date,
            'due_date' => $invoice_payment->date,
            'user_id' => $invoice_payment->user_id,
            'note' => ($invoice_payment->note ?: "{$invoice_payment->payment_mode} - {$invoice_payment->reference}"),
            'ins' => $invoice_payment->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $invoice_payment->id,
            'user_type' => 'customer',
            'is_primary' => 1,
            'customer_id' => @$invoice_payment->customer_id,
            'deposit_id' => @$invoice_payment->id,
        ];

        if ($invoice_payment->is_advance_allocation) {
            // credit Receivables (Debtors)
            Transaction::create($cr_data);
            
            // debit customer Advance DEP
            unset($cr_data['credit'], $cr_data['is_primary']);
            $account = Account::where('system', 'adv_dep')->first(['id']);
            $dr_data = array_replace($cr_data, [
                'account_id' => $account->id,
                'debit' => $invoice_payment->amount,
            ]);    
            Transaction::create($dr_data);
        } else {
            /**
             * Non-allocation of lumpsome payment
             */
            if ($invoice_payment->payment_type == 'advance_payment') {
                // credit customer Advance DEP
                $account = Account::where('system', 'adv_dep')->first(['id']);
                $cr_data['account_id'] = $account->id;
                Transaction::create($cr_data);
                
                // debit bank
                unset($cr_data['credit'], $cr_data['is_primary']);
                $dr_data = array_replace($cr_data, [
                    'account_id' => $invoice_payment->account_id,
                    'debit' => $invoice_payment->amount,
                ]);    
                Transaction::create($dr_data);
            } else {
                // credit Receivables (Debtors)
                Transaction::create($cr_data);
                            
                // debit bank
                unset($cr_data['credit'], $cr_data['is_primary']);
                $dr_data = array_replace($cr_data, [
                    'account_id' => $invoice_payment->account_id,
                    'debit' => $invoice_payment->amount,
                ]);    
                Transaction::create($dr_data);
            }
        }
        aggregate_account_transactions();        
    }
}
