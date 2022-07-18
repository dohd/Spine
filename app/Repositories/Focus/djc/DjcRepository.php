<?php

namespace App\Repositories\Focus\djc;

use DB;
use App\Models\items\DjcItem;
use App\Models\djc\Djc;
use App\Exceptions\GeneralException;
use App\Models\lead\Lead;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Storage;

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
        // dd($input);
        $data = $input['data'];
        foreach($data as $key => $value) {
            if (in_array($key, ['image_one', 'image_two', 'image_three', 'image_four'], 1)) {
                if ($value) $data[$key] = $this->uploadFile($value);
            }
            if (in_array($key, ['report_date', 'jobcard_date'], 1))
                $data[$key] = date_for_database($value);
        }

        DB::beginTransaction();
        // close lead
        Lead::find($data['lead_id'])->update(['status' => 1, 'reason' => 'won']);
        // increament tid
        $last_tid =  Djc::max('tid');
        if ($data['tid'] <= $last_tid) $data['tid'] = $last_tid + 1;
        $result = Djc::create($data);

        $data_items = $input['data_items'];
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'djc_id' => $result->id, 
                'ins' => $result->ins,
                'last_service_date' => date_for_database($v['last_service_date']),
                'next_service_date' => date_for_database($v['next_service_date'])
            ]);
        }, $data_items);
        DjcItem::insert($data_items);

        DB::commit();
        if ($result)  return $result;
           
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
    public function update($djc, array $input)
    {
        // dd($input);
        $data = $input['data'];
        foreach($data as $key => $value) {
            if (in_array($key, ['image_one', 'image_two', 'image_three', 'image_four'], 1)) {
                if ($value) $data[$key] = $this->uploadFile($value);
            }
            if (in_array($key, ['report_date', 'jobcard_date'], 1))
                $data[$key] = date_for_database($value);
        }
        DB::beginTransaction();

        // if different lead, open previous lead
        if ($djc->lead && $djc->lead->status && $djc->lead_id != $data['lead_id']) 
            $djc->lead->update(['status' => 0]);
        // close current lead
        Lead::find($data['lead_id'])->update(['status' => 1, 'reason' => 'won']);
        $result = $djc->update($data);

        $data_items = $input['data_items'];
        // remove omitted items
        $item_ids = array_map(function ($v) { return $v['item_id']; }, $data_items);
        $djc->items()->whereNotIn('id', $item_ids)->delete();

        // update or create new djc_item
        foreach($data_items as $item) {
            $item = array_replace($item, [
                'djc_id' => $djc->id,
                'ins' => $djc->ins,
                'last_service_date' => date_for_database($item['last_service_date']),
                'next_service_date' => date_for_database($item['next_service_date'])
            ]);
            $djc_item = DjcItem::firstOrNew(['id' => $item['item_id']]);
            $djc_item->fill($item);
            if (!$djc_item->id) unset($djc_item->id);
            unset($djc_item->item_id);
            $djc_item->save();
        }

        DB::commit();
        if ($result)  return $result;

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

    // Delete djc item from storage
    public function delete_item($id)
    {
        if (DjcItem::destroy($id)) return true;        

        throw new GeneralException('Error deleting Djc Item');
    }

    // Upload file to storage
    public function uploadFile($file)
    {
        $file_name = $this->file_path . time() . $file->getClientOriginalName();
        $this->storage->put($file_name, file_get_contents($file->getRealPath()));

        return $file_name;
    }
}
