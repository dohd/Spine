<?php

namespace App\Repositories\Focus\reconciliation;

use App\Exceptions\GeneralException;
use App\Models\reconciliation\Reconciliation;
use App\Models\transaction\Transaction;
use App\Repositories\BaseRepository;
use DB;

/**
 * Class ProductcategoryRepository.
 */
class ReconciliationRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Reconciliation::class;

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
            if (in_array($key, ['start_date', 'end_date'], 1)) 
                $data[$key] = date_for_database($val);
            if (in_array($key, ['system_amount', 'open_amount', 'close_amount'], 1)) 
                $data[$key] = numberClean($val);
        }
        $result = Reconciliation::create($data);
        // update reconciled transactions
        foreach ($input['data_items'] as $tr) {
            if ($tr['is_reconciled']) {
                Transaction::find($tr['id'])->update(['reconciliation_id' => $result->id]);
            }
        }
        
        DB::commit();
        if ($result) return $result;

        throw new GeneralException('Error Creating Reconciliation');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Reconciliation $teconcilliation
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Reconciliation $reconcilliation, array $data)
    {
        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Reconciliation $teconcilliation
     * @throws GeneralException
     * @return bool
     */
    public function delete(Reconciliation $reconciliation)
    {
        DB::beginTransaction();

        Transaction::where('reconciliation_id', $reconciliation->id)->update(['reconciliation_id' => 0]);
        $result = $reconciliation->delete();

        DB::commit();
        if ($result) return true;
        
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}