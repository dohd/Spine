<?php

namespace App\Repositories;

use App\Models\account\Account;
use App\Models\billpayment\Billpayment;
use App\Models\creditnote\CreditNote;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\items\BillpaymentItem;
use App\Models\items\InvoicePaymentItem;
use App\Models\items\JournalItem;
use App\Models\manualjournal\Journal;
use App\Models\supplier\Supplier;
use App\Models\utility_bill\UtilityBill;
use App\Models\withholding\Withholding;

trait CustomerSupplierBalance
{
    /**
     * Customer Opening Balance 
     * @param mixed $customer
     * @param string $method
     */
    public function customer_opening_balance($customer, $method)
    {
        $tr_data = [];
        $open_balance = $customer->open_balance;
        $open_balance_date = $customer->open_balance_date;
        if ($method == 'create') {
            // create journal
            $journal = Journal::create([
                'tid' => Journal::max('tid') + 1,
                'date' => $open_balance_date,
                'note' => $customer->open_balance_note,
                'debit_ttl' => $open_balance,
                'credit_ttl' => $open_balance,
                'ins' => $customer->ins,
                'user_id' => $customer->user_id,
                'customer_id' => $customer->id,
            ]);
            $debtor_account = Account::where('system', 'receivable')->first(['id']);
            foreach ([1,2] as $v) {
                $data = ['journal_id' => $journal->id,'account_id' => $debtor_account->id];
                if ($v == 1) {
                    $data['debit'] = $open_balance;
                } else {
                    $balance_account = Account::where('system', 'retained_earning')->first(['id']);
                    $data['account_id'] = $balance_account->id;
                    $data['credit'] = $open_balance;
                }   
                JournalItem::create($data);
            }
            Invoice::create([
                'invoicedate' => $open_balance_date,
                'invoiceduedate' => $open_balance_date,
                'subtotal' => $open_balance,
                'total' => $open_balance,
                'notes' => $customer->open_balance_note,
                'customer_id' => $customer->id,
                'user_id' => $customer->user_id,
                'ins' => $customer->ins,
                'man_journal_id' => $journal->id
            ]);
            $tr_data = array_replace($journal->toArray(), ['open_balance' => $open_balance,'account_id' => $debtor_account->id]);
        } else {
            // update journal
            $journal = Journal::where('customer_id', $customer->id)->first();
            if (!$journal) return $this->customer_opening_balance($customer, 'create');

            $invoice = Invoice::where('man_journal_id', $journal->id)->first();
            if ($invoice) {
                $invoice->update([
                    'notes' => $customer->open_balance_note, 
                    'subtotal' => $open_balance, 
                    'total' => $open_balance,
                ]);   
            }
            // update manual journal
            $journal->update([
                'note' => $customer->open_balance_note,
                'date' => $open_balance_date,
                'debit_ttl' => $open_balance,
                'credit_ttl' => $open_balance,
            ]);
            foreach ($journal->items as $item) {
                if ($item->debit > 0) $item->update(['debit' => $open_balance]);
                elseif ($item->credit > 0) $item->update(['credit' => $open_balance]);
            }
            $debtor_account = Account::where('system', 'receivable')->first(['id']); 
            $tr_data = array_replace($journal->toArray(), ['open_balance' => $open_balance,'account_id' => $debtor_account->id]);
            $journal->transactions()->delete();
        }
        return $tr_data;
    }

    /**
     * Supplier Opening Balance 
     * @param mixed $supplier
     * @param string $method
     */
    public function supplier_opening_balance($supplier, $method)
    {
        $tr_data = [];
        $open_balance = $supplier->open_balance;
        $open_balance_date = $supplier->open_balance_date;
        if ($method == 'create') {
            // create journal
            $journal = Journal::create([
                'tid' => Journal::max('tid') + 1,
                'date' => $open_balance_date,
                'note' => $supplier->open_balance_note,
                'debit_ttl' => $open_balance,
                'credit_ttl' => $open_balance,
                'ins' => $supplier->ins,
                'user_id' => $supplier->user_id,
                'supplier_id' => $supplier->id,
            ]);
            $creditor_account = Account::where('system', 'payable')->first(['id']);
            foreach ([1,2] as $v) {
                $data = ['journal_id' => $journal->id,'account_id' => $creditor_account->id];
                if ($v == 1) {
                    $data['credit'] = $open_balance;
                } else {
                    $balance_account = Account::where('system', 'retained_earning')->first(['id']);
                    $data['account_id'] = $balance_account->id;
                    $data['debit'] = $open_balance;
                }   
                JournalItem::create($data);
            }
            UtilityBill::create([
                'supplier_id' => $supplier->id,
                'document_type' => 'opening_balance',
                'date' => $open_balance_date,
                'due_date' => $open_balance_date,
                'subtotal' => $open_balance,
                'total' => $open_balance,
                'note' => $supplier->open_balance_note,
                'user_id' => $supplier->user_id,
                'ins' => $supplier->ins,   
                'man_journal_id' => $journal->id             
            ]);
            $tr_data = array_replace($journal->toArray(), ['open_balance' => $open_balance,'account_id' => $creditor_account->id]);
        } else {
            // update journal
            $journal = Journal::where('supplier_id', $supplier->id)->first();
            if (!$journal) return $this->supplier_opening_balance($supplier, 'create');
            
            $bill = UtilityBill::where('man_journal_id', $journal->id)->first();
            if ($bill) {
                $bill->update([
                    'date' => $open_balance_date,
                    'due_date' => $open_balance_date,
                    'subtotal' => $open_balance,
                    'total' => $open_balance,
                    'note' => $supplier->open_balance_note,
                ]);   
                if ($bill->item) {
                    $bill->item->update([
                        'subtotal' => $open_balance,
                        'total' => $open_balance,
                        'note' => $supplier->open_balance_note,
                    ]);
                } 
            }
            // update manual journal
            $journal->update([
                'note' => $supplier->open_balance_note,
                'date' => $open_balance_date,
                'debit_ttl' => $open_balance,
                'credit_ttl' => $open_balance,
            ]);
            foreach ($journal->items as $item) {
                if ($item->debit > 0) $item->update(['debit' => $open_balance]);
                elseif ($item->credit > 0) $item->update(['credit' => $open_balance]);
            }
            $creditor_account = Account::where('system', 'payable')->first(['id']); 
            $tr_data = array_replace($journal->toArray(), ['open_balance' => $open_balance,'account_id' => $creditor_account->id]);
            $journal->transacions()->delete();
        }
        return $tr_data;
    }

