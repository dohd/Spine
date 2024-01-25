<?php

namespace App\Repositories;

use App\Models\account\Account;
use App\Models\invoice\Invoice;
use App\Models\quote\Quote;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;

trait Accounting
{
    /**
     * Customer Opening Balance Transaction
     * @param object $manual_journal
     */
    public function post_customer_opening_balance($manual_journal)
    {
        // debit Accounts Receivable (Debtor)
        $tr_category = Transactioncategory::where('code', 'genjr')->first(['id', 'code']);
        $dr_data = [
            'tid' => Transaction::max('tid') + 1,
            'account_id' => $manual_journal->account_id,
            'trans_category_id' => $tr_category->id,
            'tr_date' => $manual_journal->date,
            'due_date' => $manual_journal->date,
            'user_id' => $manual_journal->user_id,
            'note' => $manual_journal->note,
            'debit' => $manual_journal->open_balance,
            'ins' => $manual_journal->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $manual_journal->id,
            'user_type' => 'customer',
            'is_primary' => 1,
            'customer_id' => @$manual_journal->customer_id,
            'man_journal_id' => @$manual_journal->id,
        ];
        Transaction::create($dr_data);

        // credit Retained Earning (Equity)
        unset($dr_data['debit'], $dr_data['is_primary']);
        $account = Account::where('system', 'retained_earning')->first(['id']);
        $cr_data = array_replace($dr_data, [
            'account_id' => $account->id, 
            'credit' => $manual_journal->open_balance
        ]);
        Transaction::create($cr_data);
        aggregate_account_transactions();
    }

    /**
     * Withholding Transaction
     * @param object $withholding
     */
    public function post_withholding($withholding)
    {
        // credit Accounts Receivable (Debtors)
        $account = Account::where('system', 'receivable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'withholding')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $withholding->amount,
            'tr_date' => $withholding->tr_date,
            'due_date' => $withholding->tr_date,
            'user_id' => $withholding->user_id,
            'note' => $withholding->note,
            'ins' => $withholding->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $withholding->id,
            'user_type' => 'customer',
            'is_primary' => 1,
            'customer_id' => $withholding->customer_id,
            'wht_id' => $withholding->id,
        ];
        Transaction::create($cr_data);

        // debit Withholding Account
        $account = Account::when($withholding->certificate == 'vat', function ($q) {
            $q->where('system', 'withholding_vat');
        })->when($withholding->certificate == 'tax', function ($q) {
            $q->where('system', 'withholding_inc');
        })->first();

        unset($cr_data['credit'], $cr_data['is_primary']);
        $dr_data = array_replace($cr_data, [
            'account_id' => $account->id,
            'debit' => $withholding->amount
        ]);
        Transaction::create($dr_data);
        aggregate_account_transactions(); 
    }

    /**
     * Credit Note and Debit Note Transaction
     * @param object $resource
     */
    public function post_creditnote_debitnote($resource)
    {  
        $account = Account::where('system', 'receivable')->first(['id']);
        $tid = Transaction::max('tid') + 1;
        $data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'tr_ref' => $resource->id,
            'tr_date' => date('Y-m-d'),
            'due_date' => $resource->date,
            'user_id' => $resource->user_id,
            'note' => $resource->note,
            'ins' => $resource->ins,
            'user_type' => 'customer',
            'is_primary' => 0,
            'customer_id' => $resource->customer_id,
            'dnote_id' => null,
            'cnote_id' => null,
            'debit' => 0,
            'credit' => 0,
        ];

