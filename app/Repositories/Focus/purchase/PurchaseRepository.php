<?php

namespace App\Repositories\Focus\purchase;

use App\Models\purchase\Purchase;
use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\assetequipment\Assetequipment;
use App\Models\items\PurchaseItem;
use App\Models\product\ProductVariation;
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
        $q = $this->query()->whereNull('po_id');

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
        foreach ($data as $key => $val) {
            $rate_keys = [
                'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
                'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
            ];
            if (in_array($key, ['date', 'due_date'], 1)) 
                $data[$key] = date_for_database($val);
            if (in_array($key, $rate_keys, 1)) 
                $data[$key] = numberClean($val);
        }

        $tid = Purchase::max('tid');
        if ($data['tid'] <= $tid) $data['tid'] = $tid + 1;

        $result = Purchase::create($data);

        $data_items = $input['data_items'];
        foreach ($data_items as $i => $item) {
            foreach ($item as $key => $val) {
                if (in_array($key, ['rate', 'taxrate', 'amount'], 1))
                    $item[$key] = numberClean($val);
                if (isset($item['itemproject_id'])) $item['warehouse_id'] = null;
                if (isset($item['warehouse_id'])) $item['itemproject_id'] = null;
            }

            // append modified data_items
            $data_items[$i] = array_replace($item, [
                'ins' => $result->ins,
                'user_id' => $result->user_id,
                'bill_id' => $result->id
            ]);

            // increase product stock
            if ($item['type'] == 'Stock' && $item['warehouse_id']) {
                $prod_variation = Prod_Variation::find($item['item_id']);
                if ($prod_variation) {
                    if ($prod_variation->warehouse_id != $item['warehouse_id']) {
                        $is_similar = false;
                        $similar_products = Prod_Variation::where('id', '!=', $prod_variation->id)
                            ->where('name', 'LIKE', '%'. $prod_variation->name .'%')->get();
                        foreach ($similar_products as $s_product) {
                            if ($prod_variation->warehouse_id == $item['warehouse_id']) {
                                $is_similar = true;
                                $prod_variation = $s_product;
                                break;
                            }
                        }
                        if (!$is_similar) {
                            // new warehouse product variation
                            $new_wh_product = clone $prod_variation;
                            $new_wh_product->warehouse_id = $item['warehouse_id'];
                            unset($new_wh_product->id, $new_wh_product->qty);
                            $new_wh_product->save();
                            $prod_variation = $new_wh_product;
                        }
                    }

                    // apply unit conversion
                    $units = $prod_variation->product->units;
                    foreach ($units as $unit) {
                        if ($unit->code == $item['uom']) {
                            if ($unit->unit_type == 'base') {
                                $prod_variation->increment('qty', $item['qty']);
                            } else {
                                $converted_qty = $item['qty'] * $unit->base_ratio;
                                $prod_variation->increment('qty', $converted_qty);
                            }
                        }
                    }
                }                
            }
        }
        PurchaseItem::insert($data_items);

        /** accounting **/
        $this->post_transaction($result);

        DB::commit();
        if ($result) return $result;        
        
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
    public function update($purchase, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        foreach ($data as $key => $val) {
            $rate_keys = [
                'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
                'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
            ];
            if (in_array($key, ['date', 'due_date'], 1)) 
                $data[$key] = date_for_database($val);
            if (in_array($key, $rate_keys, 1)) 
                $data[$key] = numberClean($val);
        }
        $purchase->update($data);

        $data_items = $input['data_items'];

        // delete omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $purchase->items()->whereNotIn('id', $item_ids)->delete();

        // create or update purchase item
        foreach ($data_items as $item) {         
            $purchase_item = PurchaseItem::firstOrNew(['id' => $item['id']]);

            // update product stock
            if ($item['type'] == 'Stock' && $item['warehouse_id']) {
                $prod_variation = $purchase_item->product;
                if ($prod_variation) $prod_variation->decrement('qty', $purchase_item->qty);
                else $prod_variation = ProductVariation::find($item['item_id']);
            
                if ($prod_variation) {
                    if ($prod_variation->warehouse_id != $item['warehouse_id']) {   
                        $is_similar = false;
                        $similar_products = ProductVariation::where('id', '!=', $prod_variation->id)
                            ->where('name', 'LIKE', '%'. $prod_variation->name .'%')->get();
                        foreach ($similar_products as $s_product) {
                            if ($prod_variation->warehouse_id == $item['warehouse_id']) {
                                $is_similar = true;
                                $prod_variation = $s_product;
                                break;
                            }
                        }
                        if (!$is_similar) {
                            $new_product = clone $prod_variation;
                            $new_product->warehouse_id = $item['warehouse_id'];
                            unset($new_product->id, $new_product->qty);
                            $new_product->save();
                            $prod_variation = $new_product;
                        }
                    }

                    // apply unit conversion
                    $units = $prod_variation->product->units;
                    foreach ($units as $unit) {
                        if ($unit->code == $item['uom']) {
                            if ($unit->unit_type == 'base') {
                                $prod_variation->increment('qty', $item['qty']);
                            } else {
                                $converted_qty = $item['qty'] * $unit->base_ratio;
                                $prod_variation->increment('qty', $converted_qty);
                            }
                        }
                    }                    
                }
            }    

            $item = array_replace($item, [
                'ins' => $purchase->ins,
                'user_id' => $purchase->user_id,
                'bill_id' => $purchase->id,
                'rate' => numberClean($item['rate']),
                'taxrate' => numberClean($item['taxrate']),
                'amount' => numberClean($item['amount']),
            ]);   
            $purchase_item->fill($item);
            if (!$purchase_item->id) unset($purchase_item->id);
            if ($purchase_item->warehouse_id) unset($purchase_item->itemproject_id);
            elseif ($purchase_item->itemproject_id) unset($purchase_item->warehouse_id);
            $purchase_item->save();
        }

        /** accounts */
        $purchase->transactions()->where('note', $purchase->note)->delete();
        $this->post_transaction($purchase);

        DB::commit();
        if ($purchase) return $purchase;

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
        try {
            DB::beginTransaction();
            // decrease stock
            foreach ($purchase->items as $item) {
                if ($item->type != 'Stock') continue;
                $prod_variation = $item->productvariation;
                // apply unit conversion
                $units = $prod_variation->product->units;
                foreach ($units as $unit) {
                    if ($unit->code == $item['uom']) {
                        if ($unit->unit_type == 'base') {
                            $prod_variation->decrement('qty', $item['qty']);
                        } else {
                            $converted_qty = $item['qty'] * $unit->base_ratio;
                            $prod_variation->decrement('qty', $converted_qty);
                        }
                    }
                }     
            }
            // delete transactions
            $purchase->transactions()->where('note', $purchase->note)->delete();
            aggregate_account_transactions();
            $result = $purchase->delete();

            if ($result) {
                DB::commit();
                return $result;
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new GeneralException(trans('exceptions.backend.purchaseorders.delete_error'));
        }
    }

    // Account transaction
    private function post_transaction($bill) 
    {
        // credit Accounts Payable (Creditors)
        $account = Account::where('system', 'payable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'bill')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $bill->grandttl,
            'tr_date' => $bill->date,
            'due_date' => $bill->due_date,
            'user_id' => $bill->user_id,
            'note' => $bill->note,
            'ins' => $bill->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $bill->id,
            'user_type' => 'supplier',
            'is_primary' => 1,
        ];
        Transaction::create($cr_data);

        $dr_data = array();
        unset($cr_data['credit'], $cr_data['is_primary']);

        // debit Stock
        $wip_account = Account::where('system', 'wip')->first(['id']);
        $stock_exists = $bill->items()->where('type', 'Stock')->count();
        if ($stock_exists) {
            // if project stock, WIP account else Stock account
            $is_project_stock = $bill->items()->where('type', 'Stock')->where('itemproject_id', '>', 0)->count();
            if ($is_project_stock) {
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $wip_account->id,
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

        // debit Expense and Asset account
        foreach ($bill->items as $item) {
            $subttl = $item['amount'] - $item['taxrate'];
            // debit Expense 
            if ($item['type'] == 'Expense') {
                $account_id = $item['item_id'];
                // if project expense, use WIP account
                if ($item['itemproject_id']) 
                    $account_id = $wip_account->id;
                    
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $account_id,
                    'debit' => $subttl,
                ]);
            }
            //  debit Asset 
            if ($item['type'] == 'Asset') {
                $account_id = Assetequipment::find($item['item_id'])->account_id;
                // if project asset, use WIP account
                if ($item['itemproject_id']) 
                    $account_id = $wip_account->id;
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $account_id,
                    'debit' => $subttl,
                ]);
            }
        }

        // debit tax (VAT)
        if ($bill['grandtax'] > 0) {
            $account = Account::where('system', 'tax')->first(['id']);
            $dr_data[] = array_replace($cr_data, [
                'account_id' => $account->id, 
                'debit' => $bill['grandtax'],
            ]);
        }
        Transaction::insert($dr_data); 
        aggregate_account_transactions();
    }
}
