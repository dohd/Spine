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

        if ($result) {
            // djc_item input data
            $data_items = $this->items_array($input['data_item'], $result->id, $result->ins);

            DjcItem::insert($data_items);
            DB::commit();
            
            return $result->id;
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

        // djc_item input data
        $data_items = $this->items_array($input['data_item'], $data['id'], $data['ins']);

        DB::beginTransaction();
        // update djc data
        $result = Djc::where('id', $data['id'])->update($data);

        if ($result) {
            if (count($data_items)) {
                foreach($data_items as $item) {
                    // update or create new djc_item
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
            }

            DB::commit();
            return;
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

    // Generate data_items array
    protected function items_array($item, $djc_id, $djc_ins)
    {
        $data_items = array();
        for ($i = 0; $i < count($item['tag_number']); $i++) {
            $tmp = array('djc_id' => $djc_id, 'ins' => $djc_ins);
            foreach(array_keys($item) as $key) {
                $value = $item[$key][$i];
                if ($key == 'last_service_date' || $key == 'next_service_date') {
                    $value = date_for_database($value);
                }
                $tmp[$key] = $value;
            }
            $data_items[] = $tmp;
        }
        
        return $data_items;
    }
}
