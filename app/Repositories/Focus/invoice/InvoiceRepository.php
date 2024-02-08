<?php

namespace App\Repositories\Focus\invoice;

use App\Models\items\InvoiceItem;
use App\Models\invoice\Invoice;
use App\Exceptions\GeneralException;
use App\Models\invoice_payment\InvoicePayment;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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

        // project filter (project view)
        $q->when(request('project_id'), function($q) {
            $q->whereHas('quotes', function($q) {
                $q->whereHas('project', function($q) {
                    $q->where('projects.id', request('project_id'));
                });
            });
        });


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

        return $q;
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
            if (in_array($key, ['total', 'subtotal', 'tax'])) 
                $bill[$key] = numberClean($val);
        }
        
        $tid = Invoice::where('ins', auth()->user()->ins)->max('tid');
        if ($bill['tid'] <= $tid) $bill['tid'] = $tid+1;
        $result = Invoice::create($bill);
        
        $bill_items = $input['bill_items'];
        foreach ($bill_items as $k => $item) {
            $bill_items[$k] = array_replace($item, [
                'invoice_id' => $result->id,
                'product_price' => floatval(str_replace(',', '', $item['product_price'])),
                'product_subtotal' => floatval(str_replace(',', '', @$item['product_subtotal'])),
            ]);
            $item = $bill_items[$k];
            $bill_items[$k]['product_tax'] = $item['product_price'] - @$item['product_subtotal'];
        }
        InvoiceItem::insert($bill_items);

        // update Quote or PI invoice status
        foreach ($result->products as $key => $item) {
            $quote = $item->quote;
            if ($quote) {
                $quote->update(['invoiced' => 'Yes']);
                if ($key == 0) $result->update(['currency_id' => $quote->currency_id]);
            }
        }

        // convert invoice totals to KES via verified quote items
        $result = $this->convert_totals_to_kes($result);
        
        /** accounting */
        $this->post_invoice($result);

        if ($result) {
            DB::commit();
            return $result;
        }

        DB::rollBack();
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
            foreach (['product_price', 'product_subtotal'] as $key) {
                if (isset($v[$key])) $v[$key] = floatval(str_replace(',', '', $v[$key]));
            }
            if (@$v['product_price'] && @$v['product_subtotal'])
                $v['product_tax'] = $v['product_price'] - $v['product_subtotal'];

            return [
                'id' => $v['id'],
                'reference' => @$v['reference'] ?: '', 
                'description' => $v['description']
            ];
        }, $bill_items);
        Batch::update(new InvoiceItem, $bill_items, 'id');

        // update Quote or PI invoice status
        foreach ($invoice->products as $item) {
            if ($item->quote) $item->quote->update(['invoiced' => 'Yes']);
        }

        // convert invoice totals to KES via verified quote items
        $invoice = $this->convert_totals_to_kes($invoice);

        /**accounting */
        $invoice->transactions()->delete();
        $this->post_invoice($invoice);

        if ($bill) {
            DB::commit();
            return $invoice;        
        }

        DB::rollBack();
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
        if ($invoice->payments()->exists()) {
            foreach ($invoice->payments as $key => $pmt_item) {
                $tids[] = @$pmt_item->paid_invoice->tid ?: '';
            }
            throw ValidationException::withMessages(['Invoice is linked to payments: (' . implode(', ', $tids) . ')']);
        }
            
        DB::beginTransaction();

        // pos invoice
        if ($invoice->product_expense_total > 0) {
            // reverse product qty
            foreach ($invoice->products as $item) {
                $pos_product = $item->product;
                if ($pos_product) $pos_product->decrement('qty', $item->product_qty);
            }
            // delete related payment
            InvoicePayment::whereHas('items', fn($q) => $q->where('invoice_id', $invoice->id))->delete();
        } else {
            // update Quote or PI invoice status
            foreach ($invoice->products as $item) {
                $quote = $item->quote;
                if ($quote) $quote->update(['invoiced' => 'No']);
            }
        }

        $invoice->transactions()->delete();
        aggregate_account_transactions();
        $invoice->products()->delete();
        if ($invoice->delete()) {
            DB::commit();
            return true;
        }

        DB::rollBack();
    }

    // Convert Invoice totals to KES
    public function convert_totals_to_kes($result)
    {
        $quote_ids = [];
        $inv_has_tax = $result['tax_id'] > 0;
        foreach ($result->products as $key => $inv_product) {
            $quote = $inv_product->quote;
            if ($quote) {
                if (in_array($quote->id, $quote_ids)) continue;
                $quote_ids[] = $quote->id;
                $currency = $inv_product->quote->currency;
                if ($currency && $currency->rate > 1) {
                    $subtotal = $quote->verified_products()->sum(DB::raw('product_subtotal * product_qty')) * $currency->rate;
                    $total = $quote->verified_products()->sum(DB::raw('product_price * product_qty')) * $currency->rate;
                    if ($key == 0) {
                        foreach (['total', 'tax', 'subtotal'] as $value) {
                            $result[$value] = 0;
                        }
                    }
                    $result['total'] += $total;
                    $result['subtotal'] += $subtotal;
                    if ($inv_has_tax) $result['tax'] += $total - $subtotal;
                }
            }
        }
        return $result;
    }
}
