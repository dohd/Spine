<?php

namespace App\Repositories;

use App\Models\creditnote\CreditNote;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\items\InvoicePaymentItem;
use App\Models\withholding\Withholding;

trait CustomerSupplierBalance
{
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

    public function supplier_credit_balance(int $customer_id)
    {
        // 
    }

    public function supplier_payment_balance(array $invoice_ids)
    {
        // 
    }
}