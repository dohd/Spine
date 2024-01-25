<?php

namespace App\Repositories\Focus\creditnote;

use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use App\Models\creditnote\CreditNote;
use App\Models\transaction\Transaction;
use App\Repositories\Accounting;
use App\Repositories\CustomerSupplierBalance;
use Illuminate\Support\Facades\DB;

/**
 * Class PurchaseorderRepository.
 */
class CreditNoteRepository extends BaseRepository
{
    use Accounting, CustomerSupplierBalance;
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
        $q = $this->query()->where('is_debit', request('is_debit', 0));

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
        foreach ($input as $key => $val) {
            if ($key == 'date') $input[$key] = date_for_database($val);
            if (in_array($key, ['subtotal', 'tax', 'total'], 1)) 
                $input[$key] = numberClean($val);
        }

        DB::beginTransaction();
        $result = CreditNote::create($input);
        // compute invoice balance
        $this->customer_deposit_balance([$result->invoice->id]);
        /** accounts  */
        $this->post_creditnote_debitnote($result);
        
        if ($result) {
            DB::commit();
            return $result;
        }
    }

    // 
    public function update($creditnote, array $input)
    {
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if ($key == 'date') $input[$key] = date_for_database($val);
            if (in_array($key, ['subtotal', 'tax', 'total'], 1)) 
                $input[$key] = numberClean($val);
        }
        
        $result = $creditnote->update($input);

        // compute invoice balance
        $this->customer_deposit_balance([$result->invoice->id]);
        
        /** accounts  */
        Transaction::when($creditnote->is_debit, fn($q) => $q->where('dnote_id', $creditnote->id)->delete())
        ->when(!$creditnote->is_debit, fn($q) => $q->where('cnote_id', $creditnote->id)->delete());
        $this->post_creditnote_debitnote($creditnote);

        if ($result) {
            DB::commit();
            return $result;
        }
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
        $invoice_id = @$creditnote->invoice->id;

        Transaction::when($creditnote->is_debit, fn($q) => $q->where('dnote_id', $creditnote->id)->delete())
        ->when(!$creditnote->is_debit, fn($q) => $q->where('cnote_id', $creditnote->id)->delete());
        aggregate_account_transactions();
        $result = $creditnote->delete();

        // compute invoice balance
        $this->customer_deposit_balance([$invoice_id]);

        if ($result) {
            DB::commit();
            return true;
        }
    }    
}