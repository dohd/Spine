<?php

namespace App\Repositories\Focus\invoice_payment;

use App\Exceptions\GeneralException;
use App\Models\account\Account;
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
        if (empty($data['rel_payment_id'])) {
            if (@$data['reference'] && @$data['account_id']) {
                $ref_exists = InvoicePayment::where('account_id', $data['account_id'])
                    ->where('reference', 'LIKE', "%{$data['reference']}%")  
                    ->whereNull('rel_payment_id')->exists();            
                if ($ref_exists) throw ValidationException::withMessages(['Duplicate reference no.']);
            }
        }

        // create payment
        $tid = InvoicePayment::max('tid');
        if ($data['tid'] <= $tid) $data['tid'] = $tid+1;
        $result = InvoicePayment::create($data);

        // payment line items
        $data_items = $input['data_items'];
        $data_items = array_filter($data_items, fn($v) => $v['paid'] > 0);
        if (!$data_items && $result->payment_type == 'per_invoice') 
            throw ValidationException::withMessages(['amount allocation on line items required!']);
        
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
            // payment
            if (!$result->rel_payment_id) {
                if (in_array($result->payment_type, ['on_account', 'advance_payment'])) {
                    $result->customer->increment('on_account', $result->amount);
                }
            }

            // allocate payment
            if ($result->payment_type == 'per_invoice' && $result->rel_payment_id) {
                $result->customer->decrement('on_account', $result->allocate_ttl);
                $rel_payment = InvoicePayment::find($result->rel_payment_id);
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
        if (empty($data['rel_payment_id'])) {
            if (@$data['reference'] && @$data['account_id']) {
                $ref_exists = InvoicePayment::where('id', '!=', $invoice_payment->id)
                    ->where('account_id', $data['account_id'])
                    ->where('reference', 'LIKE', "%{$data['reference']}%")  
                    ->whereNull('rel_payment_id')->exists();            
                if ($ref_exists) throw ValidationException::withMessages(['Duplicate reference no.']);
            }
        }
            
        // delete invoice_payment with no unallocated line items
        $data_items = $input['data_items'];
        if (!$data_items && $invoice_payment->payment_type == 'per_invoice') 
            return $this->delete($invoice_payment);

        DB::beginTransaction(); 

        $prev_reference = $invoice_payment->reference;
        $prev_note = $invoice_payment->note;

        // reverse customer on_account balance
        if ($invoice_payment->customer) {
            // payment
            if (!$invoice_payment->rel_payment_id) {
                if (in_array($invoice_payment->payment_type, ['on_account', 'advance_payment'])) {
                    $invoice_payment->customer->decrement('on_account', $invoice_payment->amount);
                } 
            }

            // allocate payment
            if ($invoice_payment->payment_type == 'per_invoice' && $invoice_payment->rel_payment_id) {
                $invoice_payment->customer->increment('on_account', $invoice_payment->allocate_ttl);
                $rel_payment = InvoicePayment::find($invoice_payment->rel_payment_id);
                if ($rel_payment) {
                    $rel_payment->decrement('allocate_ttl', $invoice_payment->allocate_ttl);
                    if ($rel_payment->payment_type == 'advance_payment') $invoice_payment->is_advance_allocation = true;
                }
            }
        }

        // update payment
        $result = $invoice_payment->update($data);

        // update customer on_account balance
        if ($invoice_payment->customer) {
            // payment
            if (!$invoice_payment->rel_payment_id) {
                if (in_array($invoice_payment->payment_type, ['on_account', 'advance_payment'])) {
                    $invoice_payment->customer->increment('on_account', $invoice_payment->amount);
                } 
            }

            // allocated payment
            if ($invoice_payment->payment_type == 'per_invoice' && $invoice_payment->rel_payment_id) {
                $invoice_payment->customer->decrement('on_account', $invoice_payment->allocate_ttl);
                $rel_payment = InvoicePayment::find($invoice_payment->rel_payment_id);
                if ($rel_payment) {
                    $rel_payment->increment('allocate_ttl', $invoice_payment->allocate_ttl);
                    if ($rel_payment->payment_type == 'advance_payment') $invoice_payment->is_advance_allocation = true;
                    // check over allocation
                    $diff = round($rel_payment->amount - $rel_payment->allocate_ttl);
                    if ($diff < 0) throw ValidationException::withMessages(['Allocation limit reached! Please reduce allocated amount by ' . numberFormat($diff*-1)]);
                }
            }
        }
        
        // update payment items and invoices
        foreach ($invoice_payment->items as $pmt_item) {
            $invoice = $pmt_item->invoice;
            if ($invoice) $invoice->decrement('amountpaid', $pmt_item->paid);
                
            $is_allocated = 0;
            foreach ($data_items as $data_item) {
                if ($data_item['id'] == $pmt_item->id) {
                    $is_allocated = 1;
                    $data_item['paid'] = numberClean($data_item['paid']);
                    $pmt_item->update(['paid' => $data_item['paid']]);
                    // update invoice status
                    if ($invoice) {
                        $invoice->increment('amountpaid', $data_item['paid']);
                        if ($invoice->amountpaid == 0) $invoice->update(['status' => 'due']);
                        elseif (round($invoice->total) > round($invoice->amountpaid)) $invoice->update(['status' => 'partial']);
                        else $invoice->update(['status' => 'paid']);
                    }
                }
            }
            if (!$is_allocated) $pmt_item->delete();
        }

        // check if payment is advance_payment allocation
        if ($invoice_payment->rel_payment_id) {
            $rel_payment = InvoicePayment::find($invoice_payment->rel_payment_id);
            if ($rel_payment && $rel_payment->payment_type == 'advance_payment')
                $invoice_payment->is_advance_allocation = true;
        }
        
        /** accounting */
        if (!$invoice_payment->rel_payment_id || $invoice_payment->is_advance_allocation) {
            Transaction::whereIn('tr_type', ['pmt', 'adv_pmt'])
            ->where(['tr_ref' => $invoice_payment->id, 'user_type' => 'customer'])
            ->where(function($q) use($prev_reference, $prev_note) {
                $q->where('note', 'LIKE', "%{$prev_reference}%")
                ->orWhere('note', 'LIKE', "%{$prev_note}%");
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
        // dd($invoice_payment->id);
        DB::beginTransaction();

        // check if contain related allocations
        $has_allocations = InvoicePayment::where('rel_payment_id', $invoice_payment->id)->exists();
        if ($has_allocations) throw ValidationException::withMessages([
            'Delete related payment allocations to proceed'
        ]);

        // reverse customer on_account balance
        if ($invoice_payment->customer_id) {
            if (!$invoice_payment->rel_payment_id) {
                if (in_array($invoice_payment->payment_type, ['on_account', 'advance_payment'])) {
                    $invoice_payment->customer->decrement('on_account', $invoice_payment->amount);
                }
            } else {
                $invoice_payment->customer->increment('on_account', $invoice_payment->allocate_ttl);
                $payment = InvoicePayment::find($invoice_payment->rel_payment_id);
                if ($payment) $payment->decrement('allocate_ttl', $invoice_payment->allocate_ttl);
            }
        }

        // reverse invoice amountpaid and status
        foreach ($invoice_payment->items as $item) {
            $invoice = $item->invoice;
            if ($invoice) {
                $invoice->decrement('amountpaid', $item->paid);
                if ($invoice->amountpaid == 0) $invoice->update(['status' => 'due']);
                elseif (round($invoice->total) > round($invoice->amountpaid)) $invoice->update(['status' => 'partial']);
                else $invoice->update(['status' => 'paid']);
            }
        }
        
        Transaction::whereIn('tr_type', ['pmt', 'adv_pmt'])
        ->where(['tr_ref' => $invoice_payment->id, 'user_type' => 'customer'])
        ->where(function($q) use($invoice_payment) {
            $q->where('note', 'LIKE', "%{$invoice_payment->reference}%")
            ->orWhere('note', 'LIKE', "%{$invoice_payment->note}%");
        })
        ->delete();
        aggregate_account_transactions();

        if ($invoice_payment->delete()) {
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
        $tr_category = Transactioncategory::where('code', 'pmt')->first(['id', 'code']);
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
        ];

        if ($invoice_payment->is_advance_allocation) {
            // credit Receivables (Debtors)
            Transaction::create($cr_data);
            
            // debit customer Advance PMT
            unset($cr_data['credit'], $cr_data['is_primary']);
            $account = Account::where('system', 'adv_pmt')->first(['id']);
            $dr_data = array_replace($cr_data, [
                'account_id' => $account->id,
                'debit' => $invoice_payment->amount,
            ]);    
            Transaction::create($dr_data);
        } else {
            if ($invoice_payment->payment_type == 'advance_payment') {
                // credit customer Advance PMT
                $account = Account::where('system', 'adv_pmt')->first(['id']);
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
