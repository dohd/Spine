<?php

namespace App\Repositories\Focus\invoice;

use App\Models\account\Account;
use App\Models\items\CustomEntry;
use App\Models\items\InvoiceItem;
use App\Models\invoice\Invoice;
use App\Exceptions\GeneralException;
use App\Models\invoice\PaidInvoice;
use App\Models\items\PaidInvoiceItem;
use App\Models\product\ProductVariation;
use App\Models\transaction\Transaction;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\quote\Quote;
use App\Models\transactioncategory\Transactioncategory;
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;

/**
 * Class InvoiceRepository.
 */
class InvoiceRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Invoice::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('i_rel_type') == 1, function ($q) {
            return $q->where('customer_id', request('i_rel_id', 0));
        });

        if (request('project_id')) {
            $q->whereHas('project', function ($sq) {
                return $sq->where('project_id', request('project_id', 0));
            });
        }
        
        if (request('start_date') && request('end_date')) {
            $q->whereBetween('invoicedate', [
                date_for_database(request('start_date')), 
                date_for_database(request('end_date'))
            ]);
        }

        return $q->get(['id', 'tid', 'customer_id', 'invoicedate', 'invoiceduedate', 'total', 'status', 'notes']);
    }

    /**
     * Payments DataTable
     */
    public function getPaymentsForDataTable()
    {
        return PaidInvoice::all();
    }


    /**
     * Create project invoice
     */
    public function create_project_invoice(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $bill = $input['bill'];
        $duedate = $bill['invoicedate'] . ' + ' . $bill['validity'] . ' days';
        $bill['invoiceduedate'] = date_for_database($duedate);
        foreach ($bill as $key => $val) {
            if ($key == 'invoicedate') $bill[$key] = date_for_database($val);
            if (in_array($key, ['total', 'subtotal', 'tax'], 1)) 
                $bill[$key] = numberClean($val);
        }
        // increament tid
        $last_inv = Invoice::orderBy('id', 'DESC')->first('tid');
        if ($last_inv && $bill['tid'] <= $last_inv->tid) {
            $bill['tid'] = $last_inv->tid + 1;
        }
        $result = Invoice::create($bill);
        
        $bill_items = $input['bill_items'];
        foreach ($bill_items as $k => $item) {
            $bill_items[$k] = array_replace($item, [
                'invoice_id' => $result->id,
                'product_price' => numberClean($item['product_price']),
            ]);
        }
        InvoiceItem::insert($bill_items);

        // update Quote or PI invoice status
        Quote::whereIn('id', function($q) use ($result) {
            $q->select('quote_id')->from('invoice_items')->where('invoice_id', $result->id);
        })->update(['invoiced' => 'Yes']);
        
        /** accounting */
        $this->post_transaction_project_invoice($result);

        DB::commit();
        if ($result) return $result;

        throw new GeneralException('Error Creating Invoice');
    }

    /**
     * Update Project Invoice
     */
    public function update_project_invoice($invoice, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $bill = $input['bill'];
        foreach ($bill as $key => $val) {
            if ($key == 'invoicedate') $bill[$key] = date_for_database($val);
            if (in_array($key, ['total', 'subtotal', 'tax'], 1)) 
                $bill[$key] = numberClean($val);
        }
        $duedate = $bill['invoicedate'] . ' + ' . $bill['validity'] . ' days';
        $bill['invoiceduedate'] = date_for_database($duedate);
        $invoice->update($bill);

        $bill_items = $input['bill_items'];
        $bill_items = array_map(function ($v) { 
            return [
                'id' => $v['id'],
                'reference' => $v['reference'], 
                'description' => $v['description']
            ];
        }, $bill_items);
        Batch::update(new InvoiceItem, $bill_items, 'id');

        /**accounting */
        $invoice->transactions()->delete();
        $this->post_transaction_project_invoice($invoice);

        DB::commit();
        if ($bill) return true;        
    }


    // invoice transacton
    public function post_transaction_project_invoice($result)
    {
        // debit Accounts Receivable (Debtors)
        $account = Account::where('system', 'receivable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'rcpt')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $dr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'ref_ledger_id' => $result->account_id,
            'trans_category_id' => $tr_category->id,
            'debit' => $result->total,
            'tr_date' => date('Y-m-d'),
            'due_date' => $result->invoiceduedate,
            'user_id' => $result->user_id,
            'note' => $result->notes,
            'ins' => $result->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $result->id,
            'user_type' => 'customer',
            'is_primary' => 1,
        ];
        Transaction::create($dr_data);

        // credit Client Income account (intermediary ledger)
        $account = Account::where('system', 'client_income')->first(['id']);
        unset($dr_data['debit'], $dr_data['is_primary']);
        $inc_cr_data = array_replace($dr_data, [
            'account_id' => $account->id,
            'ref_ledger_id' => $result->account_id, // revenue ledger id
            'credit' => $result->subtotal,
        ]);

        // credit tax (VAT)
        $account = Account::where('system', 'tax')->first(['id']);
        $tax_cr_data = array_replace($dr_data, [
            'account_id' => $account->id,
            'credit' => $result->tax,
        ]);
        Transaction::insert([$inc_cr_data, $tax_cr_data]);

        // update account ledgers debit and credit totals
        aggregate_account_transactions();        
    }

    /**
     * Create invoice payment
     */
    public function create_invoice_payment(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $bill = $input['bill'];
        foreach ($bill as $key => $val) {
            if ($key == 'date') $bill[$key] = date_for_database($val);
            if (in_array($key, ['amount_ttl', 'deposit_ttl', 'deposit'], 1)) 
                $bill[$key] = numberClean($val);
        }

        $result = null;
        $is_allocated = $bill['is_allocated'];
        if (!$bill['account_id']) {
            $adv = PaidInvoice::where([
                'reference' => $bill['reference'],
                'payment_mode' => $bill['payment_mode'],
                'is_allocated' => 0
            ])->first();
            $bill['account_id'] = $adv? $adv->account_id : '';
        }
        // payment per invoice (direct or advance)
        if ($is_allocated) {
            $result = PaidInvoice::create($bill);
            $result['note'] = $result->payment_mode . ' - ' . $result->reference;

            $bill_items = $input['bill_items'];
            foreach ($bill_items as $k => $item) {
                $bill_items[$k] = array_replace($item, [
                    'paidinvoice_id' => $result->id,
                    'paid' => numberClean($item['paid'])
                ]);
            }
            PaidInvoiceItem::insert($bill_items);
    
            // update paid amount in invoices
            $invoice_ids = $result->items()->pluck('invoice_id')->toArray();
            $paid_invoices = PaidInvoiceItem::whereIn('invoice_id', $invoice_ids)
                ->select(DB::raw('invoice_id as id, SUM(paid) as amountpaid'))
                ->groupBy('invoice_id')
                ->get()->toArray();
            Batch::update(new Invoice, $paid_invoices, 'id');
    
            // update payment status in invoices
            foreach ($result->items as $item) {            
                $invoice = $item->invoice;
                if ($invoice->total > $invoice->amountpaid) $invoice->update(['status' => 'partial']);
                if ($invoice->total == $invoice->amountpaid) $invoice->update(['status' => 'paid']);
            }
        } else {
            // payment on account
            $bill['deposit_ttl'] = $bill['deposit'];
            unset($bill['amount_ttl']);
            $result = PaidInvoice::create($bill);
        }

        /** accounting */
        $this->post_transaction_invoice_payment($result);

        DB::commit();
        if ($result) return true;

        throw new GeneralException('Error Creating Invoice');
    }

    /**
     * Update invoice payment
     */
    public function update_invoice_payment($id, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $bill = $input['bill'];
        foreach ($bill as $key => $val) {
            if ($key == 'date') $bill[$key] = date_for_database($val);
            if (in_array($key, ['amount_ttl', 'deposit_ttl', 'deposit'], 1)) 
                $bill[$key] = numberClean($val);
        }
        if ($bill['deposit_ttl'] == 0) $bill['deposit_ttl'] = $bill['deposit'];
        unset($bill['amount_ttl']);

        $result = PaidInvoice::find($id);
        $result->update($bill);
        $result['note'] = $result->payment_mode . ' - ' . $result->reference;

        $bill_items = $input['bill_items'];
        if ($bill_items) {
            // delete omitted items
            $item_ids = array_map(function ($v) { return $v['id']; }, $bill_items);
            $result->items()->whereNotIn('id', $item_ids)->delete();
            // update new items
            foreach ($bill_items as $k => $item) {
                $bill_items[$k] = array_replace($item, [
                    'paid' => numberClean($item['paid']),
                ]);
            }
            Batch::update(new PaidInvoiceItem, $bill_items, 'id');
            
            // update paid amount in invoices
            $invoice_ids = $result->items()->pluck('invoice_id')->toArray();
            $paid_invoices = PaidInvoiceItem::whereIn('invoice_id', $invoice_ids)
                ->select(DB::raw('invoice_id as id, SUM(paid) as amountpaid'))
                ->groupBy('invoice_id')
                ->get()->toArray();
            Batch::update(new Invoice, $paid_invoices, 'id');

            // update payment status in invoices
            foreach ($result->items as $item) {            
                $invoice = $item->invoice;
                if ($invoice->amountpaid == 0) $invoice->update(['status' => 'due']);
                elseif ($invoice->total > $invoice->amountpaid) $invoice->update(['status' => 'partial']);
                elseif ($invoice->total == $invoice->amountpaid) $invoice->update(['status' => 'paid']);
            }
        }
        

        /** accounting */
        $result->transactions()->delete();
        $this->post_transaction_invoice_payment($result);

        DB::commit();
        if ($result) return true;

        throw new GeneralException('Error Creating Invoice');
    }    

    /**
     * Delete invoice payment
     */
    public function delete_invoice_payment($id)
    {
        DB::beginTransaction();

        $payment = PaidInvoice::find($id);
        foreach ($payment->items as $item) {
            $invoice = $item->invoice;
            if ($invoice) $item->invoice->decrement('amountpaid', $item->paid);
            // update status
            if ($invoice->amountpaid == 0) $invoice->update(['status' => 'due']);
            elseif ($invoice->total > $invoice->amountpaid) $invoice->update(['status' => 'partial']);
            elseif ($invoice->total == $invoice->amountpaid) $invoice->update(['status' => 'paid']);
        }
        $payment->items()->delete();
        $payment->transactions()->delete();
        aggregate_account_transactions();
        $result = $payment->delete();

        DB::commit();
        if ($result) return $result;

        throw new GeneralException('Error Creating Invoice');
    }

    // payment transaction
    public function post_transaction_invoice_payment($result)
    {
        $tr_category_adv = Transactioncategory::where('code', 'adv_pmt')->first(['id', 'code']);
        $tr_category_pmt = Transactioncategory::where('code', 'pmt')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $data = [
            'tid' => $tid,
            'tr_date' => $result->date,
            'due_date' => date('Y-m-d'),
            'user_id' => $result->user_id,
            'ins' => $result->ins,
            'tr_ref' => $result->id,
            'user_type' => 'customer',
            'note' => $result->note,
            'is_primary' => 0,
        ];

        $tr_data = array();
        // payment per invoice
        if ($result->is_allocated) {
            $account = Account::where('system', 'receivable')->first(['id']);
            // advance payment sourcce
            if ($result->advance_account_id) {
                // debit Advance Payment Account
                $tr_data[] = array_replace($data, [
                    'account_id' => $result->advance_account_id,
                    'debit' => $result->deposit_ttl,
                    'is_primary' => 1,
                    'trans_category_id' => $tr_category_adv->id,
                    'tr_type' => $tr_category_adv->code,
                ]);
                // credit Accounts Receivable (Debtors)
                $tr_data[] = array_replace($data, [
                    'account_id' => $account->id,
                    'credit' => $result->deposit_ttl,
                    'trans_category_id' => $tr_category_pmt->id,
                    'tr_type' => $tr_category_pmt->code,
                ]);
            } 
            // direct payment source
            elseif ($result->account_id) {
                // debit Bank Account
                $tr_data[] = array_replace($data, [
                    'account_id' => $result->account_id,
                    'debit' => $result->deposit_ttl,
                    'is_primary' => 1,
                    'trans_category_id' => $tr_category_pmt->id,
                    'tr_type' => $tr_category_pmt->code,
                ]);
                // credit Accounts Receivable (Debtors)
                $tr_data[] = array_replace($data, [
                    'account_id' => $account->id,
                    'credit' => $result->deposit_ttl,
                    'trans_category_id' => $tr_category_pmt->id,
                    'tr_type' => $tr_category_pmt->code,
                ]);
            } 
        } 
        // payment on account
        else {
            // debit Bank Account
            $tr_data[] = array_replace($data, [
                'account_id' => $result->account_id,
                'debit' => $result->deposit_ttl,
                'is_primary' => 1,
                'trans_category_id' => $tr_category_pmt->id,
                'tr_type' => $tr_category_pmt->code,
            ]);
            // credit Advance payment Account
            $tr_data[] = array_replace($data, [
                'account_id' => $result->advance_account_id,
                'credit' => $result->deposit_ttl,
                'trans_category_id' => $tr_category_adv->id,
                'tr_type' => $tr_category_adv->code,
            ]);
        }
        $tr_data = array_map(function ($v) {
            if (isset($v['debit']) && $v['debit'] > 0) $v['credit'] = 0;
            if (isset($v['credit']) && $v['credit'] > 0) $v['debit'] = 0;
            return $v;
        }, $tr_data);
        Transaction::insert($tr_data);
        
        // update account ledgers debit and credit totals
        aggregate_account_transactions();
    }


    private function update_dual(Model $table, array $values, string $index = null, $index2 = null)
    {
        $final = [];

        if (!count($values)) {
            return false;
        }

        $whr = '';
        foreach ($values as $key => $val) {

            $q = '';
            $i = 0;
            foreach (array_keys($val) as $field) {
                if ($field != $index and $field != $index2) {

                    if ($i < 2) $q .= $field . '=' . $val[$field] . ',';
                    if ($i == 2) $q .= $field . '=' . $val[$field] . ' ';

                    $i++;
                }
            }
            $whr .= "UPDATE `" . $table->getTable() . "` SET $q";
            $whr .= 'WHERE (`' . $index . '` = "' . $val[$index] . '" AND `' . $index2 . '` = "' . $val[$index2] . '"  AND `value2` IS NULL);';
        }

        return DB::statement($whr);
    }


    /**
     * For updating the respective Model in storage
     *
     * @param Invoice $invoice
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Invoice $invoice, array $input)
    {
        $p = @$input['invoice']['p'];

        $id = $input['invoice']['id'];
        $extra_discount = numberClean($input['invoice']['after_disc']);
        $input['invoice']['invoicedate'] = date_for_database($input['invoice']['invoicedate']);
        $input['invoice']['subtotal'] = numberClean($input['invoice']['subtotal']);
        $input['invoice']['shipping'] = numberClean($input['invoice']['shipping']);
        $input['invoice']['discount_rate'] = numberClean($input['invoice']['discount_rate']);
        $input['invoice']['after_disc'] = numberClean($input['invoice']['after_disc']);
        $input['invoice']['total'] = numberClean($input['invoice']['total']);
        $input['invoice']['ship_tax_rate'] = numberClean($input['invoice']['ship_rate']);
        $input['invoice']['ship_tax'] = numberClean($input['invoice']['ship_tax']);
        $input['invoice']['extra_discount'] = $extra_discount;
        $total_discount = $extra_discount;
        $re_stock = @$input['invoice']['restock'];
        unset($input['invoice']['after_disc']);
        unset($input['invoice']['ship_rate']);
        unset($input['invoice']['id']);
        unset($input['invoice']['restock']);
        unset($input['invoice']['sub']);
        unset($input['invoice']['p']);

        DB::beginTransaction();
        $result = Invoice::find($id);
        if ($result->status == 'canceled') return false;
        if ($result->i_class > 1) {
            $input['invoice']['r_time'] = $input['invoice']['recur_after'];
            $input['invoice']['invoiceduedate'] = date("Y-m-d", strtotime($input['invoice']['invoicedate'] . " +" . $input['invoice']['r_time'] . 's'));
            unset($input['invoice']['recur_after']);
        } else {
            $input['invoice']['invoiceduedate'] = date_for_database($input['invoice']['invoiceduedate']);
        }
        $input['invoice'] = array_map('strip_tags', $input['invoice']);
        $result->update($input['invoice']);

        if ($result) {
            InvoiceItem::where('invoice_id', $id)->delete();
            $products = array();
            $subtotal = 0;
            $total_qty = 0;
            $total_tax = 0;
            foreach ($input['invoice_items']['product_id'] as $key => $value) {

                if ($input['invoice_items']['unit_m'][$key] > 1) {
                    $unit_val = $input['invoice_items']['unit_m'][$key];
                    $qty = $unit_val * numberClean($input['invoice_items']['product_qty'][$key]);
                    $old_qty = $unit_val * numberClean(@$input['invoice_items']['old_product_qty'][$key]);
                } else {
                    $unit_val = 1;
                    $qty = numberClean($input['invoice_items']['product_qty'][$key]);
                    $old_qty = numberClean(@$input['invoice_items']['old_product_qty'][$key]);
                }

                $subtotal += numberClean(@$input['invoice_items']['product_price'][$key]) * numberClean(@$input['invoice_items']['product_qty'][$key]);
                //$qty = numberClean($input['invoice_items']['product_qty'][$key]);

                $total_qty += $qty;
                $total_tax += numberClean(@$input['invoice_items']['product_tax'][$key]);
                $total_discount += numberClean(@$input['invoice_items']['total_discount'][$key]);
                $products[] = array(
                    'invoice_id' => $id,
                    'product_id' => $input['invoice_items']['product_id'][$key],
                    'product_name' => strip_tags(@$input['invoice_items']['product_name'][$key]),
                    'code' => @$input['invoice_items']['code'][$key],
                    'product_qty' => numberClean(@$input['invoice_items']['product_qty'][$key]),
                    'product_price' => numberClean(@$input['invoice_items']['product_price'][$key]),
                    'product_tax' => numberClean(@$input['invoice_items']['product_tax'][$key]),
                    'product_discount' => numberClean(@$input['invoice_items']['product_discount'][$key]),
                    'product_subtotal' => numberClean(@$input['invoice_items']['product_subtotal'][$key]),
                    'total_tax' => numberClean(@$input['invoice_items']['total_tax'][$key]),
                    'total_discount' => numberClean(@$input['invoice_items']['total_discount'][$key]),
                    'product_des' =>  strip_tags(@$input['invoice_items']['product_description'][$key], config('general.allowed')),
                    'unit_value' => $unit_val,
                    'i_class' => 0,
                    'unit' => $input['invoice_items']['unit'][$key], 'ins' => $input['invoice']['ins']
                );

                if ($old_qty > 0) {
                    $stock_update[] = array('id' => $input['invoice_items']['product_id'][$key], 'qty' => $qty - $old_qty);
                } else {
                    $stock_update[] = array('id' => $input['invoice_items']['product_id'][$key], 'qty' => $qty);
                }
            }
            InvoiceItem::insert($products);
            $invoice_d = Invoice::find($id);
            $invoice_d->subtotal = $subtotal;
            $invoice_d->tax = $total_tax;
            $invoice_d->discount = $total_discount;
            $invoice_d->items = $total_qty;
            $invoice_d->save();

            if (isset($input['data2']['custom_field'])) {
                foreach ($input['data2']['custom_field'] as $key => $value) {
                    $fields[] = array('custom_field_id' => $key, 'rid' => $id, 'module' => 2, 'data' =>  strip_tags($value), 'ins' => $input['invoice']['ins']);
                    CustomEntry::where('custom_field_id', '=', $key)->where('rid', '=', $id)->delete();
                }
                CustomEntry::insert($fields);
            }
            $update_variation = new ProductVariation;
            $index = 'id';
            Batch::update($update_variation, $stock_update, $index, true, '-');

            if (is_array($re_stock)) {
                $stock_update_one = array();
                foreach ($re_stock as $key => $value) {
                    $myArray = explode('-', $value);
                    $s_id = $myArray[0];
                    $s_qty = numberClean($myArray[1]);
                    if ($s_id) $stock_update_one[] = array('id' => $s_id, 'qty' => $s_qty);
                }

                Batch::update($update_variation, $stock_update_one, $index, true, '+');
            }


            DB::commit();


            return $result;
        }


        throw new GeneralException(trans('exceptions.backend.invoices.update_error'));
    }

    /**
     * Delete Project Invoice
     *
     * @param Invoice $invoice
     * @return bool
     * @throws GeneralException
     */
    public function delete($invoice)
    {
        // dd($invoice);
        DB::beginTransaction();

        foreach ($invoice->products as $item) {
            if ($item->quote) $item->quote->update(['invoiced' => 'No']);
        }
        $invoice->products()->delete();
        $invoice->transactions()->delete();
        aggregate_account_transactions();
        $result = $invoice->delete();

        DB::commit();
        if ($result) return true;

        throw new GeneralException(trans('exceptions.backend.invoices.delete_error'));
    }
}
