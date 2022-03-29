<?php

namespace App\Repositories\Focus\purchase;

use App\Models\purchase\Purchase;
use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\items\PurchaseItem;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;

use Illuminate\Support\Facades\DB;
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;

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
            $item = $item + [
                'ins' => $bill->ins,
                'user_id' => $bill->user_id,
                'bill_id' => $bill->id
            ];
            foreach ($item as $key => $val) {
                if (in_array($key, ['rate', 'taxrate', 'amount'], 1)) {
                    $item[$key] = numberClean($val);
                }
            }
            $purchase_items[$i] = $item;
        }
        PurchaseItem::insert($purchase_items);

        // accounts
        $this->post_transaction($purchase, $purchase_items, $bill);

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

        // accounts
        Transaction::where('tr_ref', $purchase->id)->delete();
        $this->post_transaction($bill, $bill_items, $purchase);

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

    /**
     * Post Account Transaction
     */
    protected function post_transaction(array $purchase, array $purchase_items, $bill) 
    {
        /** credit */ 
        $account = Account::where('system', 'payable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'BILL')->first(['id', 'code']);
        $cr_data = [
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $purchase['grandttl'],
            'tr_date' => date('Y-m-d'),
            'due_date' => $purchase['due_date'],
            'user_id' => $purchase['user_id'],
            'note' => $purchase['note'],
            'ins' => $purchase['ins'],
            'tr_type' => $tr_category->code,
            'tr_ref' => $bill->id,
            'user_type' => 'supplier',
            'is_primary' => 1
        ];
        $tr = Transaction::create($cr_data);
        $bill->update(['tr_ref' => $tr->id]);

        /** debit */
        $dr_data = array();
        unset($cr_data['credit'], $cr_data['is_primary']);
        // stock
        $stock_items = array_filter($purchase_items, function ($item) { return $item['type'] == 'Stock'; });
        if ($stock_items) {
            $account = Account::where('system', 'stock')->first(['id']);
            $stock_tr_category = Transactioncategory::where('code', 'stock')->first(['id']);
            $dr_data[] = array_replace($cr_data, [
                'trans_category_id' => $stock_tr_category->id,
                'debit' => $purchase['stock_subttl'],
            ]);    
        }
        // expense and asset
        $asset_tr_category = Transactioncategory::where('code', 'p_asset')->first(['id']);
        foreach ($purchase_items as $item) {
            $subttl = $item['amount'] - $item['taxrate'];
            if ($item['type'] == 'Expense') {
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $item['item_id'],
                    'debit' => $subttl,
                ]);
            }
            if ($item['type'] == 'Asset') {
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $item['item_id'],
                    'trans_category_id' => $asset_tr_category->id,
                    'debit' => $subttl,
                ]);
            }
        }
        // tax
        $account = Account::where('system', 'tax')->first(['id']);
        $dr_data[] = array_replace($cr_data, [
            'account_id' => $account->id, 
            'debit' => $purchase['grandtax'],
        ]);
        Transaction::insert($dr_data); 
        
        // update account ledgers debit and credit totals
        $tr_totals = Transaction::where('tr_ref', $bill->id)
            ->select(DB::raw('SELECT account_id as id, SUM(credit) as credit_ttl, SUM(debit) as debit_ttl'))
            ->groupBy('account_id')
            ->get()->toArray();
        Batch::update(new Account, $tr_totals, 'id');
    }
}
