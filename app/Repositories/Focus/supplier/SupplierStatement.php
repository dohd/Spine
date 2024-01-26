<?php

namespace App\Repositories\Focus\supplier;

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
        $q = Transaction::whereHas('account', function ($q) { 
            $q->where('system', 'payable');  
        })
        ->where(function ($q) use($params) {
            $q->whereHas('bill', fn($q) => $q->where($params))
            ->orWhereHas('bill_payment', fn($q) => $q->where($params));
        })
        ->orWhere(function($q) use($params) {
            $q->whereHas('manualjournal', fn($q) => $q->where($params))
            ->where('credit', '>', 0);     
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