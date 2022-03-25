<?php

namespace App\Repositories\Focus\purchase;

use App\Models\purchase\Purchase;
use App\Exceptions\GeneralException;
use App\Models\items\PurchaseItem;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;

use Illuminate\Support\Facades\DB;

/**
 * Class PurchaseorderRepository.
 */
class PurchaseRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Purchase::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query()->where('po_id', 0);

        return $q->get();
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
        DB::beginTransaction();

        $purchase = $input['purchase'];
        $rate_keys = [
            'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
        ];
        foreach ($purchase as $key => $val) {
            if (in_array($key, ['date', 'due_date'], 1)) {
                $purchase[$key] = date_for_database($val);
            }
            if (in_array($key, $rate_keys, 1)) {
                $purchase[$key] = numberClean($val);
            }
        }
        $bill = Purchase::create($purchase);

        $purchase_items = $input['purchase_items'];
        foreach ($purchase_items as $i => $item) {
            $purchase_items[$i] = $item + [
                'ins' => $bill->ins,
                'user_id' => $bill->user_id,
                'bill_id' => $bill->id
            ];
            foreach ($item as $key => $val) {
                if (in_array($key, ['rate', 'taxrate', 'amount'], 1)) {
                    $purchase_items[$i][$key] = numberClean($val);
                }
            }
        }
        PurchaseItem::insert($purchase_items);


        /** credit */ 
        $account = Account::where('system', 'payable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'BILL')->first(['id', 'code']);
        $cr_data = [
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $purchase['grandttl'],
            'tr_date' => date_for_database(date('Y-m-d')),
            'due_date' => $purchase['due_date'],
            'user_id' => $purchase['user_id'],
            'note' => $purchase['note'],
            'ins' => $purchase['ins'],
            'tr_type' => $tr_category->code,
            'tr_ref' => $bill->id,
            'user_type' => 'supplier',
            'is_primary' => 1
        ];

        /** debit */
        $dr_data = array();
        $init_dr_data = array_diff_key($cr_data, array_flip(['credit', 'is_primary']));
        // stock
        $account = Account::where('system', 'stock')->first(['id']);
        $stock_tr_category = Transactioncategory::where('code', 'stock')->first(['id']);
        $dr_data[] = $init_dr_data + [
            'account_id' => $account->id,
            'trans_category_id' => $stock_tr_category->id,
            'debit' => $purchase['stock_subttl'],
        ];
        // expenses and assets
        $exp_tr_category = Transactioncategory::where('code', 'BILL')->first(['id']);
        $asset_tr_category = Transactioncategory::where('code', 'p_asset')->first(['id']);
        foreach ($purchase_items as $item) {
            $subttl = $item['amount'] - $item['taxrate'];
            if ($item['type'] == 'Expense') {
                $dr_data[] = $init_dr_data + [
                    'account_id' => $item['item_id'],
                    'trans_category_id' => $exp_tr_category->id,
                    'debit' => $subttl,
                ];
            }
            if ($item['type'] == 'Asset') {
                $dr_data[] = $init_dr_data + [
                    'account_id' => $item['item_id'],
                    'trans_category_id' => $asset_tr_category->id,
                    'debit' => $subttl,
                ];
            }
        }
            
        Transaction::insert(array_merge([$cr_data], $dr_data));
        $tr = Transaction::where(['bill_id' => $bill->id, 'is_primary' => 1])->first(['id']);
        $bill->update(['tr_ref' => $tr->id]);

        DB::commit();
        if ($bill) return true;        

        throw new GeneralException(trans('exceptions.backend.purchaseorders.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Purchaseorder $purchaseorder
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Purchase $purchase, array $input)
    {
        DB::beginTransaction();

        $bill = $input['bill'];
        $rate_keys = [
            'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
        ];
        foreach ($bill as $key => $val) {
            if (in_array($key, ['date', 'due_date'], 1)) {
                $bill[$key] = date_for_database($val);
            }
            if (in_array($key, $rate_keys, 1)) {
                $bill[$key] = numberClean($val);
            }
        }
        $purchase->update($bill);

        $bill_items = $input['bill_items'];
        // delete items excluded
        $item_ids = array_reduce($bill_items, function ($init, $item) {
            array_push($init, $item['id']);
            return $init;
        }, []);
        $purchase->products()->whereNotIn('id', $item_ids)->delete();

        // update or create new items
        foreach ($bill_items as $item) {
            $item = $item + [
                'ins' =>  $purchase->ins,
                'user_id' =>  $purchase->user_id,
                'bill_id' => $purchase->id
            ];

            $bill_item = PurchaseItem::firstOrNew(['id' => $item['id']]);
            foreach($item as $key => $val) {
                if (in_array($key, ['rate', 'taxrate', 'amount'], 1)) {
                    $bill_item[$key] = numberClean($val);
                } 
                else $bill_item[$key] = $val;
            }
            if (!$bill_item->id) unset($bill_item->id);
            $bill_item->save();                
        }

        /** credit */ 
        $account = Account::where('system', 'payable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'BILL')->first(['id', 'code']);
        $cr_data = [
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $purchase['grandttl'],
            'tr_date' => date_for_database(date('Y-m-d')),
            'due_date' => $purchase['due_date'],
            'user_id' => $purchase['user_id'],
            'note' => $purchase['note'],
            'ins' => $purchase['ins'],
            'tr_type' => $tr_category->code,
            'tr_ref' => $purchase->id,
            'user_type' => 'supplier',
            'is_primary' => 1
        ];

        /** debit */
        $dr_data = array();
        $init_dr_data = array_diff_key($cr_data, array_flip(['credit', 'is_primary']));
        // stock
        $account = Account::where('system', 'stock')->first(['id']);
        $stock_tr_category = Transactioncategory::where('code', 'stock')->first(['id']);
        $dr_data[] = $init_dr_data + [
            'account_id' => $account->id,
            'trans_category_id' => $stock_tr_category->id,
            'debit' => $purchase['stock_subttl'],
        ];
        // expenses and assets
        $exp_tr_category = Transactioncategory::where('code', 'BILL')->first(['id']);
        $asset_tr_category = Transactioncategory::where('code', 'p_asset')->first(['id']);
        $purchase_items = PurchaseItem::where('bill_id', $purchase->id)->get();
        foreach ($purchase_items as $item) {
            $subttl = $item['amount'] - $item['taxrate'];
            if ($item['type'] == 'Expense') {
                $dr_data[] = $init_dr_data + [
                    'account_id' => $item['item_id'],
                    'trans_category_id' => $exp_tr_category->id,
                    'debit' => $subttl,
                ];
            }
            if ($item['type'] == 'Asset') {
                $dr_data[] = $init_dr_data + [
                    'account_id' => $item['item_id'],
                    'trans_category_id' => $asset_tr_category->id,
                    'debit' => $subttl,
                ];
            }
        }
        
        Transaction::where('tr_ref', $purchase->id)->delete();
        Transaction::insert(array_merge([$cr_data], $dr_data));        

        DB::commit();
        if ($purchase) return true;

        throw new GeneralException(trans('exceptions.backend.purchaseorders.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Purchaseorder $purchaseorder
     * @throws GeneralException
     * @return bool
     */
    public function delete($purchase)
    {
        if ($purchase->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.purchaseorders.delete_error'));
    }
}
