<?php

namespace App\Repositories\Focus\contractservice;

use App\Exceptions\GeneralException;
use App\Models\contractservice\ContractService;
use App\Models\items\ContractServiceItem;
use App\Repositories\BaseRepository;
use DB;

/**
 * Class ProductcategoryRepository.
 */
class ContractServiceRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = ContractService::class;

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
            if ($key == 'date') $data[$key] = date_for_database($val);
            if (in_array($key, ['bill_ttl', 'rate_ttl']))
                $data[$key] = numberClean($val);
        }
        $result = ContractService::create($data);

        $data_items = $input['data_items'];
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, ['contractservice_id' => $result->id]);
        }, $data_items);
        ContractServiceItem::insert($data_items);

        DB::commit();
        if ($result) return $result;
        
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
    public function update($contractservice, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        foreach ($data as $key => $val) {
            if ($key == 'date') $data[$key] = date_for_database($val);
            if (in_array($key, ['bill_ttl', 'rate_ttl']))
                $data[$key] = numberClean($val);
        }
        $result = $contractservice->update($data);

        $data_items = $input['data_items'];
        foreach ($data_items as $item) {
            $item = array_replace($item, [
                'contractservice_id' => $contractservice->id,
            ]);
            $new_item = ContractServiceItem::firstOrNew(['id' => $item['item_id']]);
            $new_item->fill($item);
            if (!$new_item->id) unset($new_item->id);
            unset($new_item->item_id);
            $new_item->save();
        }

        DB::commit();
        if ($result) return true;

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete($contractservice)
    {   
        if ($contractservice->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}