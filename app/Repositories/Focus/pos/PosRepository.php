<?php

namespace App\Repositories\Focus\pos;

use App\Models\account\Account;
use App\Models\invoice\Invoice;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\items\InvoiceItem;
use App\Models\items\InvoicePaymentItem;
use App\Models\product\ProductVariation;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
use App\Repositories\Focus\product\ProductRepository;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * Class InvoiceRepository.
 */
class PosRepository extends BaseRepository
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

        return $q->get();
    }

    // generate product purchase price
    public function gen_purchase_price($id)
    {
        $product = ProductVariation::find($id);
        return (new ProductRepository)->eval_purchase_price($product->id, $product->qty, $product->purchase_price);
    }

    // generate product default unit of measure
    public function gen_unit_measure($id)
    {
        $variation = ProductVariation::find($id);
        $base_unit = $variation->product->units()->where('unit_type', 'base')->first();
        if (!$base_unit) throw ValidationException::withMessages(['please set product units!']);

        return ['code' => $base_unit->code, 'value' => $base_unit->base_ratio];
    }

    /**
     * Create POS Transaction
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if (in_array($key, ['invoicedate', 'invoiceduedate'])) 
                $input[$key] = date_for_database($val);
            if (in_array($key, ['total', 'subtotal', 'tax', 'tax_id'])) 
                $input[$key] = numberClean($val);

            $item_keys = ['product_qty', 'product_price', 'product_tax', 'product_subtotal', 'total_tax'];
            if (in_array($key, $item_keys)) 
                $input[$key] = array_map(fn($v) => numberClean($v), $val);
        }
        
        // invoice
        $inv_data = Arr::only($input, [
            'invoicedate', 'invoiceduedate', 'subtotal', 'tax', 'total', 'customer_id', 'tax_id', 'notes', 
            'account_id', 'claimer_tax_pin', 'claimer_company'
        ]);
        $inv_data = array_replace($inv_data, [
            'tid' => Invoice::where('ins', auth()->user()->ins)->max('tid') + 1,
            'notes' => $inv_data['notes'] ?: 'POS Transaction',
            'term_id' => 1,
            'user_id' => auth()->user()->id,
            'ins' => auth()->user()->ins,
        ]);
        $result = Invoice::create($inv_data);

        // invoice items
        $inv_items_data = Arr::only($input, [
            'product_id', 'product_name', 'product_qty', 'product_price', 'product_tax', 
            'product_subtotal', 'total_tax', 'unit_m'
        ]);
        $inv_items_data = modify_array($inv_items_data);
        $inv_items_data = array_map(function ($v) use($result) {
            // expense
            $v['product_purchase_price'] = $this->gen_purchase_price($v['product_id']);
            $v['product_expense_amount'] = $v['product_purchase_price'] * $v['product_qty'];

            // sale
            $tax = $v['product_tax'] / 100;
            $v['total_tax'] = $v['product_subtotal'] * $tax;
            $v['product_amount'] = $v['product_price'] * $v['product_qty'] * (1+$tax);
            
            $v['description'] = $v['product_name'];
            unset($v['product_name'], $v['unit_m']);
            return array_replace($v, [
                'unit' => $this->gen_unit_measure($v['product_id'])['code'],
                'unit_value' => $this->gen_unit_measure($v['product_id'])['value'],
                'invoice_id' => $result->id,
            ]);
        }, $inv_items_data);
        InvoiceItem::insert($inv_items_data);

        // update invoice products expense total
        $result->update(['product_expense_total' => $result->products->sum('product_expense_amount')]);

        // reduce inventory stock
        foreach ($result->products as $item) {
            $pos_product = $item->product;
            if ($pos_product) $pos_product->decrement('qty', $item->product_qty);
        }

        /** accounting */
        $this->post_transaction($result);

        // on purchase and direct payment
        if ($input['is_pay']) $this->generate_payment($input, $result);

        if ($result) {
            DB::commit();
            return $result;
        }

        throw new GeneralException('Error Creating Invoice');
    }

    /**
     * Post Pos Invoice Transaction
     */
    public function post_transaction($invoice)
    {
        // debit Accounts Receivable (Debtors)
        $account = Account::where('system', 'receivable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'inv')->first(['id', 'code']);
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
        $dr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'debit' => $invoice->total,
            'tr_date' => $invoice->invoicedate,
            'due_date' => $invoice->invoiceduedate,
            'user_id' => $invoice->user_id,
            'note' => $invoice->notes,
            'ins' => $invoice->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $invoice->id,
            'user_type' => 'customer',
            'is_primary' => 1,
        ];
        Transaction::create($dr_data);

        unset($dr_data['debit'], $dr_data['is_primary']);

        // credit Revenue Account (Income)
        $inc_cr_data = array_replace($dr_data, [
            'account_id' => $invoice->account_id,
            'credit' => $invoice->subtotal,
        ]);
        Transaction::create($inc_cr_data);

        // credit Tax (VAT)
        if ($invoice->tax > 0) {
            $account = Account::where('system', 'tax')->first(['id']);
            $tax_cr_data = array_replace($dr_data, [
                'account_id' => $account->id,
                'credit' => $invoice->tax,
            ]);
            Transaction::create($tax_cr_data);
        }
        
        if ($invoice->product_expense_total > 0) {
            // debit COG
            $account = Account::where('system', 'cog')->first(['id']);
            $cog_dr_data = array_replace($dr_data, [
                'account_id' => $account->id,
                'debit' => $invoice->product_expense_total,
            ]);
            Transaction::create($cog_dr_data);

            // credit Inventory
            $account = Account::where('system', 'stock')->first(['id']);
            $stock_cr_data = array_replace($dr_data, [
                'account_id' => $account->id,
                'credit' => $invoice->product_expense_total,
            ]);
            Transaction::create($stock_cr_data);
            aggregate_account_transactions();   
        }
    }    

    /**
     * Generate POS Invoice Payment
     */
    public function generate_payment($input, $invoice)
    {
        $pmt_items_data = Arr::only($input, ['p_amount', 'p_method']);
        $pmt_items_data = modify_array($pmt_items_data);
        $pmt_items_data = array_filter($pmt_items_data, fn($v) => numberClean($v['p_amount']) > 0);
        if (!$pmt_items_data) throw ValidationException::withMessages(['Payment confirmation details required!']);
            
        $pmt_data = [
            'tid' => InvoicePayment::where('ins', auth()->user()->ins)->max('tid') + 1,
            'account_id' => $input['p_account'],
            'customer_id' => $invoice->customer_id,
            'date' => $invoice->invoicedate,
            'amount' => $invoice->total,
            'allocate_ttl' => $invoice->total,
            'reference' => $input['pmt_reference'],
            'payment_type' => 'per_invoice',
            'ins' => $invoice->ins,
            'user_id' => $invoice->user_id,
        ];
        foreach ($pmt_items_data as $row) {
            $pmt_data = array_replace($pmt_data, [
                'payment_mode' => $row['p_method'],
            ]);
            $result = InvoicePayment::create($pmt_data);

            $pmt_item_data = [
                'paidinvoice_id' => $result->id,
                'invoice_id' => $invoice->id,
                'paid' => $result->amount,
            ];
            InvoicePaymentItem::create($pmt_item_data);

            // update invoice amount paid
            $invoice->increment('amountpaid', $result->amount);
            // update invoice payment status
            if ($invoice->amountpaid == 0) $invoice->update(['status' => 'due']);
            elseif (round($invoice->total) > round($invoice->amountpaid)) $invoice->update(['status' => 'partial']);
            else $invoice->update(['status' => 'paid']);

            /**accounting */
            $this->post_payment_transaction($result);
        }
    }

    /**
     * Post POS Payment Transaction
     */
    public function post_payment_transaction($payment)
    {
        // credit Accounts Receivable (Debtors)
        $account = Account::where('system', 'receivable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'pmt')->first(['id', 'code']);
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid');
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
        Transaction::create($cr_data);
            
        // debit Bank
        unset($cr_data['credit'], $cr_data['is_primary']);
        $dr_data = array_replace($cr_data, [
            'account_id' => $payment->account_id,
            'debit' => $payment->amount
        ]);
        Transaction::create($dr_data);
        aggregate_account_transactions();    
    }
}
