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
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if ($key == 'date') $input[$key] = date_for_database($val);
            if (in_array($key, ['subtotal', 'tax', 'total'], 1)) 
                $input[$key] = numberClean($val);
        }
        $result = CreditNote::create($input);

        // decrement or increment invoice amount paid and update status
        $invoice = $result->invoice;
        if ($result->is_debit) $invoice->decrement('amountpaid', $result->total);
        else $invoice->increment('amountpaid', $result->total);
        if ($invoice->total == $invoice->amountpaid) $invoice->update(['status' => 'paid']);
        if ($invoice->total > $invoice->amountpaid) $invoice->update(['status' => 'partial']);
        
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
        if ($creditnote->total > $input['total']) {
            $diff = $creditnote->total - $input['total'];
            $invoice->increment('amountpaid', $diff);
        } elseif ($creditnote->total < $input['total']) {
            $diff = $input['total'] - $creditnote->total;
            $invoice->decrement('amountpaid', $diff);
        }
        if ($invoice->total == $invoice->amountpaid) $invoice->update(['status' => 'paid']);
        if ($invoice->total > $invoice->amountpaid) $invoice->update(['status' => 'partial']);

        Transaction::where(['tr_ref' => $creditnote->id, 'note' => $creditnote->note])->delete();
        $result = $creditnote->update($input);

        /** accounts  */
        $this->post_transaction($creditnote);        

        DB::commit();
        if ($result) return $result;

        throw new GeneralException(trans('exceptions.backend.purchaseorders.create_error'));
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
        // 
    }

    static function post_transaction($result)
    {
        $account = Account::where('system', 'receivable')->first(['id']);
        $tid = Transaction::max('tid') + 1;
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
            'is_primary' => 1
        ];

        // credit note
        if (!$result->is_debit) {
            // credit Receivable Account (Creditors)
            $tr_category = Transactioncategory::where('code', 'cnote')->first(['id', 'code']);
            $cr_data = array_replace($data, [
                'credit' => $result->total,
                'trans_category_id' => $tr_category->id,
                'tr_type' => $tr_category->code,    
            ]);
            Transaction::create($cr_data);

            // debit Revenue Account
            unset($cr_data['credit'], $cr_data['is_primary']);
            $dr_data = array_replace($cr_data, [
                'account_id' => Invoice::find($result->invoice_id)->account_id,
                'debit' => $result->subtotal,
            ]);
            Transaction::create($dr_data);
        }        
        // debit note,
        if ($result->is_debit) {
            // credit Revenue Account
            $tr_category = Transactioncategory::where('code', 'dnote')->first(['id', 'code']);
            $cr_data = array_replace($data, [
                'account_id' => Invoice::find($result->invoice_id)->account_id,
                'debit' => $result->subtotal,
                'trans_category_id' => $tr_category->id,
                'tr_type' => $tr_category->code,    
            ]);
            Transaction::create($cr_data);

            // debit Receivable Account (Creditors)
            unset($cr_data['debit'], $cr_data['is_primary']);
            $dr_data = array_replace($cr_data, [
                'account_id' => $account->id,
                'credit' => $result->total,
            ]);
            Transaction::create($dr_data);
        } 
        // update account ledgers debit and credit totals
        aggregate_account_transactions();
    }
}