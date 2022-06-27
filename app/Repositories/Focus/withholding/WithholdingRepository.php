<?php

namespace App\Repositories\Focus\withholding;

use DB;
use App\Models\withholding\Withholding;
use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\items\WithholdingItem;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;


/**
 * Class WithholdingRepository.
 */
class WithholdingRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Withholding::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        return $this->query()->get();
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

        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date', 'due_date'], 1))
                $data[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'amount_ttl', 'deposit_ttl'], 1))
                $data[$key] = numberClean($val);
        }
        $result = Withholding::create($data);

        $data_items = $input['data_items'];
        foreach ($data_items as $k => $item) {
            $data_items[$k] = array_replace($item, [
                'withholding_id' => $result->id,
                'paid' => numberClean($item['paid'])
            ]);
        }
        WithholdingItem::insert($data_items);

        // increment invoice amount paid and update status
        foreach ($result->items as $item) {
            $invoice = $item->invoice;
            $invoice->increment('amountpaid', $item->paid);
            if ($invoice->amountpaid == 0) $invoice->update(['status' => 'due']);
            elseif ($invoice->total > $invoice->amountpaid) $invoice->update(['status' => 'partial']);
            elseif ($invoice->total == $invoice->amountpaid) $invoice->update(['status' => 'paid']);
        }

        /**accounting */
        $this->post_transaction($result);

        DB::commit();
        if ($result) return true;

        throw new GeneralException(trans('exceptions.backend.withholdings.create_error'));
    }

    // 
    public function post_transaction($result)
    {
        // credit Accounts Receivable (Debtors)
        $account = Account::where('system', 'receivable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'withholding')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $result->deposit_ttl,
            'tr_date' => $result->due_date,
            'due_date' => $result->due_date,
            'user_id' => $result->user_id,
            'note' => $result->note,
            'ins' => $result->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $result->id,
            'user_type' => 'customer',
            'is_primary' => 1,
        ];
        Transaction::create($cr_data);

        // debit Withholding
        $q = Account::query();
        $q->when($result->certificate == 'vat', function ($q) {
            $q->where('system', 'withholding_vat');
        });
        $q->when($result->certificate == 'tax', function ($q) {
            $q->where('system', 'withholding_inc');
        });
        $account = $q->first();

        unset($cr_data['credit'], $cr_data['is_primary']);
        $dr_data = array_replace($cr_data, [
            'account_id' => $account->id,
            'debit' => $result->deposit_ttl
        ]);
        Transaction::create($dr_data);
        aggregate_account_transactions();            
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Bank $bank
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($withholding, array $input)
    {
        throw new GeneralException(trans('exceptions.backend.withholdings.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Bank $withholding
     * @throws GeneralException
     * @return bool
     */
    public function delete($withholding)
    {
        DB::beginTransaction();
        
        // decrement invoice amount paid and update status
        foreach ($withholding->items as $item) {
            if ($item->invoice) {
                $invoice = $item->invoice;
                $invoice->decrement('amountpaid', $item->paid);
                if ($invoice->total == $invoice->amountpaid) $invoice->update(['status' => 'paid']);
                elseif ($invoice->total > $invoice->amountpaid) $invoice->update(['status' => 'partial']);
                elseif ($invoice->amountpaid == 0) $invoice->update(['status' => 'pending']);    
            }
        }
        $withholding->items()->delete();
        $withholding->transactions()->delete();
        aggregate_account_transactions();
        $result = $withholding->delete();
 
        DB::commit();
        if ($result) return true;

        throw new GeneralException(trans('exceptions.backend.withholdings.delete_error'));
    }
}
