<?php

namespace App\Repositories\Focus\contract;

use App\Exceptions\GeneralException;
use App\Models\contract\Contract;
use App\Repositories\BaseRepository;

/**
 * Class ProductcategoryRepository.
 */
class ContractRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Contract::class;

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
    public function create(array $data)
    {
        throw new GeneralException('Error Creating Contract');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Contract $contract, array $data)
    {
        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete(Contract $contract)
    {   
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}