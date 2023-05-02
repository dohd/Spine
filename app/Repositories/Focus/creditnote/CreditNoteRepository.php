<?php

namespace App\Repositories\Focus\creditnote;

use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Repositories\BaseRepository;
use App\Models\creditnote\CreditNote;
use App\Models\invoice\Invoice;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use Illuminate\Support\Facades\DB;

/**
 * Class PurchaseorderRepository.
 */
class CreditNoteRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = CreditNote::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = CreditNote::query();
        $q->where('is_debit', request('is_debit', 0));

        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if ($key == 'date') $input[$key] = date_for_database($val);
            if (in_array($key, ['subtotal', 'tax', 'total'], 1)) 
                $input[$key] = numberClean($val);
        }
        $result = CreditNote::create($input);

        $invoice = $result->invoice;
        // if debit note, reduce invoice amount
        if ($result->is_debit) $invoice->decrement('amountpaid', $result->total);
        else $invoice->increment('amountpaid', $result->total);

        // update invoicce status
        if ($invoice->amountpaid == 0) $invoice->update(['status' => 'due']);
        elseif (round($invoice->total) == round($invoice->amountpaid)) $invoice->update(['status' => 'paid']);
        elseif (round($invoice->total) > round($invoice->amountpaid)) $invoice->update(['status' => 'partial']);


        /** accounts  */
        $this->post_transaction($result);
        
        DB::commit();
        if ($result) return $result;

        throw new GeneralException(trans('exceptions.backend.purchaseorders.create_error'));
    }

    // 
    public function update($creditnote, array $input)
    {
        // dd($input, $creditnote->id);
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if ($key == 'date') $input[$key] = date_for_database($val);
            if (in_array($key, ['subtotal', 'tax', 'total'], 1)) 
                $input[$key] = numberClean($val);
        }
        // decrement or increment invoice amount paid and update status
        $invoice = $creditnote->invoice;
        if (!$creditnote->is_debit) {
            // credit note
            if ($creditnote->total > $input['total']) {
                $diff = $creditnote->total - $input['total'];
                $invoice->increment('amountpaid', $diff);
            } elseif ($creditnote->total < $input['total']) {
                $diff = $input['total'] - $creditnote->total;
                $invoice->decrement('amountpaid', $diff);
            }
        } else {
            // debit note
            if ($creditnote->total > $input['total']) {
                $diff = $creditnote->total - $input['total'];
                $invoice->decrement('amountpaid', $diff);
            } elseif ($creditnote->total < $input['total']) {
                $diff = $input['total'] - $creditnote->total;
                $invoice->increment('amountpaid', $diff);
            }
        }
        // update invoice status
        if ($invoice->amountpaid == 0) $invoice->update(['status' => 'due']);
        elseif (round($invoice->total) == round($invoice->amountpaid)) $invoice->update(['status' => 'paid']);
        elseif (round($invoice->total) > round($invoice->amountpaid)) $invoice->update(['status' => 'partial']);
        
        $result = $creditnote->update($input);

        /** accounts  */
        if ($creditnote->is_debit) $creditnote->debitnote_transactions()->delete();
        else $creditnote->creditnote_transactions()->delete();
        $this->post_transaction($creditnote);        

        DB::commit();
        if ($result) return $result;

        throw new GeneralException(trans('exceptions.backend.purchaseorders.create_error'));
    }

    // transactions
    public function post_transaction($result)
    {
        $account = Account::where('system', 'receivable')->first(['id']);
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
        $data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'tr_ref' => $result->id,
            'tr_date' => date('Y-m-d'),
            'due_date' => $result->date,
            'user_id' => $result->user_id,
            'note' => $result->note,
            'ins' => $result->ins,
            'user_type' => 'customer',
            'is_primary' => 0,
        ];

        $tr_data = array();
        // if debitnote, else creditnote
        if ($result->is_debit) {
            // debit Receivable Account (Creditors)
            $tr_category = Transactioncategory::where('code', 'dnote')->first(['id', 'code']);
            $data = $data + ['trans_category_id' => $tr_category->id, 'tr_type' => $tr_category->code];
            $tr_data[] = array_replace($data, [
                'debit' => $result->total,
                'is_primary' => 1,
            ]);

            // credit Customer Income (intermediary ledger account)
            // $account = Account::where('system', 'client_income')->first(['id']);
            // $tr_data[] = array_replace($data, [
            //     'account_id' => $account->id,
            //     'credit' => $result->subtotal,
            // ]);

            // credit Revenue Account (Income)
            $tr_data[] = array_replace($data, [
                'account_id' => Invoice::find($result->invoice_id)->account_id,
                'credit' => $result->subtotal,
            ]);

            // credit tax (VAT)
            $account = Account::where('system', 'tax')->first(['id']);
            $tr_data[] = array_replace($data, [
                'account_id' => $account->id,
                'credit' => $result->tax,
            ]);
        } else {
            // credit Receivable Account (Debtors)
            $tr_category = Transactioncategory::where('code', 'cnote')->first(['id', 'code']);
            $data = $data + ['trans_category_id' => $tr_category->id, 'tr_type' => $tr_category->code];
            $tr_data[] = array_replace($data, [
                'credit' => $result->total,
                'is_primary' => 1 
            ]);

            // debit Customer Income (intermediary ledger account)
            // $account = Account::where('system', 'client_income')->first(['id']);
            // $tr_data[] = array_replace($data, [
            //     'account_id' => $account->id,
            //     'debit' => $result->subtotal,
            // ]);

            // debit Revenue Account (Income)
            $tr_data[] = array_replace($data, [
                'account_id' => Invoice::find($result->invoice_id)->account_id,
                'debit' => $result->subtotal,
            ]);

            // debit tax (VAT)
            $account = Account::where('system', 'tax')->first(['id']);
            $tr_data[] = array_replace($data, [
                'account_id' => $account->id,
                'debit' => $result->tax,
            ]);
        }
        foreach ($tr_data as $i => $tr) {
            if (isset($tr['credit']) && $tr['credit'] > 0) $tr['debit'] = 0;
            if (isset($tr['debit']) && $tr['debit'] > 0) $tr['credit'] = 0;
            $tr_data[$i] = $tr;
        }
        Transaction::insert($tr_data);
        aggregate_account_transactions();
    }

    /**
     * For deleting the respective model from storage
     *
     * @param CreditNote $creditnote
     * @throws GeneralException
     * @return bool
     */
    public function delete($creditnote)
    {
        DB::beginTransaction();

        // update invoice amountpaid
        $invoice = $creditnote->invoice;
        if ($creditnote->is_debit) $invoice->increment('amountpaid', $creditnote->total);
        else $invoice->decrement('amountpaid', $creditnote->total);
        // update invoicce status
        if ($invoice->amountpaid == 0) $invoice->update(['status' => 'due']);
        elseif (round($invoice->total) == round($invoice->amountpaid)) $invoice->update(['status' => 'paid']);    
        elseif (round($invoice->total) > round($invoice->amountpaid)) $invoice->update(['status' => 'partial']);
        
        // delete respective transactions
        if ($creditnote->is_debit) $creditnote->debitnote_transactions()->delete();
        else $creditnote->creditnote_transactions()->delete();
        aggregate_account_transactions();
        
        $result = $creditnote->delete();
        
        DB::commit();
        if ($result) return true;
    }    
}