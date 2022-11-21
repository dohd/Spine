<?php

namespace App\Repositories\Focus\invoice;

use App\Models\account\Account;
use App\Models\items\InvoiceItem;
use App\Models\invoice\Invoice;
use App\Exceptions\GeneralException;
use App\Models\invoice\PaidInvoice;
use App\Models\project\Project;
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

        // date filter
        if (request('start_date') && request('end_date')) {
            $q->whereBetween('invoicedate', [
                date_for_database(request('start_date')), 
                date_for_database(request('end_date'))
            ]);
        }

        // customer and status filter
        $q->when(request('customer_id'), function ($q) {
            $q->where('customer_id', request('customer_id'));
        })->when(request('invoice_status'), function ($q) {
            $status = request('invoice_status');
            switch ($status) {
                case 'not yet due': 
                    $q->where('invoiceduedate', '>', date('Y-m-d'));
                    break;
                case 'due':    
                    $q->where('invoiceduedate', '<=', date('Y-m-d'));
                    break;                 
            }         
        })->when(request('payment_status'), function ($q) {
            $status = request('payment_status');
            switch ($status) {
                case 'unpaid':
                    $q->where('amountpaid', 0);
                    break; 
                case 'partially paid':
                    $q->whereColumn('amountpaid', '<', 'total')->where('amountpaid', '>', 0);
                    break; 
                case 'paid':
                    $q->whereColumn('amountpaid', '>=', 'total');
                    break; 
            }         
        });

        return $q->get();
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
        $tid = Invoice::max('tid');
        if ($bill['tid'] <= $tid) $bill['tid'] = $tid+1;
        
        $result = Invoice::create($bill);
        
        $bill_items = $input['bill_items'];
        foreach ($bill_items as $k => $item) {
            $bill_items[$k] = array_replace($item, [
                'invoice_id' => $result->id,
                'product_price' => numberClean($item['product_price']),
            ]);
        }
        InvoiceItem::insert($bill_items);

        $invoice_items = $result->products;
        foreach ($invoice_items as $item) {
            // update Quote or PI invoice status
            $quote = $item->quote;
            if ($quote) $quote->update(['invoiced' => 'Yes']);
        }
        
        /** accounting */
        $this->post_transaction_project_invoice($result);

        if ($result) {
            DB::commit();
            return $result;
        }

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
            if (in_array($key, ['total', 'subtotal', 'tax'])) 
                $bill[$key] = numberClean($val);
        }
        $duedate = $bill['invoicedate'] . ' + ' . $bill['validity'] . ' days';
        $bill['invoiceduedate'] = date_for_database($duedate);
        $invoice->update($bill);

        // update invoice items
        $bill_items = $input['bill_items'];
        $bill_items = array_map(function ($v) { 
            return [
                'id' => $v['id'],
                'reference' => $v['reference'], 
                'description' => $v['description']
            ];
        }, $bill_items);
        Batch::update(new InvoiceItem, $bill_items, 'id');

        $invoice_items = $invoice->products;
        foreach ($invoice_items as $item) {
            $quote = $item->quote;
            if ($quote) {
                // close associated projects
                $project = Project::where('main_quote_id', $quote->id)->first();
                if ($project) {
                    $project->update([
                        'status' => 'closed',
                        'end_note' => 'Invoiced',
                        'end_date' => date('Y-m-d'),
                        'ended_by' => auth()->user()->id,
                    ]);
                }
            }
        }

        /**accounting */
        $invoice->transactions()->delete();
        $this->post_transaction_project_invoice($invoice);

        if ($bill) {
            DB::commit();
            return $invoice;        
        }
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

        // pos invoice
        if ($invoice->product_expense_total > 0) {
            // reverse product qty
            foreach ($invoice->products as $item) {
                $pos_product = $item->product;
                if ($pos_product) $pos_product->decrement('qty', $item->product_qty);
            }

            // delete payments
            $payment_ids = PaidInvoice::whereHas('items', fn($q) => $q->where('invoice_id', $invoice->id))
                ->pluck('id')->toArray();
            foreach ($payment_ids as $val) {
                $this->delete_invoice_payment($val);
            }
        } else {
            // project invoice
            $invoice_items = $invoice->products;
            foreach ($invoice_items as $item) {
                $quote = $item->quote;
                if ($quote) $quote->update(['invoiced' => 'No']);
            }
        }
        $invoice->transactions()->delete();
        aggregate_account_transactions();

        if ($invoice->delete()) {
            DB::commit();
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.invoices.delete_error'));
    }

    /**
     * Project Invoice transaction
     */
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

        unset($dr_data['debit'], $dr_data['is_primary']);

        // credit Revenue Account (Income)
        $inc_cr_data = array_replace($dr_data, [
            'account_id' => $result->account_id,
            'credit' => $result->subtotal,
        ]);
        Transaction::create($inc_cr_data);

        // credit tax (VAT)
        if ($result->tax > 0) {
            $account = Account::where('system', 'tax')->first(['id']);
            $tax_cr_data = array_replace($dr_data, [
                'account_id' => $account->id,
                'credit' => $result->tax,
            ]);
            Transaction::create($tax_cr_data);
        }

        // WIP and COG transactions
        $tr_data = array();

        // stock amount for items issued from inventory
        $store_inventory_amount = 0;
        // direct purchase item amounts for item directly issued to project
        $dirpurch_inventory_amount = 0;
        $dirpurch_expense_amount = 0;
        $dirpurch_asset_amount = 0;

        // invoice related quotes and pi
        $quote_ids = $result->products()->pluck('quote_id')->toArray();
        $quotes = Quote::whereIn('id', $quote_ids)->get();
        foreach ($quotes as $quote) {
            $store_inventory_amount  = $quote->projectstock->sum('subtotal');
            // direct purchase items issued to project
            if (isset($quote->project_quote->project)) {
                foreach ($quote->project_quote->project->purchase_items as $item) {
                    if ($item->itemproject_id) {
                        $subtotal = $item->amount - $item->taxrate;
                        if ($item->type == 'Expense') $dirpurch_expense_amount += $subtotal;
                        elseif ($item->type == 'Stock') $dirpurch_inventory_amount += $subtotal;
                        elseif ($item->type == 'Asset') $dirpurch_asset_amount += $subtotal;
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

}
