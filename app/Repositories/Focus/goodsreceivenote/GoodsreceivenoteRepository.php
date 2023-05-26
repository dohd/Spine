<?php

namespace App\Repositories\Focus\goodsreceivenote;

use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\items\GoodsreceivenoteItem;
use App\Models\items\UtilityBillItem;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * Class ProductcategoryRepository.
 */
class GoodsreceivenoteRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Goodsreceivenote::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('supplier_id'), function ($q) {
            $q->where('supplier_id', request('supplier_id'));
        })->when(request('invoice_status'), function ($q) {
            switch (request('invoice_status')) {
                case 'with_invoice':
                    $q->whereNotNull('invoice_no');
                    break;
                case 'without_invoice':
                    $q->whereNull('invoice_no');
                    break;
            }
        });
        
        return $q;
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();
        
        foreach ($input as $key => $val) {
            if (in_array($key, ['date', 'invoice_date'])) $input[$key] = date_for_database($val);
            if (in_array($key, ['tax_rate', 'subtotal', 'tax', 'total'])) 
                $input[$key] = numberClean($val);
            if (in_array($key, ['qty', 'rate'])) 
                $input[$key] = array_map(fn($v) => numberClean($v), $val);
        }

        $tid = Goodsreceivenote::max('tid');
        if ($input['tid'] <= $tid) $input['tid'] = $tid+1;
        $result = Goodsreceivenote::create($input);

        // grn items
        $data_items = Arr::only($input, ['qty', 'rate', 'purchaseorder_item_id', 'item_id']);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['qty'] > 0);
        if (!$data_items) throw ValidationException::withMessages(['Cannot generate GRN without product qty!']);

        foreach ($data_items as $i => $item) {
            $data_items[$i] = array_replace($item, [
                'goods_receive_note_id' => $result->id,
                'tax_rate' => $result->tax_rate
            ]);
        }
        GoodsreceivenoteItem::insert($data_items);
        
        // increase stock qty
        foreach ($result->items as $i => $item) {
            $po_item = $item->purchaseorder_item;
            if (!$po_item) throw ValidationException::withMessages(['Line ' . strval($i+1) . ' related purchase order item does not exist!']);
            $po_item->increment('qty_received', $item->qty);

            $prod_variation = $po_item->productvariation;
            if (isset($prod_variation->product->units)) {
                foreach ($prod_variation->product->units as $unit) {
                    if ($unit->code == $po_item['uom']) {
                        if ($unit->unit_type == 'base') {
                            $prod_variation->increment('qty', $item->qty);
                        } else {
                            $converted_qty = $item->qty * $unit->base_ratio;
                            $prod_variation->increment('qty', $converted_qty);
                        }
                    }
                }
            } elseif ($prod_variation) $prod_variation->increment('qty', $item->qty);
            else throw ValidationException::withMessages(['Product on line ' . strval($i+1) . ' may not exist! Please update it from the Purchase Order number ' . $po_item->purchaseorder->tid]);
        }

        // update purchase order status
        $received_goods_qty = $result->items->sum('qty');
        if ($result->purchaseorder) {
            $order_goods_qty = $result->purchaseorder->items->sum('qty');
            if ($received_goods_qty == 0) $result->purchaseorder->update(['status' => 'Pending']);
            elseif (round($received_goods_qty) < round($order_goods_qty)) $result->purchaseorder->update(['status' => 'Partial']);
            else $result->purchaseorder->update(['status' => 'Complete']);
        } else throw ValidationException::withMessages(['Purchase order does not exist!']);

        /**accounting */
        if ($result->invoice_no) $this->generate_bill($result); // generate bill
        else $this->post_transaction($result); 
        
        if ($result) {
            DB::commit();
            return $result;
        }

        throw new GeneralException('Error Creating Lead');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     * @param  array $input
     * @throws GeneralException
     * @return \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     */
    public function update(Goodsreceivenote $goodsreceivenote, array $input)
    {
        // dd($input);
        DB::beginTransaction();
        // sanitize
        foreach ($input as $key => $val) {
            if (in_array($key, ['date', 'invoice_date'])) $input[$key] = date_for_database($val);
            if (in_array($key, ['tax_rate', 'subtotal', 'tax', 'total'])) 
                $input[$key] = numberClean($val);
            if (in_array($key, ['qty', 'rate'])) 
                $input[$key] = array_map(fn($v) => numberClean($v), $val);
        }

        $prev_note = $goodsreceivenote->note;
        $result = $goodsreceivenote->update($input);

        // reverse previous stock qty
        foreach ($goodsreceivenote->items as $i => $item) {
            $po_item = $item->purchaseorder_item;
            if (!$po_item) throw ValidationException::withMessages(['Line ' . strval($i+1) . ' related purchase order item does not exist!']);
            $po_item->decrement('qty_received', $item->qty);

            // apply unit conversion
            $prod_variation = $po_item->productvariation;
            if (isset($prod_variation->product->units)) {
                foreach ($prod_variation->product->units as $unit) {
                    if ($unit->code == $po_item['uom']) {
                        if ($unit->unit_type == 'base') {
                            $prod_variation->decrement('qty', $item->qty);
                        } else {
                            $converted_qty = $item->qty * $unit->base_ratio;
                            $prod_variation->decrement('qty', $converted_qty);
                        }
                    }
                }   
            } elseif ($prod_variation) $prod_variation->decrement('qty', $item->qty);      
            else throw ValidationException::withMessages(['Product on line ' . strval($i+1) . ' may not exist! Please update it from the Purchase Order No. ' . $po_item->purchaseorder->tid]);     
        }

        // update goods receive note items
        $data_items = Arr::only($input, ['qty', 'rate', 'id']);
        $data_items = modify_array($data_items);
        foreach ($data_items as $item) {
            $grn_item = GoodsreceivenoteItem::find($item['id']);
            if (!$grn_item) throw ValidationException::withMessages(['GRN item does not exist!']);
            $grn_item->decrement('qty', $grn_item->qty); 
            $grn_item->update($item);
            if ($grn_item->qty == 0) $grn_item->delete();
        }

        // increase stock qty with new update 
        $grn_items = $goodsreceivenote->items()->get();
        foreach ($grn_items as $item) {
            $po_item = $item->purchaseorder_item;
            if (!$po_item) throw ValidationException::withMessages(['Line ' . strval($i+1) . ' related purchase order item does not exist!']);
            $po_item->increment('qty_received', $item->qty);
            
            // apply unit conversion
            $prod_variation = $po_item->productvariation;
            if (isset($prod_variation->product->units)) {
                foreach ($prod_variation->product->units as $unit) {
                    if ($unit->code == $po_item['uom']) {
                        if ($unit->unit_type == 'base') {
                            $prod_variation->increment('qty', $item->qty);
                        } else {
                            $prod_variation->increment('qty', $item->qty * $unit->base_ratio);
                        }
                    }
                }   
            } elseif ($prod_variation) $prod_variation->increment('qty', $item->qty);
            else throw ValidationException::withMessages(['Product on line ' . strval($i+1) . ' may not exist! Please update it from the Purchase Order number ' . $po_item->purchaseorder->tid]);  
        }

        // update purchase order status
        if (!$goodsreceivenote->purchaseorder) throw ValidationException::withMessages(['Purchase Order does not exist!']);
        $order_goods_qty = $goodsreceivenote->purchaseorder->items->sum('qty');
        $received_goods_qty = $grn_items->sum('qty');
        if ($received_goods_qty == 0) $goodsreceivenote->purchaseorder->update(['status' => 'Pending']);
        elseif (round($received_goods_qty) < round($order_goods_qty)) $goodsreceivenote->purchaseorder->update(['status' => 'Partial']);
        else $goodsreceivenote->purchaseorder->update(['status' => 'Complete']); 
        
        $goodsreceivenote->prev_note = $prev_note;

        /**accounting */
        if ($goodsreceivenote->invoice_no) {
            // generate bill
            $this->generate_bill($goodsreceivenote); 
        } else {
            // grn transaction
            Transaction::where(['tr_type' => 'grn', 'tr_ref' => $goodsreceivenote->id, 'note' => $goodsreceivenote->prev_note])->delete();
            $this->post_transaction($goodsreceivenote);
        }

        if ($result) {
            DB::commit();
            return $result;
        }
        
        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     * @throws GeneralException
     * @return bool
     */
    public function delete(Goodsreceivenote $goodsreceivenote)
    {     
        DB::beginTransaction();

        $grn_bill = $goodsreceivenote->bill;
        if ($grn_bill) throw ValidationException::withMessages(['Goods Receive Note is attached to Bill No. ' . gen4tid('', $grn_bill->tid)]);

        // decrease inventory stock 
        foreach ($goodsreceivenote->items as $item) {
            $po_item = $item->purchaseorder_item;
            if ($po_item) {
                $po_item->decrement('qty_received', $item->qty);
                // apply unit conversion
                $prod_variation = $po_item->productvariation;
                if (isset($prod_variation->product->units)) {
                    foreach ($prod_variation->product->units as $unit) {
                        if ($unit->code == $po_item['uom']) {
                            if ($unit->unit_type == 'base') {
                                $prod_variation->decrement('qty', $item->qty);
                            } else {
                                $prod_variation->decrement('qty', $item->qty * $unit->base_ratio);
                            }
                        }
                    }   
                } elseif ($prod_variation) $prod_variation->decrement('qty', $item->qty);
            }
        }

        // delete received items
        $goodsreceivenote->items()->delete();

        // update purchase order status
        if ($goodsreceivenote->purchaseorder) {
            $order_goods_qty = $goodsreceivenote->purchaseorder->items->sum('qty');
            $received_goods_qty = $goodsreceivenote->items()->sum('qty');
            if ($received_goods_qty == 0) $goodsreceivenote->purchaseorder->update(['status' => 'Pending']);
            elseif (round($received_goods_qty) < round($order_goods_qty)) $goodsreceivenote->purchaseorder->update(['status' => 'Partial']);
            else $goodsreceivenote->purchaseorder->update(['status' => 'Complete']); 
        }
        
        // clear transactions
        $goodsreceivenote->transactions()->delete();
        aggregate_account_transactions();
          
        if ($goodsreceivenote->delete()) {
            DB::commit(); 
            return true;
        }
  
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }


    /**
     * Generate Bill For Goods Receive with invoice
     * 
     * @param Goodsreceivenote $grn
     * @return void
     */
    public function generate_bill($grn)
    {
        $bill_data = [
            'supplier_id' => $grn->supplier_id,
            'reference' => $grn->invoice_no,
            'reference_type' => 'invoice',
            'document_type' => 'goods_receive_note',
            'ref_id' => $grn->id,
            'date' => $grn->invoice_date,
            'due_date' => $grn->invoice_date,
            'subtotal' => $grn->subtotal,
            'tax_rate' => $grn->tax_rate,
            'tax' => $grn->tax,
            'total' => $grn->total,
            'note' => $grn->note,
        ];

        $grn_items = $grn->items()->get()->map(fn($v) => [
            'ref_id' => $v->id,
            'note' => $v->purchaseorder_item? $v->purchaseorder_item->description : '',
            'qty' => $v->qty,
            'subtotal' => $v->qty * $v->rate,
            'tax' => $v->qty * $v->rate * ($v->tax_rate / 100),
            'total' => $v->qty * $v->rate * (1 + $v->tax_rate / 100)
        ])->toArray();       
        
        $bill = UtilityBill::where(['ref_id' => $grn->id, 'document_type' => 'goods_receive_note'])->first();
        if ($bill) {
            // update bill
            $bill->update($bill_data);
            foreach ($grn_items as $item) {
                $new_item = UtilityBillItem::firstOrNew(['bill_id' => $bill->id,'ref_id' => $item['ref_id']]);
                $new_item->fill($item);
                $new_item->save();
            }

            // accounting
            Transaction::where(['tr_type' => 'bill', 'tr_ref' => $bill->id, 'note' => $bill->prev_note])->delete();
        } else {
            // create bill
            $bill_data['tid'] = UtilityBill::where('ins', auth()->user()->ins)->max('tid') + 1;
            $bill = UtilityBill::create($bill_data);

            $bill_items_data = array_map(function ($v) use($bill) {
                $v['bill_id'] = $bill->id;
                return $v;
            }, $grn_items);
            UtilityBillItem::insert($bill_items_data);
        }        
        // accounting
        $this->invoiced_grn_transaction($bill);
    }

    /**
     * Post Goods Received With Invoice Transactions
     */
    public function invoiced_grn_transaction($utility_bill)
    {
        // debit Inventory Account
        $account = Account::where('system', 'stock')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'bill')->first(['id', 'code']);
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
        $dr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'debit' => $utility_bill->subtotal,
            'tr_date' => $utility_bill->date,
            'due_date' => $utility_bill->due_date,
            'user_id' => $utility_bill->user_id,
            'note' => $utility_bill->note,
            'ins' => $utility_bill->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $utility_bill->id,
            'user_type' => 'supplier',
            'is_primary' => 1
        ];
        Transaction::create($dr_data);

        // debit TAX
        if ($utility_bill->tax > 0) {
            $account = Account::where('system', 'tax')->first(['id']);
            $cr_data = array_replace($dr_data, [
                'account_id' => $account->id,
                'debit' => $utility_bill->tax,
            ]);
            Transaction::create($cr_data);
        }

        // credit Accounts Payable (creditors)
        unset($dr_data['debit'], $dr_data['is_primary']);
        $account = Account::where('system', 'payable')->first(['id']);
        $cr_data = array_replace($dr_data, [
            'account_id' => $account->id,
            'credit' => $utility_bill->total,
        ]);    
        Transaction::create($cr_data);
        aggregate_account_transactions();
    }

    /**
     * Post Goods Received Account transactions
     * 
     * @param \App\Models\goodsreceivenote\Goodsreceivenote $grn
     * @return void
     */
    public function post_transaction($grn)
    {
        // credit Uninvoiced Goods Received Note (liability)
        $account = Account::where('system', 'grn')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'grn')->first(['id', 'code']);
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $grn->subtotal,
            'tr_date' => $grn->date,
            'due_date' => $grn->date,
            'user_id' => $grn->user_id,
            'note' => $grn->note,
            'ins' => $grn->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $grn->id,
            'user_type' => 'supplier',
            'is_primary' => 1
        ];
        Transaction::create($cr_data);

        // debit Inventory (Stock) Account
        unset($cr_data['credit'], $cr_data['is_primary']);
        $account = Account::where('system', 'stock')->first(['id']);
        $dr_data = array_replace($cr_data, [
            'account_id' => $account->id,
            'debit' => $grn->subtotal,
        ]);    
        Transaction::create($dr_data);
        aggregate_account_transactions();
    }
}