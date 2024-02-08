<?php

namespace App\Repositories\Focus\journal;

use DB;
use App\Exceptions\GeneralException;
use App\Models\items\JournalItem;
use App\Models\manualjournal\Journal;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;

/**
 * Class JournalRepository.
 */
class JournalRepository extends BaseRepository
{
    use Accounting;
    /**
     * Associated Repository Model.
     */
    const MODEL = Journal::class;

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
     * @return bool
     * @throws GeneralException
     */
    public function create(array $input)
    {
        DB::beginTransaction();

        $data = $input['data'];
        $data = array_replace($data, [
            'date' => date_for_database($data['date']),
            'debit_ttl' => numberClean($data['debit_ttl']),
            'credit_ttl' => numberClean($data['credit_ttl'])
        ]);
        $result = Journal::create($data);

        $data_items = $input['data_items'];
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'journal_id' => $result->id,
                'debit' =>  numberClean($v['debit']),
                'credit' => numberClean($v['credit']),
            ]);
        }, $data_items);
        JournalItem::insert($data_items);
        
        /** accounting */ 
        $this->post_gen_journal($result);

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For updating the respective model in storage
     *
     * @param array $input
     * @return bool
     * @throws GeneralException
     */
    public function update($journal, array $input)
    {
        // 
    }

    /**
     * Delete method from storage
     */
    public function delete($journal)
    {
        DB::beginTransaction();

        $journal->transactions()->delete();
        aggregate_account_transactions();
        $journal->items()->delete(); 
        $result = $journal->delete();

        if ($result) {
            DB::commit();
            return $result;
        }
    }
}