    /**
     * Customer Credit Balance 
     * @param int $customer_id
     */
    public function customer_credit_balance(int $customer_id)
    {   
        $customer = Customer::find($customer_id);
        // deposits
        $dep_amount = InvoicePayment::where('customer_id', $customer_id)
        ->whereIn('payment_type', ['on_account', 'advance_payment'])
        ->sum('amount');
        $dep_allocated_amount = InvoicePayment::where('customer_id', $customer_id)
        ->where('rel_payment_id', '>', 0)
        ->where('payment_type', 'per_invoice')
        ->sum('allocate_ttl');
        // withholdings
        $wht_amount = Withholding::whereNull('rel_payment_id')->sum('amount');
        $wht_allocated_amount = Withholding::where('rel_payment_id', '>', 0)->sum('allocate_ttl');

        $total_amount = $dep_amount+$wht_amount-$dep_allocated_amount-$wht_allocated_amount;
        $customer->update(['on_account' => $total_amount]);
    }

    /**
     * Customer Deposit Balance 
     * @param array $invoice_ids
     */
    public function customer_deposit_balance(array $invoice_ids)
    {
        $invoices = Invoice::whereIn('id', $invoice_ids)->get();
        foreach ($invoices as $key => $invoice) {
            $dep_total = InvoicePaymentItem::whereHas('paid_invoice')->where('invoice_id', $invoice->id)->sum('paid');
            $cnote_total = CreditNote::where('is_debit', 0)->where('invoice_id', $invoice->id)->sum('total');
            $dnote_total = CreditNote::where('is_debit', 1)->where('invoice_id', $invoice->id)->sum('total');

            $total_amount = $dep_total+$dnote_total-$cnote_total;
            $invoice->update(['amountpaid' => $total_amount]);
            if ($invoice->amountpaid == 0) $invoice->update(['status' => 'due']);
            elseif (round($invoice->total) > round($invoice->amountpaid)) $invoice->update(['status' => 'partial']);
            else $invoice->update(['status' => 'paid']);
        }
    }

    /**
     * Supplier Credit Balance 
     * @param int $supplier_id
     */
    public function supplier_credit_balance(int $supplier_id)
    {
        $supplier = Supplier::find($supplier_id);
        // deposits
        $payment_amount = Billpayment::where('supplier_id', $supplier_id)
        ->whereIn('payment_type', ['on_account', 'advance_payment'])
        ->sum('amount');
        $pmt_allocated_amount = Billpayment::where('supplier_id', $supplier_id)
        ->where('rel_payment_id', '>', 0)
        ->where('payment_type', 'per_invoice')
        ->sum('allocate_ttl');

        $total_amount = $payment_amount-$pmt_allocated_amount;
        $supplier->update(['on_account' => $total_amount]);
    }

    /**
     * Supplier Payment Balance 
     * @param int $bill_ids
     */
    public function supplier_payment_balance(array $bill_ids)
    {
        $bills = UtilityBill::whereIn('id', $bill_ids)->get();
        foreach ($bills as $key => $bill) {
            $payment_total = BillpaymentItem::whereHas('bill_payment')->where('bill_id', $bill->id)->sum('paid');
            $bill->update(['amount_paid' => $payment_total]);
            if ($bill->amountpaid == 0) $bill->update(['status' => 'due']);
            elseif (round($bill->total) > round($bill->amountpaid)) $bill->update(['status' => 'partial']);
            else $bill->update(['status' => 'paid']);

            // update direct purchase expense
            $purchase = $bill->purchase;
            if ($bill->document_type == 'direct_purchase' && $purchase) {
                $purchase->update(['amountpaid' => $bill->amount_paid]);
                if ($bill->amountpaid == 0) $purchase->update(['status' => 'pending']);
                elseif (round($bill->total) > round($bill->amount_paid)) $purchase->update(['status' => 'partial']);
                else $purchase->update(['status' => 'paid']);
            }
        }
    }
}