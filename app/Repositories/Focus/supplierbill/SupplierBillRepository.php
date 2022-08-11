<?php

namespace App\Repositories\Focus\supplierbill;

use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\items\SupplierbillItem;
use App\Models\supplierbill\Supplierbill;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;

/**
 * Class ProductcategoryRepository.
 */
class SupplierBillRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Supplierbill::class;

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
     * @return \App\Models\supplierbill\Supplierbill $supplierbill
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();
        // sanitize
        foreach ($input as $key => $val) {
            if (in_array($key, ['date', 'due_date'])) $input[$key] = date_for_database($val);
            if (in_array($key, ['subtotal', 'tax', 'total'])) $input[$key] = numberClean($val);
            if (in_array($key, ['grn_subtotal', 'grn_tax', 'grn_total'])) {
                $input[$key] = array_map(function ($v) { 
                    return numberClean($v); 
                }, $val);
            }
        }

        $result = Supplierbill::create($input);

        $data_items = Arr::only($input, ['grn_id', 'grn_note', 'grn_subtotal', 'grn_tax', 'grn_total']);
        $data_items = array_map(function ($v) use($result) {
            return [
                'supplier_bill_id' => $result->id,
                'goods_receive_note_id' => $v['grn_id'],
                'note' => $v['grn_note'],
                'subtotal' => $v['grn_subtotal'],
                'tax' => $v['grn_tax'],
                'total' => $v['grn_total']
            ];
        }, modify_array($data_items));
        SupplierbillItem::insert($data_items);

        /**accounting */
        $this->post_transaction($result);

        DB::commit();
        if ($result) return $result;


        throw new GeneralException('Error Creating Lead');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param \App\Models\supplierbill\Supplierbill $supplierbill
     * @param  array $input
     * @throws GeneralException
     * @return \App\Models\supplierbill\Supplierbill $supplierbill
     */
    public function update(Supplierbill $supplierbill, array $input)
    {
        dd($input);

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\supplierbill\Supplierbill $supplierbill
     * @throws GeneralException
     * @return bool
     */
    public function delete(Supplierbill $supplierbill)
    {     
        DB::beginTransaction();
        
        Transaction::where(['tr_type' => 'bill', 'note' => $supplierbill->note])->delete();
        $result = $supplierbill->delete();

        if ($result) {
            DB::commit(); 
            return true;
        }
                
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }

    /**
     * Post Goods Received Account transactions
     * @param \App\Models\supplierbill\Supplierbill $supplierbill
     * @return void
     */
    public function post_transaction($supplierbill)
    {
        // debit Uninvoiced Goods Received Note (liability)
        $account = Account::where('system', 'grn')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'bill')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $dr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'debit' => $supplierbill->total,
            'tr_date' => $supplierbill->date,
            'due_date' => $supplierbill->due_date,
            'user_id' => $supplierbill->user_id,
            'note' => $supplierbill->note,
            'ins' => $supplierbill->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $supplierbill->id,
            'user_type' => 'supplier',
            'is_primary' => 1
        ];
        Transaction::create($dr_data);

        // credit Accounts Payable (creditors)
        unset($dr_data['debit'], $dr_data['is_primary']);
        $account = Account::where('system', 'grn')->first(['id']);
        $cr_data = array_replace($dr_data, [
            'account_id' => $account->id,
            'debit' => $supplierbill->subtotal,
        ]);    
        Transaction::create($cr_data);

        // credit TAX
        $account = Account::where('system', 'tax')->first(['id']);
        $cr_data = array_replace($dr_data, [
            'account_id' => $account->id,
            'debit' => $supplierbill->tax,
        ]);
        Transaction::create($cr_data);
    }
}