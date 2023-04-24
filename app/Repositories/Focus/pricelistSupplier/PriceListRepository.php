<?php

namespace App\Repositories\Focus\pricelistSupplier;

use App\Exceptions\GeneralException;
use App\Models\supplier_product\SupplierProduct;
use App\Repositories\BaseRepository;
use DB;

/**
 * Class ProductcategoryRepository.
 */
class PriceListRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = SupplierProduct::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('supplier_id') && request('contract'), function ($q) {
            $q->where(['supplier_id' => request('supplier_id'), 'contract' => request('contract')]);
        })->when(request('supplier_id'), function ($q) {
            $q->where(['supplier_id' => request('supplier_id')]);
        });

        return $q;
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
        $input['rate'] = numberClean($input['rate']);
        $result = SupplierProduct::create($input);
        if ($result) return $result;

        throw new GeneralException('Error Creating PriceList');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(SupplierProduct $Supplier_product, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $input['rate'] = numberClean($input['rate']);

        if (isset($input['contract']) && $Supplier_product->contract != $input['contract'])
            SupplierProduct::where('contract', $Supplier_product->contract)->update(['contract' => $input['contract']]);
            

        if ($Supplier_product->update($input)) {
            DB::commit();
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete(SupplierProduct $Supplier_product)
    {
        if ($Supplier_product->delete()) return true;
        
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }

    public function mass_delete($input)
    {
        // dd($input);
        $result = null;
        if (request('supplier_id') && request('contract')) {
            $result = SupplierProduct::where([
                'supplier_id' => request('supplier_id'),
                'contract' => request('contract'),
            ])->delete();
        } elseif (request('supplier_id')) {
            $result = SupplierProduct::where(['supplier_id' => request('supplier_id')])->delete();
        }

        return $result;
    }
}