<?php

namespace App\Repositories\Focus\opening_stock;

use DB;
use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\items\OpeningStockItem;
use App\Models\opening_stock\OpeningStock;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
use Illuminate\Support\Arr;

class OpeningStockRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = OpeningStock::class;

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
     * @return OpeningStock $opening_stock
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if ($key == 'date') $input[$key] = date_for_database($val);
            if ($key == 'total') $input[$key] = numberClean($val);
            if (in_array($key, ['qty_alert', 'purchase_price', 'qty', 'amount'])) {
                $input[$key] = array_map(function ($v) {
                    return numberClean($v);
                }, $val);
            }
        }
        $result = OpeningStock::create($input);

        $data_items = Arr::only($input, ['product_id', 'parent_id', 'qty_alert', 'purchase_price', 'qty', 'amount']);
        $data_items = array_filter(modify_array($data_items), fn($v) => $v['qty'] > 0);
        $data_items = array_map(function ($v) use ($result) {
            return array_replace($v, [
                'opening_stock_id' => $result->id,
            ]);
        }, $data_items);
        OpeningStockItem::insert($data_items);

        // update inventory stock
        foreach ($result->items as $item) {
            $item->productvariation->update([
                'purchase_price' => $item->purchase_price,
                'qty' => $item->qty
            ]);
        }

        /**accounting */
        $this->post_transaction($result);

        if ($result) {
            DB::commit();
            return $result;
        }

        throw new GeneralException(trans('exceptions.backend.OpeningStocks.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param OpeningStock $opening_stock
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(OpeningStock $opening_stock, array $input)
    {
        dd($input);

        throw new GeneralException(trans('exceptions.backend.OpeningStocks.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param OpeningStock $opening_stock
     * @throws GeneralException
     * @return bool
     */
    public function delete(OpeningStock $opening_stock)
    {
        DB::beginTransaction();

        // revert stock state
        foreach ($opening_stock->items as $item) {
            $item->productvariation->update([
                'purchase_price' => 0,
            ]);
            $item->productvariation->decrement('qty', $item->qty);
        }

        Transaction::where(['tr_ref' => $opening_stock->id, 'note' => $opening_stock->note])->delete();

        if ($opening_stock->delete()) {
            DB::commit();
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.OpeningStocks.delete_error'));
    }

    /**
     * Opening Stock Balance Transaction
     * 
     * @param OpeningStock $opening_stock
     * @return void
     */
    public function post_transaction(OpeningStock $opening_stock)
    {
        // debit Inventory Account
        $account = Account::where('system', 'stock')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'stock')->first(['id', 'code']);
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
        $dr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'debit' => $opening_stock->total,
            'tr_date' => $opening_stock->date,
            'due_date' => $opening_stock->date,
            'user_id' => $opening_stock->user_id,
            'note' => $opening_stock->note,
            'ins' => $opening_stock->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $opening_stock->id,
            'user_type' => 'company',
            'is_primary' => 1
        ];
        Transaction::create($dr_data);

        // credit Retained Earnings
        unset($dr_data['debit'], $dr_data['is_primary']);
        $account = Account::where('system', 'retained_earning')->first(['id']);
        $cr_data = array_replace($dr_data, [
            'account_id' => $account->id,
            'credit' => $opening_stock->total,
        ]);
        Transaction::create($cr_data);
        aggregate_account_transactions();
    }
}
