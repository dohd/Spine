<?php

namespace App\Repositories\Focus\rjc;

use DB;
use App\Models\items\RjcItem;
use App\Models\rjc\Rjc;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Storage;

/**
 * Class ProductcategoryRepository.
 */
class RjcRepository extends BaseRepository
{
    /**
     *file_path .
     *
     * @var string
     */
    protected $file_path;

    /**
     * Storage Class Object.
     *
     * @var \Illuminate\Support\Facades\Storage
     */
    protected $storage;

    /**
     * Associated Repository Model.
     */
    const MODEL = Rjc::class;

    public function __construct()
    {
        $this->file_path = 'img' . DIRECTORY_SEPARATOR . 'rjcreport' . DIRECTORY_SEPARATOR;
        $this->storage = Storage::disk('public');
    }

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * 
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
     * @return object
     */
    public function create(array $input)
    {
        // rjc input data
        $data = $input['data'];
        
        $data['report_date'] = date_for_database($data['report_date']);
        // increament tid
        $ref =  Rjc::orderBy('tid', 'desc')->first('tid')->tid;
        if ($data['tid'] <= $ref) {
            $data['tid'] = $ref + 1;
        }
        
        // upload files
        foreach($data as $key => $value) {
            if ($key == 'image_one' || $key == 'image_two' || $key == 'image_three' || $key == 'image_four') {
                if ($value) {
                    $data[$key] = $this->uploadFile($value);
                }                
            }
        }

        DB::beginTransaction();
        $result = Rjc::create($data);

        // rjc items
        $item_count = count($input['data_items']['tag_number']);
        $data_items = $this->items_array(
            $item_count, 
            $input['data_items'],
            ['rjc_id' => $result['id'], 'ins' => $result['ins']]
        );

        // bulk insert rjc items
        if ($result && $item_count) {
            RjcItem::insert($data_items);
            DB::commit();
            return $result;
        }

        throw new GeneralException('Error Creating Rjc');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Rjc $rjc
     * @param  array $input
     * @throws GeneralException
     * @return object
     */
    public function update(array $input)
    {
        // rjc input data
        $data = $input['data'];
        $data['report_date'] = date_for_database($data['report_date']);

        DB::beginTransaction();
        $result = Rjc::where('id', $data['id'])->update($data);

        // rjc items
        $item_count = count($input['data_items']['tag_number']);
        $data_items = $this->items_array(
            $item_count, 
            $input['data_items'],
            ['rjc_id' => $data['id'], 'ins' => $data['ins']]
        );

        // update or create new rjc_item
        if ($result && $item_count) {
            foreach($data_items as $item) {
                $rjc_item = RjcItem::firstOrNew([
                    'id' => $item['item_id'],
                    'rjc_id' => $item['rjc_id'],
                ]);
                // assign properties to the item
                foreach($item as $key => $value) {
                    $rjc_item[$key] = $value;
                }
                // remove stale attributes
                if ($rjc_item['id'] == 0) unset($rjc_item['id']);
                unset($rjc_item['item_id']);

                $rjc_item->save();
            }

            DB::commit();
            return $result;
        }

        throw new GeneralException('Error Updating Rjc');
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Rjc $rjc
     * @throws GeneralException
     * @return bool
     */
    public function delete(Rjc $rjc)
    {
        // delete rjc_items items then delete rjc
        if ($rjc->items()->delete() && $rjc->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }

    // Delete rjc item from storage
    public function delete_item($id)
    {
        if (RjcItem::destroy($id)) {
            return true;
        }

        throw new GeneralException('Error deleting Rjc Item');
    }

    // Upload file to storage
    public function uploadFile($file)
    {
        $path = $this->file_path;
        $file_name = time() . $file->getClientOriginalName();

        $this->storage->put($path . $file_name, file_get_contents($file->getRealPath()));

        return $file_name;
    }

    // Convert array to database collection format
    protected function items_array($count=0, $item=[], $extra=[])
    {
        $data_items = array();
        for ($i = 0; $i < $count; $i++) {
            $row = $extra;
            foreach (array_keys($item) as $key) {
                $value = $item[$key][$i];
                if ($key == 'last_service_date' || $key == 'next_service_date') {
                    $value = date_for_database($value);
                }
                $row[$key] = $value;
            }
            $data_items[] = $row;
        }
        return $data_items;
    }
}
