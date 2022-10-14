<?php

namespace App\Repositories\Focus\pricelist;

use App\Exceptions\GeneralException;
use App\Models\client_product\ClientProduct;
use App\Repositories\BaseRepository;

/**
 * Class ProductcategoryRepository.
 */
class PriceListRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = ClientProduct::class;

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
        $input['rate'] = numberClean($input['rate']);
        $result = ClientProduct::create($input);
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
    public function update(ClientProduct $client_product, array $input)
    {
        // dd($input);
        $input['rate'] = numberClean($input['rate']);
        if ($client_product->update($input)) return true;

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete(ClientProduct $client_product)
    {
        if ($client_product->delete()) return true;
        
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}