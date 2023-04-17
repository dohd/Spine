<?php

namespace App\Repositories\Focus\verification;

use App\Exceptions\GeneralException;
use App\Models\items\VerificationItem;
use App\Models\project\BudgetItem;
use App\Models\quote\Quote;
use App\Models\verification\Verification;
use App\Models\verification\VerificationJc;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;

class VerificationRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Verification::class;

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

    public function getForVerificationQuoteDataTable()
    {
        $q = Quote::query()->whereColumn('total', '>', 'verified_total');
            
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return Verification $verification
     */
    public function create(array $input)
    {
        // dd($input);
        foreach ($input as $key => $value) {
            if (in_array($key, ['taxable', 'subtotal', 'tax', 'total']))
                $input[$key] = numberClean($value);
            if (in_array($key, ['product_subtotal', 'product_tax', 'product_total'])) {
                if (is_array($value)) $input[$key] = array_map(fn($v) => floatval(str_replace(',', '', $v)), $value);                 ;
            }
        }

        $data = Arr::only($input, ['quote_id', 'customer_id', 'branch_id', 'note', 'taxable', 'subtotal', 'tax', 'total']);
        $data_items = Arr::only($input, [
            'numbering', 'product_name', 'unit', 'tax_rate', 'product_qty', 'product_subtotal', 'product_tax', 'product_total', 'remark', 
            'row_index', 'a_type', 'product_id', 'quote_item_id'
        ]);
        $jc_data_items = Arr::only($input, [
            'type', 'reference', 'date', 'technician', 'equipment', 'location', 'fault', 'equipment_id'
        ]);
        $data_items = modify_array($data_items);
        $jc_data_items = modify_array($jc_data_items);
        // dd($data, $data_items, $jc_data_items);
        DB::beginTransaction();

        // part verification
        $verifix = Verification::create($data);

        // part verification items
        $data_items = array_map(function($v) use($verifix) {
            return array_replace($v, [
                'parent_id' => $verifix->id,
            ]);
        }, $data_items);
        VerificationItem::insert($data_items);

        // part verification jobcards/dnotes
        $jc_data_items = array_filter($jc_data_items, fn($v) => $v['reference']);
        $jc_data_items = array_map(function($v) use($verifix) {
            return array_replace($v, [
                'parent_id' => $verifix->id,
            ]);
        }, $jc_data_items);
        VerificationJc::insert($jc_data_items);

        if ($verifix) {
            DB::commit();
            return $verifix;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Verification $verification
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(Verification $verification, array $input)
    {
        dd($input);
        // dd($input);
        foreach ($input as $key => $value) {
            if (in_array($key, ['taxable', 'subtotal', 'tax', 'total']))
                $input[$key] = numberClean($value);
            if (in_array($key, ['product_subtotal', 'product_tax', 'product_total'])) {
                if (is_array($value)) $input[$key] = array_map(fn($v) => floatval(str_replace(',', '', $v)), $value);                 ;
            }
        }

        $data = Arr::only($input, ['quote_id', 'customer_id', 'branch_id', 'note', 'taxable', 'subtotal', 'tax', 'total']);
        $data_items = Arr::only($input, [
            'numbering', 'product_name', 'unit', 'tax_rate', 'product_qty', 'product_subtotal', 'product_tax', 'product_total', 'remark', 
            'row_index', 'a_type', 'product_id', 'quote_item_id'
        ]);
        $jc_data_items = Arr::only($input, [
            'type', 'reference', 'date', 'technician', 'equipment', 'location', 'fault', 'equipment_id'
        ]);
        $data_items = modify_array($data_items);
        $jc_data_items = modify_array($jc_data_items);
        // dd($data, $data_items, $jc_data_items);
        DB::beginTransaction();

        // part verification
        $verifix = Verification::create($data);

        // part verification items
        $data_items = array_map(function($v) use($verifix) {
            return array_replace($v, [
                'parent_id' => $verifix->id,
            ]);
        }, $data_items);
        VerificationItem::insert($data_items);

        // part verification jobcards/dnotes
        $jc_data_items = array_filter($jc_data_items, fn($v) => $v['reference']);
        $jc_data_items = array_map(function($v) use($verifix) {
            return array_replace($v, [
                'parent_id' => $verifix->id,
                'type' => $v['type'] == 1? 'jobcard' : 'dnote',
            ]);
        }, $jc_data_items);
        VerificationJc::insert($jc_data_items);

        if ($verifix) {
            DB::commit();
            return $verifix;
        }

        throw new GeneralException(trans('exceptions.backend.leave_category.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Verification $verification
     * @throws GeneralException
     * @return bool
     */
    public function delete(Verification $verification)
    {   
        if ($verification->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.leave_category.delete_error'));
    }
}
