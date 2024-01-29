<?php

namespace App\Repositories;

use App\Models\account\Account;
use App\Models\account\AccountType;
use App\Models\assetequipment\Assetequipment;
use App\Models\deposit\Deposit;
use App\Models\invoice\Invoice;
use App\Models\items\JournalItem;
use App\Models\manualjournal\Journal;
use App\Models\opening_stock\OpeningStock;
use App\Models\quote\Quote;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;

trait Accounting
{
    /**
     * Ledger Account Opening Balance
     * @param object $account
     */
    public function post_ledger_opening_balance($manual_journal)
    {
        $account = Account::where('system', 'retained_earning')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'genjr')->first(['id', 'code']);
        $tr_data = [
            'tid' => Transaction::max('tid')+1,
            'trans_category_id' => $tr_category->id,
            'tr_date' => $manual_journal->date,
            'due_date' => $manual_journal->date,
            'user_id' => $manual_journal->user_id,
            'note' => $manual_journal->note,
            'ins' => $manual_journal->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $manual_journal->id,
            'user_type' => 'company',
            'man_journal_id' => @$manual_journal->id,
            'debit' => 0,
            'credit' => 0,
        ];

        $account_type = @$manual_journal->ledger_account->account_type;
        if (in_array($account_type, ['Asset', 'Expense'])) {
            // debit [Asset | Expense] Account 
            $dr_data = array_replace($tr_data, [
                'account_id' => $manual_journal->account_id, 
                'debit' => $manual_journal->opening_balance,
                'is_primary' => 1,
            ]);
            Transaction::create($dr_data);
            // credit Retained Earnings Account
            $cr_data = array_replace($tr_data, [
                'account_id' => $account->id, 
                'credit' => $manual_journal->opening_balance,
            ]);
            Transaction::create($cr_data);
        } else {
            // credit "Other" Account
            $cr_data = array_replace($tr_data, [
                'account_id' => $manual_journal->account_id, 
                'credit' => $manual_journal->opening_balance,
                'is_primary' => 1,
            ]);
            Transaction::create($cr_data);
            // debit Retained Earnings Account
            $dr_data = array_replace($tr_data, [
                'account_id' => $account->id, 
                'credit' => $manual_journal->opening_balance,
            ]);
            Transaction::create($dr_data);
        }
        aggregate_account_transactions();
    }

    /**
     * Customer Opening Balance
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

    /**
     * Supplier Opening Balance 
     * @param object $manual_journal
     */
    public function post_supplier_opening_balance($manual_journal)
    {
        // credit Accounts Payable (Creditor)
        $tr_category = Transactioncategory::where('code', 'genjr')->first(['id', 'code']);
        $cr_data = [
            'tid' => Transaction::max('tid') + 1,
            'account_id' => $manual_journal->account_id,
            'trans_category_id' => $tr_category->id,
            'tr_date' => $manual_journal->date,
            'due_date' => $manual_journal->date,
            'user_id' => $manual_journal->user_id,
            'note' => $manual_journal->note,
            'credit' => $manual_journal->open_balance,
            'ins' => $manual_journal->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $manual_journal->id,
            'user_type' => 'supplier',
            'is_primary' => 1,
            'supplier_id' => $manual_journal->supplier_id,
            'man_journal_id' => $manual_journal->id,
        ];
        Transaction::create($cr_data);

        // debit Retained Earning (Equity)
        unset($cr_data['credit'], $cr_data['is_primary']);
        $account = Account::where('system', 'retained_earning')->first(['id']);
        $dr_data = array_replace($cr_data, ['account_id' => $account->id, 'debit' => $manual_journal->open_balance]);
        Transaction::create($dr_data);
        aggregate_account_transactions();
    }

    /**
     * Post Bill Payment
     * @param object $bill_payment
     */
    public function post_bill_payment($bill_payment)
    {   
        // default liability accounts
        $account = Account::where('system', 'payable')->first(['id']);
        if ($bill_payment->employee_id) $account = Account::where('system', 'adv_salary')->first(['id']);
            
        $tr_category = Transactioncategory::where('code', 'pmt')->first(['id', 'code']);
        $dr_data = [
            'tid' => Transaction::max('tid')+1,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'debit' => $bill_payment->amount,
            'tr_date' => $bill_payment->date,
            'due_date' => $bill_payment->date,
            'user_id' => $bill_payment->user_id,
            'note' => ($bill_payment->note ?: "{$bill_payment->payment_mode} - {$bill_payment->reference}"),
            'ins' => $bill_payment->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $bill_payment->id,
            'user_type' => 'supplier',
            'is_primary' => 1,
            'payment_id' => $bill_payment->id,
            'supplier_id' => $bill_payment->supplier_id
        ];

        if ($bill_payment->is_advance_allocation) {
            // debit Payables (liability)
            Transaction::create($dr_data);
            
            // credit supplier advance payment 
            unset($dr_data['debit'], $dr_data['is_primary']);
            $account = Account::where('system', 'adv_pmt')->first(['id']);
            $cr_data = array_replace($dr_data, [
                'account_id' => $account->id,
                'credit' => $bill_payment->amount,
            ]);    
            Transaction::create($cr_data);
        } else {
            if ($bill_payment->payment_type == 'advance_payment') {
                // debit supplier advance payment 
                $account = Account::where('system', 'adv_pmt')->first(['id']);
                $dr_data['account_id'] = $account->id;
                Transaction::create($dr_data);
                
                // credit bank
                unset($dr_data['debit'], $dr_data['is_primary']);
                $cr_data = array_replace($dr_data, [
                    'account_id' => $bill_payment->account_id,
                    'credit' => $bill_payment->amount,
                ]);    
                Transaction::create($cr_data);
            } else {
                // debit Payables (liability)
                Transaction::create($dr_data);
                            
                // credit bank
                unset($dr_data['debit'], $dr_data['is_primary']);
                $cr_data = array_replace($dr_data, [
                    'account_id' => $bill_payment->account_id,
                    'credit' => $bill_payment->amount,
                ]);    
                Transaction::create($cr_data);
            }
        }
        aggregate_account_transactions();
    }

    /**
     * Direct Purchase Expense
     * @param Purchase $purchase
     */
    public function post_purchase_expense($purchase) 
    {
        // credit Accounts Payable (Creditors)
        $account = Account::where('system', 'payable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'bill')->first(['id', 'code']);
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $purchase->grandttl,
            'tr_date' => $purchase->date,
            'due_date' => $purchase->due_date,
            'user_id' => $purchase->user_id,
            'note' => $purchase->note,
            'ins' => $purchase->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $purchase->bill_id,
            'user_type' => 'supplier',
            'is_primary' => 1,
            'bill_id' => $purchase->bill_id,
            'supplier_id' => $purchase->supplier_id
        ];
        Transaction::create($cr_data);

        $dr_data = [];
        unset($cr_data['credit'], $cr_data['is_primary']);

        // debit Stock
        $wip_account = Account::where('system', 'wip')->first(['id']);
        $stock_exists = $purchase->items()->where('type', 'Stock')->count();
        if ($stock_exists) {
            // if project stock, WIP account else Stock account
            $is_project_stock = $purchase->items()->where('type', 'Stock')->where('itemproject_id', '>', 0)->count();
            if ($is_project_stock) {
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $wip_account->id,
                    'debit' => $purchase['stock_subttl'],
                ]);    
            } else {
                $account = Account::where('system', 'stock')->first(['id']);
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $account->id,
                    'debit' => $purchase['stock_subttl'],
                ]);    
            }
        }

        // debit Expense and Asset account
        foreach ($purchase->items as $item) {
            $subttl = $item['amount'] - $item['taxrate'];
            // debit Expense 
            if ($item['type'] == 'Expense') {
                $account_id = $item['item_id'];
                // if project expense, use WIP account
                if ($item['itemproject_id']) 
                    $account_id = $wip_account->id;
                    
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $account_id,
                    'debit' => $subttl,
                ]);
            }
            //  debit Asset 
            if ($item['type'] == 'Asset') {
                $account_id = Assetequipment::find($item['item_id'])->account_id;
                // if project asset, use WIP account
                if ($item['itemproject_id']) 
                    $account_id = $wip_account->id;
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $account_id,
                    'debit' => $subttl,
                ]);
            }
        }

        // debit tax (VAT)
        if ($purchase['grandtax'] > 0) {
            $account = Account::where('system', 'tax')->first(['id']);
            $dr_data[] = array_replace($cr_data, [
                'account_id' => $account->id, 
                'debit' => $purchase['grandtax'],
            ]);
        }
        Transaction::insert($dr_data); 
        aggregate_account_transactions();
    }

    /**
     * Goods Received Note Bill
     * @param UtilityBill $purchase
     */
    public function post_grn_bill($utility_bill)
    {
        // debit Uninvoiced Goods Received Note (liability)
        $account = Account::where('system', 'grn')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'bill')->first(['id', 'code']);
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
        $dr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'debit' => $utility_bill->subtotal,
            'tr_date' => $utility_bill->date,
            'due_date' => $utility_bill->due_date,
            'user_id' => $utility_bill->user_id,
            'note' => $utility_bill->note,
            'ins' => $utility_bill->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $utility_bill->id,
            'user_type' => 'supplier',
            'is_primary' => 1,
            'supplier_id' => $utility_bill->supplier_id,
            'bill_id' => $utility_bill->id
        ];
        Transaction::create($dr_data);

        // debit TAX
        unset($dr_data['debit'], $dr_data['is_primary']);
        if ($utility_bill->tax > 0) {
            $account = Account::where('system', 'tax')->first(['id']);
            $cr_data = array_replace($dr_data, [
                'account_id' => $account->id,
                'debit' => $utility_bill->tax,
            ]);
            Transaction::create($cr_data);
        }

        // credit Accounts Payable (creditors)
        $account = Account::where('system', 'payable')->first(['id']);
        $cr_data = array_replace($dr_data, [
            'account_id' => $account->id,
            'credit' => $utility_bill->total,
        ]);    
        Transaction::create($cr_data);
        aggregate_account_transactions();
    }  

    /**
     * Manual General Journal
     * @param Journal $journal
     */
    public function post_gen_journal($journal)
    {
        $tr_category = Transactioncategory::where('code', 'genjr')->first(['id', 'code']);
        $data = [
            'tid' => Transaction::max('tid')+1,
            'trans_category_id' => $tr_category->id,
            'tr_date' => $journal->date,
            'due_date' => $journal->date,
            'user_id' => $journal->user_id,
            'ins' => $journal->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $journal->id,
            'user_type' => 'company',
            'is_primary' => 1,
            'note' => $journal->note,
            'man_journal_id' => $journal->id,
        ];

        $tr_data = [];
        foreach ($journal->items as $item) {
            $i = count($tr_data) - 1;
            if (isset($tr_data[$i])) {
                if ($tr_data[$i]['is_primary'])
                    $tr_data[$i]['is_primary'] = 0;
            }
            if ($item->debit > 0) {
                $tr_data[] = array_replace($data, [
                    'account_id' => $item->account_id,
                    'debit' => $item->debit,
                    'credit' => 0
                ]);
            } elseif ($item->credit > 0) {
                $tr_data[] = array_replace($data, [
                    'account_id' => $item->account_id,
                    'credit' => $item->credit,
                    'debit' => 0
                ]);
            }
        }
        Transaction::insert($tr_data);
        aggregate_account_transactions();    
    }

    /**
     * Account Charges
     * @param Charge $charge
     */
    public function post_account_charge($charge)
    {
        // credit bank
        $tr_category = Transactioncategory::where('code', 'chrg')->first(['id', 'code']);
        $cr_data = [
            'tid' => Transaction::max('tid')+1,
            'account_id' => $charge->bank_id,
            'trans_category_id' => $tr_category->id,
            'credit' => $charge->amount,
            'tr_date' => $charge->date,
            'due_date' => $charge->date,
            'user_id' => $charge->user_id,
            'ins' => $charge->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $charge->id,
            'user_type' => 'customer',
            'is_primary' => 1,
            'note' => $charge->note,
            'charge_id' => $charge->id
        ];
        Transaction::create($cr_data);

        // debit expense account (bank charge)
        unset($cr_data['credit'], $cr_data['is_primary']);
        $dr_data = array_replace($cr_data, [
            'account_id' => $charge['expense_id'],
            'debit' => $charge['amount'],
        ]);
        Transaction::create($dr_data);
        aggregate_account_transactions();
    }

    /**
     * Bank Transfer
     * @param Bank $bank
     */
    public function post_bank_transfer($bank_transfer)
    {
        $tr_category = Transactioncategory::where('code', 'xfer')->first(['id', 'code']);
        $tr_data = [
            'tid' => Transaction::max('tid')+1,
            'trans_category_id' => $tr_category->id,
            'tr_date' => $bank_transfer->transaction_date,
            'due_date' => $bank_transfer->transaction_date,
            'user_id' => $bank_transfer->user_id,
            'note' => $bank_transfer->note,
            'ins' => $bank_transfer->ins,
            'tr_type' => $tr_category->code,
            'user_type' => 'employee',
            'bank_transfer_id' => $bank_transfer->id,
        ];

        // debit Asset Account (Recipient)
        $dr_data = array_replace($tr_data, [
            'account_id' => $bank_transfer->debit_account_id ,
            'debit' => $bank_transfer->amount,
            'is_primary' => 1,
        ]);
        Transaction::create($dr_data);

        // credit Asset Account (Source)
        unset($tr_data['is_primary']);
        $cr_data = array_replace($tr_data, [
            'account_id' => $bank_transfer->account_id,
            'credit' => $bank_transfer->amount,
        ]);
        Transaction::create($cr_data);
        aggregate_account_transactions();
    }

    /**
     * Opening Product Stock Balance
     * @param OpeningStock $opening_stock
     */
    public function post_opening_stock(OpeningStock $opening_stock)
    {
        // debit Inventory Account
        $account = Account::where('system', 'stock')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'stock')->first(['id', 'code']);
        $dr_data = [
            'tid' => Transaction::max('tid')+1,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'debit' => $opening_stock->total,
            'tr_date' => $opening_stock->date,
            'due_date' => $opening_stock->date,
            'user_id' => $opening_stock->user_id,
            'note' => $opening_stock->note,
            'ins' => $opening_stock->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $opening_stock->id,
            'user_type' => 'company',
            'is_primary' => 1,
            'opening_stock_id' => $opening_stock->id
        ];
        Transaction::create($dr_data);

        // credit Retained Earnings
        unset($dr_data['debit'], $dr_data['is_primary']);
        $account = Account::where('system', 'retained_earning')->first(['id']);
        $cr_data = array_replace($dr_data, [
            'account_id' => $account->id,
            'credit' => $opening_stock->total,
        ]);
        Transaction::create($cr_data);
        aggregate_account_transactions();
    }

    /**
     * Goods Received Without Invoice
     * @param GoodsReceivedNote $grn
     */
    public function post_uninvoiced_grn($grn)
    {
        // credit Uninvoiced Goods Received Note (liability)
        $account = Account::where('system', 'grn')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'grn')->first(['id', 'code']);
        $cr_data = [
            'tid' => Transaction::max('tid')+1,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $grn->subtotal,
            'tr_date' => $grn->date,
            'due_date' => $grn->date,
            'user_id' => $grn->user_id,
            'note' => $grn->note,
            'ins' => $grn->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $grn->id,
            'user_type' => 'supplier',
            'is_primary' => 1,
            'supplier_id' => $grn->supplier,
            'grn_id' => $grn->id
        ];
        Transaction::create($cr_data);

        // debit Inventory (Stock) Account
        unset($cr_data['credit'], $cr_data['is_primary']);
        $account = Account::where('system', 'stock')->first(['id']);
        $dr_data = array_replace($cr_data, [
            'account_id' => $account->id,
            'debit' => $grn->subtotal,
        ]);    
        Transaction::create($dr_data);
        aggregate_account_transactions();
    }

    /**
     * Goods Received With Invoice
     * @param UtilityBill $utility_bill
     */
    public function post_invoiced_grn_bill($utility_bill)
    {
        // debit Inventory Account
        $account = Account::where('system', 'stock')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'bill')->first(['id', 'code']);
        $dr_data = [
            'tid' => Transaction::max('tid')+1,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'debit' => $utility_bill->subtotal,
            'tr_date' => $utility_bill->date,
            'due_date' => $utility_bill->due_date,
            'user_id' => $utility_bill->user_id,
            'note' => $utility_bill->note,
            'ins' => $utility_bill->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $utility_bill->id,
            'user_type' => 'supplier',
            'is_primary' => 1,
            'supplier_id' => $utility_bill->supplier_id,
            'bill_id' => $utility_bill->id,
        ];
        Transaction::create($dr_data);

        // debit TAX
        if ($utility_bill->tax > 0) {
            $account = Account::where('system', 'tax')->first(['id']);
            $cr_data = array_replace($dr_data, [
                'account_id' => $account->id,
                'debit' => $utility_bill->tax,
            ]);
            Transaction::create($cr_data);
        }

        // credit Accounts Payable (creditors)
        unset($dr_data['debit'], $dr_data['is_primary']);
        $account = Account::where('system', 'payable')->first(['id']);
        $cr_data = array_replace($dr_data, [
            'account_id' => $account->id,
            'credit' => $utility_bill->total,
        ]);    
        Transaction::create($cr_data);
        aggregate_account_transactions();
    }

    /**
     * Project Stock Issuance
     * @param ProjectStock $projectstock
     */
    public function post_projectstock_issuance($projectstock)
    {
        // credit Inventory (stock) Account
        $account = Account::where('system', 'stock')->first('id');
        $tr_category = Transactioncategory::where('code', 'stock')->first(['id', 'code']);
        $cr_data = [
            'tid' => Transaction::max('tid')+1,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $projectstock->total,
            'tr_date' => $projectstock->date,
            'due_date' => $projectstock->date,
            'note' => $projectstock->note,
            'user_id' => $projectstock->user_id,
            'ins' => $projectstock->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $projectstock->id,
            'user_type' => 'customer',
            'is_primary' => 1,
            'customer_id' => @$projectstock->quote->customer_id,
            'projectstock_issuance_id' => $projectstock->id
        ];
        Transaction::create($cr_data);

        // debit WIP Account
        unset($cr_data['credit'], $cr_data['is_primary']);
        $account = Account::where('system', 'wip')->first('id');
        $dr_data = array_replace($cr_data, [
            'account_id' =>  $account->id,
            'debit' => $projectstock['total'],
        ]);
        Transaction::create($dr_data);
        aggregate_account_transactions();
    }

    /**
     * Loan Issuance
     * @param Loan $lost
     */
    public function post_loan_issuance($loan)
    {
        // credit lender account (bank)
        $tr_category = Transactioncategory::where('code', 'loan')->first(['id', 'code']);
        $cr_data = [
            'tid' => Transaction::max('tid')+1,
            'account_id' => $loan->lender_id,
            'trans_category_id' => $tr_category->id,
            'credit' => $loan->amount,
            'tr_date' => $loan->approval_date,
            'due_date' => $loan->approval_date,
            'user_id' => $loan->user_id,
            'ins' => $loan->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $loan->id,
            'user_type' => 'employee',
            'is_primary' => 1,
            'note' => $loan->note,
            'loan_id' => $loan->id,
        ];
        Transaction::create($cr_data);

        unset($cr_data['credit'], $cr_data['is_primary']);
        if ($loan->employee) {
            // debit Loan Receivable
            $account = Account::where('system', 'loan_receivable')->first();
            $dr_data = array_replace($cr_data, [
                'account_id' =>  $account->id,
                'debit' => $loan->amount,
            ]);
            Transaction::create($dr_data);
        } else {
            // business loan
        }
        aggregate_account_transactions();    
    }
}