<?php

namespace App\Repositories\Focus\djc;

use DB;
use Carbon\Carbon;
use App\Models\items\DjcItem;
use App\Models\djc\Djc;
use App\Models\Access\User\User;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\Rose;
use Illuminate\Support\Facades\Storage;
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;

/**
 * Class ProductcategoryRepository.
 */
class DjcRepository extends BaseRepository
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
    const MODEL = Djc::class;

    public function __construct()
    {
        $this->file_path = 'img' . DIRECTORY_SEPARATOR . 'djcreport' . DIRECTORY_SEPARATOR;
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
        // djc input data
        $data = $input['data'];
        $data['report_date'] = date_for_database($data['report_date']);
        
        // upload files
        foreach($data as $key => $value) {
            if ($key == 'image_one' || $key == 'image_two' || $key == 'image_three' || $key == 'image_four') {
                if ($value) {
                    $data[$key] = $this->uploadFile($value);
                }                
            }
        }

        DB::beginTransaction();
        $result = Djc::create($data);
        // djc items
        $item_count = count($input['data_item']['tag_number']);
        $data_items = $this->items_array(
            $item_count, 
            $input['data_item'],
            ['djc_id' => $result['id'], 'ins' => $result['ins']]
        );
        // bulk insert djc items
        if ($result && $item_count) {
            DjcItem::insert($data_items);
            DB::commit();
            return $result;
        }

        throw new GeneralException('Error Creating Djc');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Djc $djc
     * @param  array $input
     * @throws GeneralException
     * @return object
     */
    public function update(array $input)
    {
        // djc input data
        $data = $input['data'];
        $data['report_date'] = date_for_database($data['report_date']);

        DB::beginTransaction();
        $result = Djc::where('id', $data['id'])->update($data);
        // djc items
        $item_count = count($input['data_item']['tag_number']);
        $data_items = $this->items_array(
            $item_count, 
            $input['data_item'],
            ['djc_id' => $data['id'], 'ins' => $data['ins']]
        );

        // update or create new djc_item
        if ($result && $item_count) {
            foreach($data_items as $item) {
                $djc_item = DjcItem::firstOrNew([
                    'djc_id' => $item['djc_id'],
                    'tag_number' => $item['tag_number']
                ]);
                // assign properties to the djc_item
                foreach($item as $key => $value) {
                    if ($key == 'djc_id' || $key == 'tag_number') continue;
                    $djc_item[$key] = $value;
                }

                $djc_item->save();
            }

            DB::commit();
            return $result;
        }

        throw new GeneralException('Error Updating Djc');
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Djc $djc
     * @throws GeneralException
     * @return bool
     */
    public function delete(Djc $djc)
    {
        // delete djc_items items then delete djc
        if ($djc->items()->delete() && $djc->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
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
