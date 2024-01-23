<?php

namespace App\Repositories\Focus\supplier;

use App\Models\billpayment\Billpayment;
use App\Models\supplier\Supplier;
use App\Models\transaction\Transaction;
use App\Models\utility_bill\UtilityBill;

trait SupplierStatement
{
    public function getBillsForDataTable($supplier_id = 0)
    {
        return UtilityBill::where('supplier_id', request('supplier_id', $supplier_id))->get();
    }

    public function getTransactionsForDataTable($supplier_id = 0)
    {
        $params = ['supplier_id' => request('supplier_id', $supplier_id)];
        $supplier = Supplier::find(request('supplier_id'), ['id', 'open_balance_note']);

        $transactions = collect();
        // bills
        $bills = UtilityBill::where($params)->get();
        foreach ($bills as $i => $bill) {
            // skip opening balance bill
            if ($bill->tid == 0) continue;

            $tid = gen4tid('BILL-', $bill->tid);
            $transactions->add((object) [
                'id' => $i+1,
                'tr_date' => $bill->date,
                'tr_type' => 'bill',
                'note' => "({$tid}) " . $bill->note . " ({$bill->reference_type}-{$bill->reference})",
                'debit' => 0,
                'credit' => $bill->total,
            ]);
        }
        // bill payments
        $bill_payments = Billpayment::where($params)->get();
        $j = $transactions->last()? $transactions->last()->id : 0;
        foreach ($bill_payments as $pmt) {
            $j++;
            $tid = gen4tid('PMT-', $pmt->tid);
            $transactions->add((object) [
                'id' => $j,
                'tr_date' => $pmt->date,
                'tr_type' => 'pmt',
                'note' => "({$tid}) " . $pmt->note . " ({$pmt->payment_mode}-{$pmt->reference})",
                'debit' => $pmt->amount,
                'credit' => 0,
            ]);
        }
        // opening balance
        $note = "%{$supplier->id}-supplier Account Opening Balance {$supplier->open_balance_note}%";
        $open_balance_tr = Transaction::where('tr_type', 'genjr')->where('credit', '>', 0)
            ->where('note', 'LIKE', $note)->first();
        if ($open_balance_tr) {
            $i = $transactions->last()? $transactions->last()->id : 0;
            $transactions->add((object) [
                'id' => $i+1,
                'tr_date' => $open_balance_tr->tr_date,
                'tr_type' => $open_balance_tr->tr_type,
                'note' => $open_balance_tr->note,
                'debit' => $open_balance_tr->debit,
                'credit' => $open_balance_tr->credit,
            ]);
        }

        // add balance brought foward logic on datefilter
        // 

        return $transactions;


        /**
        $q = Transaction::whereHas('account', function ($q) { 
            $q->where('system', 'payable');  
        })->where(function ($q) use($params) {
            $q->where('tr_type', 'pmt')->where(function ($q) use($params) {
                $q->whereHas('bill_payment', function ($q) use($params) {
                    $q->where($params);
                });
            })
            ->orwhere('tr_type', 'bill')->where(function ($q) use($params) {
                $q->where('credit', '>', 0)->where(function  ($q) use($params) {
                    $q->whereHas('direct_purchase_bill', function ($p) use($params) {
                        $p->where($params);
                    })
                    ->orwhereHas('grn_bill', function ($q) use($params) {
                        $q->where($params);
                    })
                    ->orWhereHas('grn_invoice_bill', function ($q) use($params) {
                        $q->where($params);
                    });   
                });  
            });                
        })->orwhere(function ($q) use($supplier) {
            // opening balance
            $note = "%{$supplier->id}-supplier Account Opening Balance {$supplier->open_balance_note}%";
            $q->where('tr_type', 'genjr')->where('credit', '>', 0)->where('note', 'LIKE', $note);
        });

        // on date filter
        if (request('start_date') && request('is_transaction')) {
            $from = date_for_database(request('start_date'));
            $tr_ids = $q->pluck('id')->toArray();
            
            $params = ['id', 'tr_date', 'tr_type', 'note', 'debit', 'credit'];
            $transactions = Transaction::whereIn('id', $tr_ids)->whereBetween('tr_date', [$from, date('Y-m-d')])->get($params);
            // compute balance brought foward as of start date
            $bf_transactions = Transaction::whereIn('id', $tr_ids)->where('tr_date', '<', $from)->get($params);
            $credit_balance = $bf_transactions->sum('credit') - $bf_transactions->sum('debit');
            if ($credit_balance) {
                $record = (object) array(
                    'id' => 0,
                    'tr_date' => date('Y-m-d', strtotime($from . ' - 1 day')),
                    'tr_type' => 'balance',
                    'note' => '** Balance Brought Foward ** ',
                    'debit' => $credit_balance < 0 ? ($credit_balance * -1) : 0,
                    'credit' => $credit_balance > 0 ? $credit_balance : 0,
                );
                // merge brought foward balance with the rest of the transactions
                $transactions = collect([$record])->merge($transactions);
            }

            return $transactions;
        }

        return $q->get(); 
        **/
    }

    public function getStatementForDataTable($supplier_id = 0)
    {
        $q = UtilityBill::where('supplier_id', request('supplier_id', $supplier_id))->with('payments');
        $bills = $q->get();

        $i = 0;
        $statement = collect();
        foreach ($bills as $bill) {
            $i++;
            $bill_id = $bill->id;
            $tid = gen4tid('BILL-', $bill->tid);
            $bill_record = (object) array(
                'id' => $i,
                'date' => $bill->date,
                'type' => 'bill',
                'note' => "({$tid}) {$bill->note}",
                'debit' => 0,
                'credit' => $bill->total,
                'bill_id' => $bill_id
            );

            $payments = collect();
            foreach ($bill->payments as $pmt) {
                if (!$pmt->bill_payment) continue;
                $i++;
                $reference = $pmt->bill_payment->reference;
                $pmt_tid = gen4tid('PMT-', $pmt->bill_payment->tid);
                $account = $pmt->bill_payment->account? $pmt->bill_payment->account->holder : '';
                $amount = numberFormat($pmt->bill_payment->amount);
                $payment_mode = ucfirst($pmt->bill_payment->payment_mode);
                $record = (object) array(
                    'id' => $i,
                    'date' => $pmt->bill->date,
                    'type' => 'payment',
                    'note' => "({$tid}) {$pmt_tid} reference: {$reference} mode: {$payment_mode} account: {$account} amount: {$amount}",
                    'debit' => $pmt->paid,
                    'credit' => 0,
                    'bill_id' => $bill_id,
                    'payment_item_id' => $pmt->id
                );
                $payments->add($record);
            }   
            $statement->add($bill_record);
            $statement = $statement->merge($payments);
        }

        return $statement;     
    }
}