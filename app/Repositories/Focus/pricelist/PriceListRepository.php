<?php

namespace App\Repositories\Focus\pricelist;

use App\Exceptions\GeneralException;
use App\Models\pricelist\PriceList;
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
    const MODEL = PriceList::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        return $this->query()->where('pricegroup_id', request('pricegroup_id'))->get();
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
        $data_items = $input['data_items'];
        // update or create
        foreach ($data_items as $v) {
            $v = $v + $data;
            $v['price'] = numberClean($v['price']);
            $item = PriceList::firstOrNew([
                'product_id' => $v['product_id'],
                'pricegroup_id' => $v['pricegroup_id'],
            ]);
            foreach($v as $key => $val) {
                $item[$key] = $val;
            }
            $item->save();
        }

        DB::commit();
        if ($data) return $data;

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
    public function update(PriceList $pricelist, array $data)
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
    public function delete(PriceList $pricelist)
    {
        if ($pricelist->delete()) return true;
        
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}