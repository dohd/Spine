<?php

namespace App\Repositories\Focus\invoice;

use App\Models\account\Account;
use App\Models\items\CustomEntry;
use App\Models\items\InvoiceItem;
use App\Models\invoice\Invoice;
use App\Exceptions\GeneralException;
use App\Models\invoice\PaidInvoice;
use App\Models\items\PaidInvoiceItem;
use App\Models\transaction\Transaction;
use App\Repositories\BaseRepository;
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

        return $q->get(['id', 'tid', 'customer_id', 'invoicedate', 'invoiceduedate', 'total', 'status', 'notes', 'amountpaid']);
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
        if ($bill) return $invoice;        
    }


    // invoice transacton
    public function post_transaction_project_invoice($result)
    {
        // debit Accounts Receivable (Debtors)
        $account = Account::where('system', 'receivable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'inv')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $dr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'debit' => $result->total,
            'tr_date' => $result->invoicedate,
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

        // credit Customer Income (intermediary ledger account)
        // $account = Account::where('system', 'client_income')->first(['id']);
        // unset($dr_data['debit'], $dr_data['is_primary']);
        // $inc_cr_data = array_replace($dr_data, [
        //     'account_id' => $account->id,
        //     'credit' => $result->subtotal,
        // ]);

        // credit Revenue Account (Income)
        unset($dr_data['debit'], $dr_data['is_primary']);
        $inc_cr_data = array_replace($dr_data, [
            'account_id' => $result->account_id,
            'credit' => $result->subtotal,
        ]);

        // credit tax (VAT)
        $account = Account::where('system', 'tax')->first(['id']);
        $tax_cr_data = array_replace($dr_data, [
            'account_id' => $account->id,
            'credit' => $result->tax,
        ]);
        Transaction::insert([$inc_cr_data, $tax_cr_data]);

        // WIP and COG Accounts
        $tr_data = [];
        // invoice related quotes and pi query
        $q = Quote::whereIn('id', function ($q) use($result) {
            $q->select('quote_id')->from('invoice_items')->where('invoice_id', $result->id);
        });
        // update query results
        $q1 = clone $q;
        $q1->update(['closed_by' => $result['user_id']]);
        // fetch query results
        $quotes = $q->get();

        // stock issued from store to project
        $store_inventory_amount = 0;
        // direct purchase items issued directly to project
        $dirpurch_inventory_amount = 0;
        $dirpurch_expense_amount = 0;
        $dirpurch_asset_amount = 0;
        foreach ($quotes as $quote) {
            $store_inventory_amount += $quote->issuance->sum('total');
            if (isset($quote->project_quote->project)) {
                foreach ($quote->project_quote->project->purchase_items as $item) {
                    $subttl = $item['amount'] - $item['taxrate'];
                    // project items
                    if ($item['itemproject_id']) {
                        if ($item['type'] == 'Expense') $dirpurch_expense_amount += $subttl;
                        elseif ($item['type'] == 'Stock') $dirpurch_inventory_amount += $subttl;
                        elseif ($item['type'] == 'Asset') $dirpurch_asset_amount += $subttl;
                    }
                    
                }
            }
        }

        // credit WIP account and debit COG
        $wip_account = Account::where('system', 'wip')->first(['id']);
        $cog_account = Account::where('system', 'cog')->first(['id']);
        $cr_data = array_replace($dr_data, ['account_id' => $wip_account->id, 'is_primary' => 1]);
        $dr_data = array_replace($dr_data, ['account_id' => $cog_account->id, 'is_primary' => 0]);
        if ($dirpurch_inventory_amount > 0) {
            $tr_data[] = array_replace($cr_data, ['credit' => $dirpurch_inventory_amount]);
            $tr_data[] = array_replace($dr_data, ['debit' => $dirpurch_inventory_amount]);
        }
        if ($dirpurch_expense_amount > 0) {
            $tr_data[] = array_replace($cr_data, ['credit' => $dirpurch_expense_amount]);
            $tr_data[] = array_replace($dr_data, ['debit' => $dirpurch_expense_amount]);
        }
        if ($dirpurch_asset_amount > 0) {
            $tr_data[] = array_replace($cr_data, ['credit' => $dirpurch_asset_amount]);
            $tr_data[] = array_replace($dr_data, ['debit' => $dirpurch_asset_amount]);
        }
        if ($store_inventory_amount > 0) {
            $tr_data[] = array_replace($cr_data, ['credit' => $store_inventory_amount]);
            $tr_data[] = array_replace($dr_data, ['debit' => $store_inventory_amount]);
        }

        $tr_data = array_map(function ($v) {
            if (isset($v['debit']) && $v['debit'] > 0) $v['credit'] = 0;
            elseif (isset($v['credit']) && $v['credit'] > 0) $v['debit'] = 0;
            return $v;
        }, $tr_data);
        Transaction::insert($tr_data);        
        aggregate_account_transactions();        
    }

    /**
     * Create invoice payment
     */
    public function create_invoice_payment(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        foreach ($data as $key => $val) {
            if ($key == 'date') $data[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'allocate_ttl'])) 
                $data[$key] = numberClean($val);
        }

        $result = (object) array();
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
                $this->post_transaction_invoice_payment($result);
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
                elseif ($invoice->total > $invoice->amountpaid) $invoice->update(['status' => 'partial']);
                elseif ($invoice->total == $invoice->amountpaid) $invoice->update(['status' => 'paid']);
            }
        }
        
        /**accounting */
        if ($is_payment) $this->post_transaction_invoice_payment($result);
        
        DB::commit();
        if ($result) return $result;

        throw new GeneralException('Error Creating Invoice');
    }

    /**
     * Update invoice payment
     */
    public function update_invoice_payment($payment, array $input)
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
                elseif ($invoice->total > $invoice->amountpaid) $invoice->update(['status' => 'partial']);
                elseif ($invoice->total == $invoice->amountpaid) $invoice->update(['status' => 'paid']);
            }
            // delete items with zero payment
            if ($item->paid == 0) $item->delete();
        }

        /** accounting */
        $payment->transactions()->delete();
        $this->post_transaction_invoice_payment($payment);

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
        // reverse customer unallocated amount
        $unallocated = $payment->amount - $payment->allocate_ttl;
        $payment->customer->decrement('on_account', $unallocated);
        // reverse payment
        foreach ($payment->items as $item) {
            if ($item->invoice) {
                $invoice = $item->invoice;
                $invoice->decrement('amountpaid', $item->paid);
                if ($invoice->amountpaid == 0) $invoice->update(['status' => 'due']);
                elseif ($invoice->total > $invoice->amountpaid) $invoice->update(['status' => 'partial']);
                elseif ($invoice->total == $invoice->amountpaid) $invoice->update(['status' => 'paid']);
            }            
        }
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
        $account = Account::where('system', 'receivable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'pmt')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $result->amount,
            'tr_date' => $result->date,
            'due_date' => $result->date,
            'user_id' => $result->user_id,
            'note' => $result->payment_mode . ' - ' . $result->reference,
            'ins' => $result->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $result->id,
            'user_type' => 'customer',
            'is_primary' => 1,
        ];

        if (in_array($result->payment_type, ['per_invoice', 'on_account'])) {
            // credit Accounts Receivable (Debtors)
            Transaction::create($cr_data);
            
            // debit Bank Account
            unset($cr_data['credit'], $cr_data['is_primary']);
            $dr_data = array_replace($cr_data, [
                'account_id' => $result->account_id,
                'debit' => $result->amount
            ]);
            Transaction::create($dr_data);
        } else {
            $adv_account = Account::where('system', 'adv_pmt')->first(['id']);
            if ($result->allocate_ttl == 0)  {
                // credit Advance Payment Account
                $cr_data = array_replace($cr_data, [
                    'account_id' => $adv_account->id,
                ]);
                Transaction::create($cr_data);

                // debit Bank Account
                unset($cr_data['credit'], $cr_data['is_primary']);
                $dr_data = array_replace($cr_data, [
                    'account_id' => $result->account_id,
                    'debit' => $result->amount
                ]);
                Transaction::create($dr_data);
            } else {
                // credit Accounts Receivable (Debtors)
                $cr_data['credit'] = $result->allocate_ttl;
                Transaction::create($cr_data);

                // debit Advance Payment Account
                unset($cr_data['credit'], $cr_data['is_primary']);
                $cr_data = array_replace($cr_data, [
                    'account_id' => $adv_account->id,
                    'debit' => $result->allocate_ttl
                ]);
                Transaction::create($cr_data);
            }
        }
        aggregate_account_transactions();     
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

        $quote_ids = $invoice->products()->pluck('quote_id')->toArray();
        Quote::whereIn('id', $quote_ids)->update(['invoiced' => 'No']);
        $invoice->transactions()->delete();
        aggregate_account_transactions();
        $result = $invoice->delete();

        DB::commit();
        if ($result) return true;

        throw new GeneralException(trans('exceptions.backend.invoices.delete_error'));
    }
}
