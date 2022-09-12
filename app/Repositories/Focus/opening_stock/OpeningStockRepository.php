<?php

namespace App\Repositories\Focus\opening_stock;

use DB;
use App\Exceptions\GeneralException;
use App\Models\opening_stock\OpeningStock;
use App\Repositories\BaseRepository;


class OpeningStockRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = OpeningStock::class;

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
        dd($input);

        throw new GeneralException(trans('exceptions.backend.OpeningStocks.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param OpeningStock $opening_stock
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(OpeningStock $opening_stock, array $input)
    {
        dd($input);

        throw new GeneralException(trans('exceptions.backend.OpeningStocks.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param OpeningStock $opening_stock
     * @throws GeneralException
     * @return bool
     */
    public function delete(OpeningStock $opening_stock)
    {
        if ($opening_stock->delete()) return true;
        
        throw new GeneralException(trans('exceptions.backend.OpeningStocks.delete_error'));
    }
}
