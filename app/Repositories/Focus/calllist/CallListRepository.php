<?php

namespace App\Repositories\Focus\calllist;
use App\Models\prospect\Prospect;
use App\Models\calllist\CallList;
use App\Exceptions\GeneralException;
use App\Models\items\Prefix;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Validation\ValidationException;

/**
 * Class ProductcategoryRepository.
 */
class CallListRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = CallList::class;

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
    public function create(array $data)
    {
        $data['start_date'] = date_for_database($data['start_date']);
        $data['end_date'] = date_for_database($data['end_date']);

        $result = CallList::create($data);
        $response = $result->refresh();
        $callid= $response->id;

        $prospects = Prospect::where('industry',$data['title'])->limit($data['prospects_number'])->get()->toArray();

        dd($prospects);

        return $response;

        throw new GeneralException('Error Creating CallList');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param \App\Models\CallList $calllist
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    // public function update(array $data)
    // {
    //     DB::beginTransaction();

    //     $result = $calllist->update($data);
    //     if ($result) {
    //         DB::commit();
    //         return true;
    //     }

    //     throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    // }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\calllist\CallList $calllist
     * @throws GeneralException
     * @return bool
     */
    public function delete(CallList $calllist)
    {
        $prefix = Prefix::where('note', 'calllist')->first();
        $tid = gen4tid("{$prefix}-", $calllist->reference);

        if ($calllist->djcs->count())
            throw ValidationException::withMessages(["{$tid} is attached to DJC Report!"]);
        if ($calllist->quotes->count())
            throw ValidationException::withMessages(["{$tid} is attached to Quote!"]);

        if ($calllist->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}
