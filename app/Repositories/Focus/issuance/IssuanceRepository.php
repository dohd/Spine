<?php

namespace App\Repositories\Focus\issuance;

use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\issuance\Issuance;
use App\Models\items\IssuanceItem;
use App\Models\product\ProductVariation;
use App\Models\project\Budget;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
use DB;

/**
 * Class ProductcategoryRepository.
 */
class IssuanceRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Issuance::class;

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
            if ($key == 'date') $data[$key] = date_for_database($val);
            if (in_array($key, ['subtotal', 'tax', 'total']))
                $data[$key] = numberClean($val);
        }
        $result = Issuance::create($data);

        $data_items = $input['data_items'];
        $budget_items = $result->quote->budget->items;
        $status = '';
        foreach ($data_items as $k => $item) {
            $item = $item + ['issuance_id' => $result->id];
            $product = ProductVariation::find($item['product_id']);
            if ($product && $item['qty'] >= $product->qty) {
                // reduce stock
                $item['qty'] = $product->qty;
                $product->decrement('qty', $item['qty']);
                // increase budget_item issue_qty
                foreach ($budget_items as $b_item) {
                    if ($b_item->product_id == $product->id)
                        $b_item->increment('issue_qty', $item['qty']);
                }
                $status = 'partial';
            } 
            else $item['qty'] = 0;
            $data_items[$k] = $item;
        }
        $data_items = array_filter($data_items, function ($v) { return $v['qty'] > 0; });
        if (!$data_items) return false;
        
        if ($status) $result->quote->update(['issuance_status' => $status]);
        IssuanceItem::insert($data_items);
        
        /** accounts */
        $this->post_transaction($result);

        DB::commit();
        if ($result) return $result;

        throw new GeneralException('Error Creating Lead');
    }

    public function post_transaction($result)
    {
        // credit stock/inventory account
        $account = Account::where('holder', 'LIKE', '%Inventory%')->first('id');
        $tr_category = Transactioncategory::where('code', 'stock')->first(['id', 'code']);
        $cr_data = [
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $result['total'],
            'tr_date' => date('Y-m-d'),
            'due_date' => $result['date'],
            'user_id' => $result['user_id'],
            'ins' => $result['ins'],
            'tr_type' => $tr_category->code,
            'tr_ref' => $result['id'],
            'user_type' => 'customer',
            'is_primary' => 1,
            'note' => $result['note'],
        ];
        Transaction::create($cr_data);

        unset($cr_data['credit'], $cr_data['is_primary']);
        $account = Account::where('holder', 'LIKE', '%WIP%')->first('id');
        // debit work in progress account (WIP)
        $dr_data = array_replace($cr_data, [
            'account_id' =>  $account->id,
            'debit' => $result['total'],
        ]);
        Transaction::create($dr_data);

        // update account ledgers debit and credit totals
        aggregate_account_transactions();
    }    


    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete(Lead $lead)
    {
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}
