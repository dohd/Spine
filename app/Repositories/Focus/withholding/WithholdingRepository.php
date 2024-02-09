<?php

namespace App\Repositories\Focus\withholding;

use DB;
use App\Models\withholding\Withholding;
use App\Exceptions\GeneralException;
use App\Models\items\WithholdingItem;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use App\Repositories\CustomerSupplierBalance;
use Illuminate\Validation\ValidationException;

/**
 * Class WithholdingRepository.
 */
class WithholdingRepository extends BaseRepository
{
    use CustomerSupplierBalance, Accounting;

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
        $q = $this->query();

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
        $data = $input['data'];
        $data['rel_payment_id'] = @$data['withholding_tax_id'];
        unset($data['withholding_tax_id']);
        $data_items = $input['data_items'];

        foreach ($data as $key => $val) {
            if (in_array($key, ['cert_date', 'tr_date']))
                $data[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'allocate_ttl']))
                $data[$key] = numberClean($val);
        }
        if (@$data['amount'] == 0) throw ValidationException::withMessages(['Amount Withheld is required']);
        if (@$input['data_items'] && $data['amount'] != $data['allocate_ttl']) 
            throw ValidationException::withMessages(['Total Amount Withheld must be equal to Total Amount Allocated']);
        
        DB::beginTransaction();

        $result = Withholding::create($data);
        foreach ($data_items as $key => $item) {
            $data_items[$key]['withholding_id'] = $result->id;
            $data_items[$key]['paid'] = numberClean($item['paid']);
        }
        WithholdingItem::insert($data_items);
        
        $is_allocation_pmt = boolval($data['rel_payment_id']);
        if ($is_allocation_pmt) {
            // increament allocated amount
            $wh_tax = Withholding::find($data['withholding_tax_id']);
            $wh_tax->increment('allocate_ttl', $data['allocate_ttl']);
            $diff = round($wh_tax->amount - $wh_tax->allocate_ttl);
            if ($diff < 0) throw ValidationException::withMessages(['Allocation limit reached! Please reduce allocated amount by ' . numberFormat(-$diff)]);            

            // compute balances
            $this->customer_credit_balance($result->customer_id);
            $invoice_ids = $wh_tax->items()->pluck('invoice_id')->toArray();
            $this->customer_deposit_balance($invoice_ids);
        } else {
            // compute balances
            $this->customer_credit_balance($result->customer_id);

            /**accounting */
            $this->post_withholding($result);
        }
        
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
        $wht_id = $withholding->id;
        $rel_payment_id = $withholding->rel_payment_id;
        $is_income_cert = ($withholding->certificate == 'tax');
        $is_allocation_pmt = $withholding->items()->exists();
        $invoice_ids = $withholding->items()->pluck('invoice_id')->toArray();
        $customer = $withholding->customer;

        $withholding->transactions()->delete();
        aggregate_account_transactions();
        $withholding->items()->delete();
        $result = $withholding->delete();

        if ($is_income_cert) {
            if ($is_allocation_pmt) {
                $lumpsome_wht_pmt = Withholding::find($rel_payment_id);
                // compute allocated total
                $allocated_total = Withholding::where('rel_payment_id', $wht_id)->sum('allocate_ttl');
                $lumpsome_wht_pmt->update(['allocate_ttl' => $allocated_total]);
            } else {
                // check if income certificate has allocated pmts
                $has_allocated_pmts = Withholding::where('rel_payment_id', $wht_id)->exists();
                if ($has_allocated_pmts) throw ValidationException::withMessages(['Withholding Tax has related allocations']);
            }
        }

        // customer balances
        $this->customer_credit_balance($customer->id);
        $this->customer_deposit_balance($invoice_ids);
        
        if ($result) {
            DB::commit();
            return true;
        }
    }
}
