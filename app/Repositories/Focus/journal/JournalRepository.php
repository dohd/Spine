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
        DB::beginTransaction();

        Transaction::where(['tr_ref' => $journal->id, 'tr_type' => 'genjr'])->delete();
        aggregate_account_transactions(); 
        $result = $journal->delete();

        DB::commit();
        if ($result) return true;

        throw new GeneralException(trans('exceptions.backend.customers.create_error'));
    }


    public function post_transaction($result)
    {
        $tr_category = Transactioncategory::where('code', 'genjr')->first(['id', 'code']);
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
        $data = [
            'tid' => $tid,
            'trans_category_id' => $tr_category->id,
            'tr_date' => $result->date,
            'due_date' => $result->date,
            'user_id' => $result->user_id,
            'ins' => $result->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $result->id,
            'user_type' => 'company',
            'is_primary' => 1,
            'note' => $result->note,
        ];

        $tr_data = array();
        foreach ($result->items as $item) {
            $i = count($tr_data) - 1;
            if (isset($tr_data[$i])) {
                if ($tr_data[$i]['is_primary'])
                    $tr_data[$i]['is_primary'] = 0;
            }
            if ($item->debit > 0) {
                $tr_data[] = $data + [
                    'account_id' => $item->account_id,
                    'debit' => $item->debit,
                    'credit' => 0
                ];
            } elseif ($item->credit > 0) {
                $tr_data[] = $data + [
                    'account_id' => $item->account_id,
                    'credit' => $item->credit,
                    'debit' => 0
                ];
            }
        }
        Transaction::insert($tr_data);
        aggregate_account_transactions();    
    }
}