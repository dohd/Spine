<?php

namespace App\Repositories\Focus\verification;

use App\Exceptions\GeneralException;
use App\Models\quote\Quote;
use App\Models\verification\Verification;
use App\Repositories\BaseRepository;
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
        $data = Arr::only($input, ['quote_id', 'customer_id', 'branch_id', 'note', 'taxable', 'subtotal', 'tax', 'total']);
        $data_items = Arr::only($input, [
            'numbering', 'product_name', 'unit', 'item_tax_id', 'product_qty', 'product_subtotal', 'product_tax', 'product_total', 'remark', 
            'row_index', 'a_type', 'product_id', 'quote_item_id'
        ]);
        $jc_data_items = Arr::only($input, ['type', 'reference', 'date', 'technician', 'equipment', 'location', 'fault', 'equipment_id']);
        dd($input, $data, $data_items, $jc_data_items);



            
        throw new GeneralException(trans('exceptions.backend.leave_category.create_error'));
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
        dd($verification->id);
        
        if ($verification->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.leave_category.delete_error'));
    }
}
