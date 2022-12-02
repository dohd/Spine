<?php

namespace App\Repositories\Focus\invoice_payment;

use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\invoice\PaidInvoice;
use App\Models\items\PaidInvoiceItem;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Validation\ValidationException;
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;

class InvoicePaymentRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = PaidInvoice::class;

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
     * @return PaidInvoice $payment
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

        $result = PaidInvoice::where('ins', auth()->user()->ins)->where('tid', $data['tid'])->count();
        if ($result) throw ValidationException::withMessages(['Similar payment already received!']);

        $is_payment = empty($data['payment_id']);
        if ($is_payment) {
            if (isset($data['payment_id'])) unset($data['payment_id']);
            $result = PaidInvoice::create($data);

            if (in_array($result->payment_type, ['per_invoice', 'on_account'])) {
                $unallocated = $result->amount - $result->allocate_ttl;
                $result->customer->increment('on_account', $unallocated);  
            }
        } else {
            $result = PaidInvoice::find($data['payment_id']);            
            $result->increment('allocate_ttl', $data['allocate_ttl']);

            // reduce unallocated, else post Advance Payment Account
            if (in_array($result->payment_type, ['per_invoice', 'on_account'])) {
                $allocated = $result->amount - $result->allocate_ttl;
                $result->customer->decrement('on_account', $allocated);    
            } else {
                $result->allocate_ttl = $data['allocate_ttl'];
                $this->post_transaction($result);
            }
        }

        // allocate items
        $data_items = $input['data_items'];
        if ($data_items) {
            $data_items = array_map(function ($v) use($result) {
                return array_replace($v, [
                    'paidinvoice_id' => $result->id,
                    'paid' => numberClean($v['paid'])
                ]);
            }, $data_items);
            PaidInvoiceItem::insert($data_items);

            // increment invoice amount paid and update status
            foreach ($result->items as $item) {
                $invoice = $item->invoice;
                $invoice->increment('amountpaid', $item->paid);
                if ($invoice->amountpaid == 0) $invoice->update(['status' => 'due']);
                elseif (round($invoice->total) > round($invoice->amountpaid)) $invoice->update(['status' => 'partial']);
                else $invoice->update(['status' => 'paid']);
            }
        }
        
        /**accounting */
        if ($is_payment) $this->post_transaction($result);
        
        if ($result) {
            DB::commit();
            return $result;
        }

        throw new GeneralException('Error Creating Invoice');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param PaidInvoice $payment
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(PaidInvoice $payment, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        foreach ($data as $key => $val) {
            if ($key == 'date') $data[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'allocate_ttl'])) 
                $data[$key] = numberClean($val);
        }
        // reverse customer unallocated amount
        $unallocated = $payment->amount - $payment->allocate_ttl;
        $payment->customer->decrement('on_account', $unallocated);

        $result = $payment->update($data);
        
        // update customer unallocated amount
        $unallocated = $payment->amount - $payment->allocate_ttl;        
        $payment->customer->increment('on_account', $unallocated);

        // allocated items
        $data_items = $input['data_items'];
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $payment_items = PaidInvoiceItem::whereIn('id', $item_ids)->get();
        // reverse invoice amount paid
        foreach ($payment_items as $item) {
            if ($item->invoice) $item->invoice->decrement('amountpaid', $item->paid);
        }
        // update payment items
        $data_items = array_map(function ($v) {
            return array_replace($v, [
                'paid' => numberClean($v['paid'])
            ]);
        }, $data_items);
        Batch::update(new PaidInvoiceItem, $data_items, 'id');

        foreach ($payment->items as $item) {
            // update invoice amount paid
            if ($item->invoice) {
                $invoice = $item->invoice;
                $invoice->increment('amountpaid', $item->paid);
                if ($invoice->amountpaid == 0) $invoice->update(['status' => 'due']);
                elseif (round($invoice->total) > round($invoice->amountpaid)) $invoice->update(['status' => 'partial']);
                else $invoice->update(['status' => 'paid']);
            }
            // delete items with zero payment
            if ($item->paid == 0) $item->delete();
        }

        /** accounting */
        $payment->transactions()->delete();
        $this->post_transaction($payment);

        if ($result) {
            DB::commit();
            return true;
        }

        throw new GeneralException('Error Creating Invoice');
    }

    /**
     * For deleting the respective model from storage
     *
     * @param PaidInvoice $payment
     * @throws GeneralException
     * @return bool
     */
    public function delete(PaidInvoice $payment)
    {
        DB::beginTransaction();

        // reverse customer unallocated amount
        $unallocated = $payment->amount - $payment->allocate_ttl;
        $payment->customer->decrement('on_account', $unallocated);
        // reverse payment
        foreach ($payment->items as $item) {
            if ($item->invoice) {
                $invoice = $item->invoice;
                $invoice->decrement('amountpaid', $item->paid);
                if ($invoice->amountpaid == 0) $invoice->update(['status' => 'due']);
                elseif (round($invoice->total) > round($invoice->amountpaid)) $invoice->update(['status' => 'partial']);
                else $invoice->update(['status' => 'paid']);
            }            
        }
        
        $payment->transactions()->delete();
        aggregate_account_transactions();
        if ($payment->delete()) {
            DB::commit();
            return true;
        }

        throw new GeneralException('Error Creating Invoice');            
    }


    /**
     * Post Invoice Payment Transaction
     */
    public function post_transaction($payment)
    {
        $account = Account::where('system', 'receivable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'pmt')->first(['id', 'code']);
        $tid = Transaction::where('ins', $payment->ins)->max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $payment->amount,
            'tr_date' => $payment->date,
            'due_date' => $payment->date,
            'user_id' => $payment->user_id,
            'note' => $payment->payment_mode . ' - ' . $payment->reference,
            'ins' => $payment->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $payment->id,
            'user_type' => 'customer',
            'is_primary' => 1,
        ];

        if (in_array($payment->payment_type, ['per_invoice', 'on_account'])) {
            // credit Accounts Receivable (Debtors)
            Transaction::create($cr_data);
            
            // debit Bank Account
            unset($cr_data['credit'], $cr_data['is_primary']);
            $dr_data = array_replace($cr_data, [
                'account_id' => $payment->account_id,
                'debit' => $payment->amount
            ]);
            Transaction::create($dr_data);
        } else {
            $adv_account = Account::where('system', 'adv_pmt')->first(['id']);
            if ($payment->allocate_ttl == 0)  {
                // credit Advance Payment Account
                $cr_data = array_replace($cr_data, [
                    'account_id' => $adv_account->id,
                ]);
                Transaction::create($cr_data);

                // debit Bank Account
                unset($cr_data['credit'], $cr_data['is_primary']);
                $dr_data = array_replace($cr_data, [
                    'account_id' => $payment->account_id,
                    'debit' => $payment->amount
                ]);
                Transaction::create($dr_data);
            } else {
                // credit Accounts Receivable (Debtors)
                $cr_data['credit'] = $payment->allocate_ttl;
                Transaction::create($cr_data);

                // debit Advance Payment Account
                unset($cr_data['credit'], $cr_data['is_primary']);
                $cr_data = array_replace($cr_data, [
                    'account_id' => $adv_account->id,
                    'debit' => $payment->allocate_ttl
                ]);
                Transaction::create($cr_data);
            }
        }
        aggregate_account_transactions();        
    }
}
