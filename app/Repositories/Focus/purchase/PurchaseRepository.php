<?php

namespace App\Repositories\Focus\purchase;

use App\Models\purchase\Purchase;
use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\assetequipment\Assetequipment;
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
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        $rate_keys = [
            'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
        ];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date', 'due_date'], 1)) 
                $data[$key] = date_for_database($val);
            if (in_array($key, $rate_keys, 1)) 
                $data[$key] = numberClean($val);
        }
        $result = Purchase::create($data);

        $data_items = $input['data_items'];
        foreach ($data_items as $i => $item) {
            $item = $item + [
                'ins' => $result->ins,
                'user_id' => $result->user_id,
                'bill_id' => $result->id
            ];
            foreach ($item as $key => $val) {
                if (in_array($key, ['rate', 'taxrate', 'amount'], 1))
                    $item[$key] = numberClean($val);
            }
            // direct project stock issuance
            if ($result->project_id && $item['type'] == 'Stock')
                $item['itemproject_id'] = $result->project_id;
            $data_items[$i] = $item;
        }
        PurchaseItem::insert($data_items);

        // accounting
        $this->post_transaction($result);

        DB::commit();
        if ($result) return true;        

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
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        $rate_keys = [
            'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
        ];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date', 'due_date'], 1)) 
                $data[$key] = date_for_database($val);
            if (in_array($key, $rate_keys, 1)) 
                $data[$key] = numberClean($val);
        }
        $purchase->update($data);

        $purchase->products()->delete();
        $data_items = $input['data_items'];
        foreach ($data_items as $i => $item) {
            $item = $item + [
                'ins' => $purchase->ins,
                'user_id' => $purchase->user_id,
                'bill_id' => $purchase->id
            ];
            foreach ($item as $key => $val) {
                if (in_array($key, ['rate', 'taxrate', 'amount'], 1))
                    $item[$key] = numberClean($val);
            }
            // direct project stock issuance
            if ($purchase->project_id && $item['type'] == 'Stock')
                $item['itemproject_id'] = $purchase->project_id;
            $data_items[$i] = $item;
        }
        PurchaseItem::insert($data_items);

        // accounts
        Transaction::where('tr_ref', $purchase->id)->delete();
        $this->post_transaction($purchase);

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

    // Account transaction
    protected function post_transaction($bill) 
    {
        // credit Accounts Payable (Creditors)
        $account = Account::where('system', 'payable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'bill')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $bill['grandttl'],
            'tr_date' => date('Y-m-d'),
            'due_date' => $bill['due_date'],
            'user_id' => $bill['user_id'],
            'note' => $bill['note'],
            'ins' => $bill['ins'],
            'tr_type' => $tr_category->code,
            'tr_ref' => $bill->id,
            'user_type' => 'supplier',
            'is_primary' => 1,
            'ref_ledger_id' => 0,
        ];
        $tr = Transaction::create($cr_data);
        $bill->update(['tr_ref' => $tr->id]);

        $dr_data = array();
        unset($cr_data['credit'], $cr_data['is_primary']);
        // debit Stock Account
        $wip_account = Account::where('system', 'wip')->first(['id']);
        $is_stock = $bill->items()->where('type', 'Stock')->count();
        if ($is_stock) {
            $is_for_Project = $bill->items()->where('type', 'Stock')->where('itemproject_id', '>', 0)->count();
            if ($is_for_Project) {
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $wip_account->id,
                    'ref_ledger_id' => $account->id,
                    'trans_category_id' => $tr_category->id,
                    'debit' => $bill['stock_subttl'],
                ]);    
            } else {
                $account = Account::where('system', 'stock')->first(['id']);
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $account->id,
                    'debit' => $bill['stock_subttl'],
                ]);    
            }
        }
        foreach ($bill->items as $item) {
            $subttl = $item['amount'] - $item['taxrate'];
            // debit Expense Account
            if ($item['type'] == 'Expense') {
                $account_id = $item['item_id'];
                // on project expense
                if ($item['itemproject_id']) {
                    $account_id = $wip_account->id;
                    $cr_data['ref_ledger_id'] = $item['item_id'];
                }
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $account_id,
                    'debit' => $subttl,
                ]);
            }
            //  debit Asset Account
            if ($item['type'] == 'Asset') {
                $asset = Assetequipment::find($item['item_id']);
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $asset->account_id,
                    'debit' => $subttl,
                ]);
            }
        }
        // debit tax (VAT)
        $account = Account::where('system', 'tax')->first(['id']);
        $dr_data[] = array_replace($cr_data, [
            'account_id' => $account->id, 
            'debit' => $bill['grandtax'],
        ]);
        Transaction::insert($dr_data); 
        
        // update account ledgers debit and credit totals
        aggregate_account_transactions();
    }
}
