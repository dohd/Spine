<?php

namespace App\Repositories\Focus\journal;

use DB;
use App\Exceptions\GeneralException;
use App\Models\items\JournalItem;
use App\Models\manualjournal\Journal;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
/**
 * Class CustomerRepository.
 */
class JournalRepository extends BaseRepository
{
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
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        $data['date'] = date_for_database($data['date']);
        $data['debit_ttl'] = numberClean($data['debit_ttl']);
        $data['credit_ttl'] = numberClean($data['credit_ttl']);
        $result = Journal::create($data);

        $data_items = $input['data_items'];
        foreach ($data_items as $k => $item) {
            $item['journal_id'] = $result->id;
            $item['credit'] = numberClean($item['credit']);
            $item['debit'] = numberClean($item['debit']);
            $data_items[$k] = $item;
        }
        JournalItem::insert($data_items);

        // accounting
        $this->post_transaction($result);

        DB::commit();
        if ($result) return $result;

        throw new GeneralException(trans('exceptions.backend.customers.create_error'));
    }

    /**
     * Delete method from storage
     */
    public function delete($journal)
    {
        if ($journal->delete()) {
            aggregate_account_transactions(); 
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.customers.create_error'));
    }


    public function post_transaction($result)
    {
        $tr_category = Transactioncategory::where('code', 'genjr')->first(['id', 'code']);
        $data = [
            'trans_category_id' => $tr_category->id,
            'tr_date' => date('Y-m-d'),
            'due_date' => $result['date'],
            'user_id' => $result['user_id'],
            'ins' => $result['ins'],
            'tr_type' => $tr_category->code,
            'tr_ref' => $result['id'],
            'user_type' => 'employee',
            'is_primary' => 1,
            'note' => $result['note'],
        ];

        $debits = array();
        $credits = array();
        foreach ($result->items as $item) {
            if ($item->credit > 0) {
                $credits[] = $data + [
                    'account_id' => $item->account_id,
                    'credit' => $item->credit
                ];
            } else if ($item->debit > 0) {
                $debits[] = $data + [
                    'account_id' => $item->account_id,
                    'debit' => $item->debit
                ];
            }
        }

        foreach ([$debits, $credits] as $tr) {
            Transaction::insert($tr);
        }
        aggregate_account_transactions();    
    }
}