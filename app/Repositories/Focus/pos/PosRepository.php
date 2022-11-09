<?php

namespace App\Repositories\Focus\pos;

use App\Models\account\Account;
use App\Models\invoice\Invoice;
use App\Models\invoice\PaidInvoice;
use App\Models\items\InvoiceItem;
use App\Models\items\PaidInvoiceItem;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
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
            if (in_array($key, ['total', 'subtotal', 'tax_id'])) 
                $input[$key] = numberClean($val);

            $item_keys = ['product_qty', 'product_price', 'product_tax', 'product_subtotal', 'total_tax'];
            if (in_array($key, $item_keys)) 
                $input[$key] = array_map(fn($v) => numberClean($v), $val);
        }
        
        // invoice
        $inv_data = Arr::only($input, [
            'invoicedate', 'invoiceduedate', 'subtotal', 'total', 'customer_id', 'tax_id', 'notes'
        ]);
        $inv_data = array_replace($inv_data, [
            'tid' => Invoice::max('tid') + 1,
            'notes' => $inv_data['notes'] ?: 'POS Transaction',
            'term_id' => 1,
            'user_id' => auth()->user()->id,
            'user_id' => auth()->user()->ins,
        ]);
        $result = Invoice::create($inv_data);

        // invoice items
        $inv_items_data = Arr::only($input, [
            'product_id', 'product_name', 'product_qty', 'product_price', 'product_tax', 
            'product_subtotal', 'total_tax', 'unit_m'
        ]);
        $inv_items_data = modify_array($inv_items_data);
        $inv_items_data = array_map(function ($v) use($result) {
            $tax = $v['product_tax'] * 0.01;
            $v['product_price'] = $v['product_price'] * $v['product_qty'] * (1+$tax);
            $v['total_tax'] = $v['product_subtotal'] * $tax;
            
            $v['description'] = $v['product_name'];
            unset($v['product_name'], $v['unit_m']);
            return array_replace($v, [
                'unit' => 'Lot',
                'unit_value' => 1,
                'invoice_id' => $result->id,
            ]);
        }, $inv_items_data);
        InvoiceItem::insert($inv_items_data);

        // reduce inventory
        foreach ($result->items as $item) {
            $product = $item->product;
            if ($product) $product->decrement('qty', $item->product_qty);
        }

        // payment items
        $pmt_items_data = Arr::only($input, ['p_amount', 'p_method']);
        $pmt_items_data = modify_array($pmt_items_data);
        $pmt_items_data = array_filter($pmt_items_data, fn($v) => numberClean($v['p_amount']) > 0);
        if ($pmt_items_data) $this->generate_payment($pmt_items_data, $result);

        dd($inv_data, $inv_items_data, $pmt_items_data, $input);

        /** accounting */
        $this->post_transaction($result);

        if ($result) {
            DB::commit();
            return $result;
        }

        throw new GeneralException('Error Creating Invoice');
    }

    /**
     * Generate POS Invoice Payment
     */
    public function generate_payment($input, $invoice)
    {
        $pmt_data = [
            'tid' => PaidInvoice::max('tid') + 1,
            'account_id' => $invoice->account_id,
            'customer_id' => $invoice->customer_id,
            'date' => $invoice->invoicedate,
            'amount' => $invoice->total,
            'allocate_ttl' => $invoice->total,
            'payment_type' => 'per_invoice',
            'ins' => $invoice->ins,
            'user_id' => $invoice->user_id,
        ];
        foreach ($input as $pmt_data) {
            $pmt_data = array_replace($pmt_data, [
                'payment_mode' => $pmt_data['p_method'],
                'reference' => '123456',
            ]);
            $result = PaidInvoice::create($pmt_data);

            $pmt_item_data = [
                'paidinvoice_id' => $result->id,
                'invoice_id' => $invoice->id,
                'paid' => $result->amount,
            ];
            PaidInvoiceItem::create($pmt_item_data);

            /**accounting */
            $this->post_payment_transaction($result);
        }
    }

    /**
     * Post Pos Invoice Transaction
     */
    public function post_transaction($invoice)
    {
        // debit Accounts Receivable (Debtors)
        $account = Account::where('system', 'receivable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'inv')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
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
        // credit Tax (VAT)
        $account = Account::where('system', 'tax')->first(['id']);
        $tax_cr_data = array_replace($dr_data, [
            'account_id' => $account->id,
            'credit' => $invoice->tax,
        ]);
        Transaction::insert([$inc_cr_data, $tax_cr_data]);

        // debit COG
        $account = Account::where('system', 'cog')->first(['id']);
        $cog_dr_data = array_replace($dr_data, [
            'account_id' => $account->id,
            'debit' => $invoice->subtotal,
        ]);
        Transaction::create($cog_dr_data);

        // credit Inventory
        $account = Account::where('system', 'stock')->first(['id']);
        $stock_cr_data = array_replace($dr_data, [
            'account_id' => $account->id,
            'credit' => $invoice->subtotal,
        ]);
        Transaction::create($stock_cr_data);
        aggregate_account_transactions();        
    }

    /**
     * Post POS Payment Transaction
     */
    public function post_payment_transaction($payment)
    {
        // credit Accounts Receivable (Debtors)
        $account = Account::where('system', 'receivable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'pmt')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
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