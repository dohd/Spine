<?php

namespace App\Repositories\Focus\stock_transfer;

use App\Exceptions\GeneralException;
use App\Models\stock_transfer\StockTransfer;
use App\Repositories\BaseRepository;

class StockTransferRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = StockTransfer::class;

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
     * @return StockTransfer $stock_transfer
     */
    public function create(array $input)
    {
        dd($input);
            
        throw new GeneralException(trans('exceptions.backend.stock_transfer.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param StockTransfer $stock_transfer
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(StockTransfer $stock_transfer, array $input)
    {   
        dd($stock_transfer);

        throw new GeneralException(trans('exceptions.backend.stock_transfer.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param StockTransfer $stock_transfer
     * @throws GeneralException
     * @return bool
     */
    public function delete(StockTransfer $stock_transfer)
    {
        if ($stock_transfer->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.stock_transfer.delete_error'));
    }
}
