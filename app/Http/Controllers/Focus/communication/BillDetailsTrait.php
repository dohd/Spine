<?php

namespace App\Http\Controllers\Focus\communication;

use App\Models\bank\Bank;
use App\Models\bill\Bill;
use App\Models\Company\Company;
use App\Models\Company\ConfigMeta;
use App\Models\Company\UserGateway;
use App\Models\order\Order;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\quote\Quote;
use App\Models\djc\Djc;
use App\Models\invoice\Invoice;
use App\Models\items\VerifiedItem;
use App\Models\rjc\Rjc;

trait BillDetailsTrait
{
    protected function bill_details($request)
    {
        $flag = false;

        $getAttr = function ($type, $title, $custom, $person, $person_id, $url) { 
            return compact('type', 'title', 'person', 'person_id', 'url'); 
        };
        $getGeneral = function ($bill_type, $lang_bill_number, $lang_bill_date, $lang_bill_due_date, $person, $direction, $prefix, $status_block) {
            return compact('bill_type', 'lang_bill_number', 'lang_bill_date', 'lang_bill_due_date', 'person', 'direction', 'prefix', 'status_block');
        };

        switch ($request->type) {
            case 1:
                //invoice
                $invoice = Invoice::find($request->id);
                $attributes = $getAttr(1, 'invoice', 2, 1, $invoice->customer_id, route('biller.invoices.show', $invoice->id));
                foreach($attributes as $key => $val) {
                    $invoice[$key] = $val;
                }
                // assign bank
                $invoice['bank'] = Bank::find($invoice->bank_id);

                $prefix = 1;
                $title = trans('invoices.invoice_title');
                if ($invoice->i_class == 1) {
                    $prefix = 10;
                    $title = trans('invoices.pos');
                } elseif ($invoice->i_class > 1) {
                    $prefix = 6;
                    $title = trans('invoices.subscription');
                }

                $flag = token_validator($request->token, 'i' . $invoice->id . $invoice->tid);
                $general = $getGeneral(
                    $title, 
                    trans('invoices.tid'), 
                    trans('invoices.invoice_date'), 
                    trans('invoices.invoice_due_date'),
                    trans('customers.customer'),
                    'ltr', $prefix, true
                );
                $valid_token = token_validator('', 'i' . $invoice->id . $invoice->tid, true);
                break;
            case 3:
                //invoice proforma
                $invoice = Bill::find($request->id);
                $attributes = $getAttr(3, 'proformer', 2, 1, $invoice->customer_id, route('biller.invoices.show', $invoice->id));
                foreach($attributes as $key => $val) {
                    $invoice[$key] = $val;
                }

                $flag = token_validator($request->token, 'i' . $invoice->id . $invoice->tid,);
                $general = $getGeneral(
                    trans('invoices.proforma'),
                    trans('invoices.proforma_tid'),
                    trans('invoices.invoice_date'),
                    trans('invoices.invoice_due_date'), 
                    trans('customers.customer'),
                    'ltr', 3, false
                );
                $valid_token = token_validator('', 'i' . $invoice->id . $invoice->tid, true);
                break;
            case 4:
                // quote
                $invoice = Quote::find($request->id);
                $attributes = $getAttr(1, 'quote', 2, 1, $invoice->customer_id, route('biller.quotes.show', $invoice->id));
                foreach($attributes as $key => $val) {
                    $invoice[$key] = $val;
                }
                // assign bank
                $invoice['bank'] = Bank::find($invoice->bank_id);


                $invoice['products'] = $invoice->products()->orderBy('row_index')->get();
                if ($invoice->verified == 'Yes') {
                    $invoice['verified_items'] = VerifiedItem::where('quote_id', $invoice->id)->orderBy('row_index')->get();
                }

                $flag = token_validator($request->token, 'q' . $invoice->id . $invoice->tid);
                $general = $getGeneral(
                    trans('quotes.quote'),
                    trans('quotes.quote'),
                    trans('quotes.invoicedate'),
                    trans('quotes.invoiceduedate'),
                    trans('customers.customer'),
                    'ltr', 5, false
                );
                $valid_token = token_validator('', 'q' . $invoice->id . $invoice->tid, true);
                break;
            case 5:
                // order
                $invoice = Order::find($request->id);
                $attributes = $getAttr(5, 'order', 5, 1, $invoice->customer_id, route('biller.orders.show', $invoice->id));
                foreach($attributes as $key => $val) {
                    $invoice[$key] = $val;
                }

                $title = trans('orders.credit_note');
                $prefix = 7;
                $person = trans('customers.customer');
                $flag = token_validator($request->token, 'o' . $invoice->id . $invoice->tid);
                if ($invoice->i_class == 3) {
                    $prefix = 8;
                    $title = trans('orders.stock_return');
                    $person = trans('suppliers.supplier');
                }
                $general = $getGeneral(
                    $title, 
                    trans('orders.order'),
                    trans('general.date'),
                    trans('orders.invoiceduedate'),
                    $person, 'ltr', $prefix, false
                );
                $valid_token = token_validator('', 'o' . $invoice->id . $invoice->tid, true);
                break;
            case 9:
                // purchase order
                $invoice = Purchaseorder::find($request->id);
                $attributes = $getAttr(9, 'purchase_order', 9, 1, $invoice->customer_id, route('biller.purchaseorders.show', $invoice->id));
                foreach($attributes as $key => $val) {
                    $invoice[$key] = $val;
                }

                $flag = token_validator($request->token, 'po' . $invoice->id . $invoice->tid);
                $general = $getGeneral(
                    trans('purchaseorders.purchaseorder'),
                    trans('purchaseorders.purchaseorder'),
                    trans('purchaseorders.invoicedate'),
                    trans('purchaseorders.invoiceduedate'),                    
                    trans('suppliers.supplier'),
                    'ltr', 9, false
                );
                $valid_token = token_validator('', 'po' . $invoice->id . $invoice->tid, true);
                break;
            case 10:
                // djc
                $invoice = Djc::find($request->id);
                $attributes = $getAttr(9, 'djc_report', 9, 1, $invoice->customer_id, route('biller.purchaseorders.show', $invoice->id));
                foreach($attributes as $key => $val) {
                    $invoice[$key] = $val;
                }

                $flag = token_validator($request->token, 'd' . $invoice->id);
                $general = $getGeneral(
                    trans('purchaseorders.purchaseorder'),
                    trans('purchaseorders.purchaseorder'),
                    trans('purchaseorders.invoicedate'),
                    trans('purchaseorders.invoiceduedate'), 
                    trans('suppliers.supplier'),
                    'ltr', 9, false
                );
                $valid_token = token_validator('', 'd' . $invoice->id, true);
                break;
            case 11:
                //rjc
                $invoice = Rjc::find($request->id);
                $attributes = $getAttr(9, 'rjc_report', 9, 1, $invoice->customer_id, route('biller.purchaseorders.show', $invoice->id));
                foreach($attributes as $key => $val) {
                    $invoice[$key] = $val;
                }
                // main project quote
                $invoice['quote'] = $invoice->project->quotes->find($invoice->project->main_quote_id);
                // All djcs sharing lead of the main project quote
                $invoice['djcs'] = Djc::where('lead_id', $invoice->quote->lead->id)->get(['tid', 'report_date']);

                $flag = token_validator($request->token, 'd' . $invoice->id);
                $general = $getGeneral(
                    trans('purchaseorders.purchaseorder'),
                    trans('purchaseorders.purchaseorder'),
                    trans('purchaseorders.invoicedate'),
                    trans('purchaseorders.invoiceduedate'), 
                    trans('suppliers.supplier'),
                    'ltr', 9, false
                );
                $valid_token = token_validator('', 'd' . $invoice->id, true);
                break;

        }

        if ($flag) {
            $company = Company::find($invoice->ins);

            $online_payment = ConfigMeta::where(['feature_id' => 5, 'ins' => $company->id])->first();
            $online_pay_account = ConfigMeta::where(['feature_id' => 6, 'ins' => $company->id])->first('feature_value');

            $gateway = UserGateway::whereHas('config', function ($q) use ($company) { $q->where('ins', $company->id); })->get();

            config([ 'currency' => ConfigMeta::where(['feature_id' => 2, 'ins' => $company->id])->first()->currency ]);

            $general['tax_string_total'] = trans('general.total_tax');
            $general['tax_id'] = trans('general.tax_id');
            if ($invoice->tax_format == 'igst' or $invoice->tax_format == 'cgst') {
                $general['tax_string_total'] = trans('general.total_gst');
                $general['tax_id'] = trans('general.gstin');
            }

            $link = array(
                'link' => route('biller.print_bill', [$invoice->id, $invoice->type, $valid_token, 1]),
                'download' => route('biller.print_bill', [$invoice->id, $invoice->type, $valid_token, 2]),
                'preview' => route('biller.view_bill', [$invoice->id, $invoice->type, $valid_token, 0]),
                'bank' => route('biller.view_bank', [$invoice->id, $invoice->type, $valid_token]),
                'payment' => route('biller.pay_card', [$invoice->id, $invoice->type, $valid_token])
            );

            $data = compact('general', 'invoice', 'company', 'gateway', 'link');
            $data['online_payment'] = $online_payment->feature_value;
            $data['online_pay_account'] = $online_pay_account->feature_value;

            return $data;
        }

        exit('Bill details error');
    }    
}