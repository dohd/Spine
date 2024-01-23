<?php

namespace App\Repositories\Focus\customer;

use App\Models\invoice\Invoice;
use App\Models\transaction\Transaction;

trait CustomerStatement
{
    /**
     * Statement on account transactions
     */
    public function getTransactionsForDataTable($customer_id = 0)
    {            
        $params = ['customer_id' => request('customer_id', $customer_id)];
        
        $q = Transaction::whereHas('account', function ($q) { 
            $q->where('system', 'receivable');  
        })
        ->where(function ($q) use($params) {
            $q->where(function($q) use($params) {
                $q->where('tr_type', 'inv')->where('debit', '>', 0)
                ->where(function($q) use($params) {
                    $q->where($params)
                    ->orWhere(function($q) use($params) {
                        $q->whereIn('tr_ref', function($q) use($params) {
                            $q->select('id')->from('invoices')->where($params);
                        });
                    });
                });
            })
            ->orwhere(function($q) use($params) {
                $q->where('tr_type', 'dep')->where('credit', '>', 0)
                ->where(function($q) use($params) {
                    $q->where($params)
                    ->orWhere(function($q) use($params) {
                        $q->whereIn('tr_ref', function($q) use($params) {
                            $q->select('id')->from('paid_invoices')->where($params);
                        });
                    });
                });
            })
            ->orwhere(function($q) use($params) {
                $q->where('tr_type', 'withholding')->where('credit', '>', 0)
                ->where(function($q) use($params) {
                    $q->where($params)
                    ->orWhere(function($q) use($params) {
                        $q->whereIn('tr_ref', function($q) use($params) {
                            $q->select('id')->from('withholdings')->where($params);
                        });
                    });
                });
            })
            ->orwhere(function($q) use($params) {
                $q->where('tr_type', 'cnote')->where('credit', '>', 0)
                ->where(function($q) use($params) {
                    $q->where($params)
                    ->orWhere(function($q) use($params) {
                        $q->whereIn('tr_ref', function($q) use($params) {
                            $q->select('id')->from('credit_notes')->where($params);
                        });
                    });
                });
            });
        })
        ->orWhere(function ($q) use($params) {
            $q->where('tr_type', 'genjr')->where('debit', '>', 0)
            ->where('user_type', 'customer')
            ->where('note', 'LIKE', "%{$params['customer_id']}-customer%")
            ->where(function($q) use($params) {
                $q->where($params)->orWhere('customer_id', null);
            });
        });       
        
        // on date filter
        if (request('start_date') && request('is_transaction')) {
            $from = date_for_database(request('start_date'));
            $tr_ids = $q->pluck('id')->toArray();
            
            $params = ['id', 'tr_date', 'tr_type', 'note', 'debit', 'credit'];
            $transactions = Transaction::whereIn('id', $tr_ids)->whereBetween('tr_date', [$from, date('Y-m-d')])->get($params);
            // compute balance brought foward as of start date
            $bf_transactions = Transaction::whereIn('id', $tr_ids)->where('tr_date', '<', $from)->get($params);
            $debit_balance = $bf_transactions->sum('debit') - $bf_transactions->sum('credit');
            if ($debit_balance) {
                $record = (object) array(
                    'id' => 0,
                    'tr_date' => date('Y-m-d', strtotime($from . ' - 1 day')),
                    'tr_type' => 'balance',
                    'note' => '** Balance Brought Foward ** ',
                    'debit' => $debit_balance > 0 ? $debit_balance : 0,
                    'credit' => $debit_balance < 0 ? ($debit_balance * -1) : 0,
                );
                // merge brought foward balance with the rest of the transactions
                $transactions = collect([$record])->merge($transactions);
            }
            return $transactions;
        }
        return $q->get();
    }

    /**
     * Statement on invoice records
     */
    public function getStatementForDataTable($customer_id = 0)
    {
        $q = Invoice::where('customer_id', request('customer_id', $customer_id));
        
        $q->with(['payments', 'withholding_payments', 'creditnotes', 'debitnotes']);
        
        return $this->generate_statement($q->get());
    }

    // generate statement
    public function generate_statement($invoices = [])
    {
        $i = 0;
        $statement = collect();
        foreach ($invoices as $invoice) {
            $i++;
            $invoice_id = $invoice->id;
            $tid = gen4tid('Inv-', $invoice->tid);
            $note = $invoice->notes;
            $inv_record = (object) array(
                'id' => $i,
                'date' => $invoice->invoicedate,
                'type' => 'invoice',
                'note' => '(' . $tid . ')' . ' ' . $note,
                'debit' => $invoice->total,
                'credit' => 0,
                'invoice_id' => $invoice_id
            );

            $payments = collect();
            foreach ($invoice->payments as $pmt) {
                if (!$pmt->paid_invoice) continue;
                $i++;
                $reference = $pmt->paid_invoice->reference;
                $mode = $pmt->paid_invoice->payment_mode;
                $pmt_tid = gen4tid('pmt-', $pmt->paid_invoice->tid);
                $account = $pmt->paid_invoice->account->holder;
                $amount = $pmt->paid_invoice->amount;
                $record = (object) array(
                    'id' => $i,
                    'date' => $pmt->paid_invoice->date,
                    'type' => 'payment',
                    'note' => '(' . $tid . ')' . ' ' . $pmt_tid . ' ' . ' reference: ' . $reference . ' mode: ' 
                        . ucfirst($mode) . ', account: ' . $account . ', amount: ' . numberFormat($amount),
                    'debit' => 0,
                    'credit' => $pmt->paid,
                    'invoice_id' => $invoice_id,
                    'payment_item_id' => $pmt->id
                );
                $payments->add($record);
            }    

            $withholdings = collect();
            foreach ($invoice->withholding_payments as $pmt) {
                $i++;
                $reference = @$pmt->withholding->reference;
                $certificate = @$pmt->withholding->certificate;
                $note = @$pmt->withholding->note;
                $date = @$pmt->withholding->date;
                $record = (object) array(
                    'id' => $i,
                    'date' => $date,
                    'type' => 'withholding',
                    'note' => "({$tid}) {$reference} - {$certificate} - {$note}",
                    'debit' => 0,
                    'credit' => $pmt->paid,
                    'invoice_id' => $invoice_id,
                    'withholding_item_id' => $pmt->id 
                );
                $withholdings->add($record);
            }  

            $creditnotes = collect();
            foreach ($invoice->creditnotes as $cnote) {
                $i++;
                $record = (object) array(
                    'id' => $i,
                    'date' => $cnote->date,
                    'type' => 'credit-note',
                    'note' => '(' . $tid . ')' . ' ' . $cnote->note,
                    'debit' => 0,
                    'credit' => $cnote->total,
                    'invoice_id' => $invoice_id,
                    'creditnote_id' => $cnote->id
                );
                $creditnotes->add($record);
            }   

            $debitnotes = collect();
            foreach ($invoice->debitnotes as $dnote) {
                $i++;
                $record = (object) array(
                    'id' => $i,
                    'date' => $dnote->date,
                    'type' => 'debit-note',
                    'note' => '(' . $tid . ')' . ' ' . $dnote->note,
                    'dedit' => $dnote->total,
                    'credit' => 0,
                    'invoice_id' => $invoice_id,
                    'debitnote_id' => $dnote->id
                );
                $debitnotes->add($record);
            }   

            $statement->add($inv_record);
            $statement = $statement->merge($payments);
            $statement = $statement->merge($creditnotes);
            $statement = $statement->merge($withholdings);
        }

        return $statement;        
    }
}