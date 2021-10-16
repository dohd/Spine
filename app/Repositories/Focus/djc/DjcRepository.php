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
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
        // $q->when(!request('rel_type'), function ($q) {
        // return $q->where('c_type', '=',request('rel_type',0));
        //});
        //$q->when(request('rel_type'), function ($q) {
        // return $q->where('rel_id', '=',request('rel_id',0));
        // });

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
        $input['data']['report_date'] = date_for_database($input['data']['report_date']);

        if (!empty($input['data']['image_one'])) {
            $input['data']['image_one'] = $this->uploadFile($input['data']['image_one']);
        }
        if (!empty($input['data']['image_two'])) {
            $input['data']['image_two'] = $this->uploadFile($input['data']['image_two']);
        }
        if (!empty($input['data']['image_three'])) {
            $input['data']['image_three'] = $this->uploadFile($input['data']['image_three']);
        }
        if (!empty($input['data']['image_four'])) {
            $input['data']['image_four'] = $this->uploadFile($input['data']['image_four']);
        }

        DB::beginTransaction();
        $action_taken = $input['data']['action_taken'];
        $root_cause = $input['data']['root_cause'];
        $recommendations = $input['data']['recommendations'];
        $input['data'] = array_map('strip_tags', $input['data']);
        $input['data']['action_taken'] = strip_tags($action_taken, config('general.allowed'));
        $input['data']['root_cause'] = strip_tags($root_cause, config('general.allowed'));
        $input['data']['recommendations'] = strip_tags($recommendations, config('general.allowed'));
        $result = Djc::create($input['data']);

        if ($result) {
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
        error_log('===djc update called===');

        // djc data
        $data = $input['data'];
        $data['report_date'] = date_for_database($data['report_date']);

        // djc_item data
        $data_items = $this->items_array($input['data_item'], $data['id'], $data['ins']);

        // database transactional update
        // DB::beginTransaction();
        $action_taken = $data['action_taken'];
        $root_cause = $data['root_cause'];
        $recommendations = $data['recommendations'];

        $data = array_map('strip_tags', $data);
        $data['action_taken'] = strip_tags($action_taken, config('general.allowed'));
        $data['root_cause'] = strip_tags($root_cause, config('general.allowed'));
        $data['recommendations'] = strip_tags($recommendations, config('general.allowed'));
        // error_log(print_r($data, true));
        // $result = Djc::where('id', $data['id'])->update($data);

        if (true) {
            // error_log(print_r($data_items, true));

            foreach($data_items as $item) {
                error_log('=== update or new object ===');

                $db_item = DjcItem::firstOrNew([
                    'djc_id' => $item['djc_id'],
                    'tag_number' => $item['tag_number']
                ]);
                $properties = [
                    'make' => $item['make'], 
                    'equipment_type' => $item['equipment_type'], 
                    'joc_card' => $item['joc_card'], 
                    'capacity' => $item['capacity'], 
                    'location' => $item['location'], 
                    'last_service_date' => $item['last_service_date'], 
                    'next_service_date' => $item['next_service_date'], 
                    'ins' => $item['ins']
                ];
                // assign properties to db_item
                foreach($properties as $key => $value) {
                    $db_item[$key] = $value;
                } 

                // error_log(print_r($db_item, true));
            }

            // DjcItem::insert($dataitems);
            // DB::commit();

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
        if ($djc->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }

    public function uploadFile($file)
    {
        $path = $this->file_path;
        $file_name = time() . $file->getClientOriginalName();

        $this->storage->put($path . $file_name, file_get_contents($file->getRealPath()));

        return $file_name;
    }

    // for generating dataitem array
    protected function items_array($item, $djc_id, $djc_ins)
    {
        error_log('=== input data item=== ');
        error_log(print_r($item, true));

        $data_items = array();
        foreach ($item['tag_number'] as $key => $value) {
            $data_items[] = array(
                'djc_id' => $djc_id, 
                'tag_number' => $item['tag_number'][$key], 
                'make' => $item['make'][$key], 
                'equipment_type' => $item['equipment_type'][$key], 
                'joc_card' => $item['joc_card'][$key], 
                'capacity' => $item['capacity'][$key], 
                'location' => $item['location'][$key], 
                'last_service_date' => date_for_database($item['last_service_date'][$key]), 
                'next_service_date' => date_for_database($item['next_service_date'][$key]), 
                'ins' => $djc_ins
            );
        }
        
        return $data_items;
    }
}