        $tr_data = [];
        $is_debitnote = $resource->is_debit;
        if ($is_debitnote) {
            // debit Receivable Account (Creditors)
            $tr_category = Transactioncategory::where('code', 'dnote')->first(['id', 'code']);
            $data = $data + ['trans_category_id' => $tr_category->id, 'tr_type' => $tr_category->code];
            $tr_data[] = array_replace($data, [
                'debit' => $resource->total,
                'is_primary' => 1,
                'dnote_id' => $resource->id,
            ]);

            // credit Customer Income (intermediary ledger account)
            // $account = Account::where('system', 'client_income')->first(['id']);
            // $tr_data[] = array_replace($data, [
            //     'account_id' => $account->id,
            //     'credit' => $resource->subtotal,
            // ]);

            // credit Revenue Account (Income)
            $tr_data[] = array_replace($data, [
                'account_id' => Invoice::find($resource->invoice_id)->account_id,
                'credit' => $resource->subtotal,
                'dnote_id' => $resource->id,
            ]);

            // credit tax (VAT)
            $account = Account::where('system', 'tax')->first(['id']);
            if ($resource->tax > 0) {
                $tr_data[] = array_replace($data, [
                    'account_id' => $account->id,
                    'credit' => $resource->tax,
                    'dnote_id' => $resource->id,
                ]);
            }
        } else {
            // credit Receivable Account (Debtors)
            $tr_category = Transactioncategory::where('code', 'cnote')->first(['id', 'code']);
            $data = $data + ['trans_category_id' => $tr_category->id, 'tr_type' => $tr_category->code];
            $tr_data[] = array_replace($data, [
                'credit' => $resource->total,
                'is_primary' => 1,
                'cnote_id' => $resource->id,
            ]);

            // debit Customer Income (intermediary ledger account)
            // $account = Account::where('system', 'client_income')->first(['id']);
            // $tr_data[] = array_replace($data, [
            //     'account_id' => $account->id,
            //     'debit' => $resource->subtotal,
            // ]);

            // debit Revenue Account (Income)
            $tr_data[] = array_replace($data, [
                'account_id' => Invoice::find($resource->invoice_id)->account_id,
                'debit' => $resource->subtotal,
                'cnote_id' => $resource->id,
            ]);

            // debit tax (VAT)
            $account = Account::where('system', 'tax')->first(['id']);
            if ($resource->tax > 0) {
                $tr_data[] = array_replace($data, [
                    'account_id' => $account->id,
                    'debit' => $resource->tax,
                    'cnote_id' => $resource->id,
                ]);
            }
        }
        Transaction::insert($tr_data);
        aggregate_account_transactions();
    }

    /**
     * Invoice Transaction
     * @param object $invoice
     */
    public function post_invoice($invoice)
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
            'debit' => 0,
            'credit' => 0,
            'customer_id' => $invoice->customer_id,
            'invoice_id' => $invoice->id
        ];
        Transaction::create($dr_data);

        unset($dr_data['debit'], $dr_data['is_primary']);

        // credit Revenue Account (Income)
        $inc_cr_data = array_replace($dr_data, [
            'account_id' => $invoice->account_id,
            'credit' => $invoice->subtotal,
        ]);
        Transaction::create($inc_cr_data);

        // credit tax (VAT)
        if ($invoice->tax > 0) {
            $account = Account::where('system', 'tax')->first(['id']);
            $tax_cr_data = array_replace($dr_data, [
                'account_id' => $account->id,
                'credit' => $invoice->tax,
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
        $quote_ids = $invoice->products->pluck('quote_id')->toArray();
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
        Transaction::insert($tr_data);        
        aggregate_account_transactions();        
    }
    
    /**
     * Invoice Deposit Transaction
     * @param object $invoice_deposit
     */
    public function post_invoice_deposit($invoice_deposit)
    {
        $account = Account::where('system', 'receivable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'dep')->first(['id', 'code']);
        $tid = Transaction::where('ins', $invoice_deposit->ins)->max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $invoice_deposit->amount,
            'tr_date' => $invoice_deposit->date,
            'due_date' => $invoice_deposit->date,
            'user_id' => $invoice_deposit->user_id,
            'note' => ($invoice_deposit->note ?: "{$invoice_deposit->payment_mode} - {$invoice_deposit->reference}"),
            'ins' => $invoice_deposit->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $invoice_deposit->id,
            'user_type' => 'customer',
            'is_primary' => 1,
            'customer_id' => @$invoice_deposit->customer_id,
            'deposit_id' => @$invoice_deposit->id,
        ];

        if ($invoice_deposit->is_advance_allocation) {
            // credit Receivables (Debtors)
            Transaction::create($cr_data);
            
            // debit customer Advance DEP
            unset($cr_data['credit'], $cr_data['is_primary']);
            $account = Account::where('system', 'adv_dep')->first(['id']);
            $dr_data = array_replace($cr_data, [
                'account_id' => $account->id,
                'debit' => $invoice_deposit->amount,
            ]);    
            Transaction::create($dr_data);
        } else {
            /**
             * Non-allocation of lumpsome payment
             */
            if ($invoice_deposit->payment_type == 'advance_payment') {
                // credit customer Advance DEP
                $account = Account::where('system', 'adv_dep')->first(['id']);
                $cr_data['account_id'] = $account->id;
                Transaction::create($cr_data);
                
                // debit bank
                unset($cr_data['credit'], $cr_data['is_primary']);
                $dr_data = array_replace($cr_data, [
                    'account_id' => $invoice_deposit->account_id,
                    'debit' => $invoice_deposit->amount,
                ]);    
                Transaction::create($dr_data);
            } else {
                // credit Receivables (Debtors)
                Transaction::create($cr_data);
                            
                // debit bank
                unset($cr_data['credit'], $cr_data['is_primary']);
                $dr_data = array_replace($cr_data, [
                    'account_id' => $invoice_deposit->account_id,
                    'debit' => $invoice_deposit->amount,
                ]);    
                Transaction::create($dr_data);
            }
        }
        aggregate_account_transactions();        
    }
}