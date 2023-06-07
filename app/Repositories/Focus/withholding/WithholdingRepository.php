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
use Illuminate\Validation\ValidationException;

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
            if (in_array($key, ['cert_date', 'tr_date']))
                $data[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'allocate_ttl']))
                $data[$key] = numberClean($val);
        }

        if ($data['amount'] == 0)
            throw ValidationException::withMessages(['Amount Withheld is required']);
        if ($input['data_items'] && $data['amount'] != $data['allocate_ttl']) 
            throw ValidationException::withMessages(['Total Amount Withheld must be equal to Total Amount Allocated']);
        
        $is_whtax_allocation = @$data['withholding_tax_id'];
        if ($is_whtax_allocation) {
            $wh_tax = Withholding::find($data['withholding_tax_id']);
            $wh_tax->increment('allocate_ttl', $data['allocate_ttl']);
            $diff = round($wh_tax->amount - $wh_tax->allocate_ttl);
            if ($diff < 0) throw ValidationException::withMessages(['Allocation limit reached! Please reduce allocated amount by ' . numberFormat($diff*-1)]);
            $wh_tax->customer->decrement('on_account', $data['allocate_ttl']); 

            // create withholding tax allocation
            unset($data['withholding_tax_id']);
            $result = Withholding::create($data);
        } else {
            unset($data['withholding_tax_id']);
            $result = Withholding::create($data);
            // set on account balance for withholding tax 
            if ($data['certificate'] == 'tax') $result->customer->increment('on_account', $data['amount']);  
        }
        
        // allocated items
        $data_items = $input['data_items'];
        if ($data_items) {
            $data_items = array_map(function ($v) use($result) {
                return array_replace($v, ['withholding_id' => $result->id, 'paid' => numberClean($v['paid'])]);
            }, $data_items);
            WithholdingItem::insert($data_items);

            // increment invoice amount paid and update status
            foreach ($result->items as $item) {
                $invoice = $item->invoice;
                if ($invoice) {
                    $invoice->increment('amountpaid', $item->paid);
                    if ($invoice->amountpaid == 0) $invoice->update(['status' => 'due']);
                    elseif (round($invoice->total) > round($invoice->amountpaid)) $invoice->update(['status' => 'partial']);
                    else $invoice->update(['status' => 'paid']);
                }
            }
        }
        
        /**accounting */
        if (!$is_whtax_allocation) 
            $this->post_transaction($result);
        
        if ($result) {
            DB::commit();
            return $result;
        }
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
        dd($input);
        
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

        if ($withholding->certificate == 'tax') {
            // check if is allocation
            if ($withholding->items->count()) {
                $wh_tax = Withholding::where('id', '!=', $withholding->id)
                    ->where('reference', $withholding->reference)->first();
                $wh_tax->decrement('allocate_ttl', $withholding->allocate_ttl);
                // reverse client unallocated amount state before allocation
                if ($wh_tax->customer) $wh_tax->customer->increment('on_account', $withholding->amount);
            } else {
                // check if it has allocations
                $withholdings = Withholding::where('reference', $withholding->reference)->get();
                if ($withholdings->count() > 1) throw ValidationException::withMessages(['Withholding Tax has related allocations']);
                // reverse client unallocated amount  state before withholding tax
                $withholding->customer->decrement('on_account', $withholding->amount);
            }           
        }
        
        // reverse invoice amount paid and update status
        foreach ($withholding->items as $item) {
            $invoice = $item->invoice;
            if ($invoice) {
                $invoice->decrement('amountpaid', $item->paid);
                if ($invoice->amountpaid == 0) $invoice->update(['status' => 'due']);    
                elseif (round($invoice->total) > round($invoice->amountpaid)) $invoice->update(['status' => 'partial']);
                else $invoice->update(['status' => 'paid']);
            }
        }

        // remove tramnsactions
        $withholding->transactions()->delete();
        aggregate_account_transactions();

        $withholding->items()->delete();
        if ($withholding->delete()) {
            DB::commit();
            return true;
        }
    }

    /**
     * Withholding Transaction
     */
    public function post_transaction($result)
    {
        // credit Accounts Receivable (Debtors)
        $account = Account::where('system', 'receivable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'withholding')->first(['id', 'code']);
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $result->amount,
            'tr_date' => $result->tr_date,
            'due_date' => $result->tr_date,
            'user_id' => $result->user_id,
            'note' => $result->note,
            'ins' => $result->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $result->id,
            'user_type' => 'customer',
            'is_primary' => 1,
        ];
        Transaction::create($cr_data);

        // debit Withholding Account
        $account = Account::when($result->certificate == 'vat', function ($q) {
            $q->where('system', 'withholding_vat');
        })->when($result->certificate == 'tax', function ($q) {
            $q->where('system', 'withholding_inc');
        })->first();

        unset($cr_data['credit'], $cr_data['is_primary']);
        $dr_data = array_replace($cr_data, [
            'account_id' => $account->id,
            'debit' => $result->amount
        ]);
        Transaction::create($dr_data);
        aggregate_account_transactions();            
    }
}
