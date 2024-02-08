<?php

namespace App\Repositories\Focus\goodsreceivenote;

use App\Exceptions\GeneralException;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\items\GoodsreceivenoteItem;
use App\Models\items\UtilityBillItem;
use App\Models\product\ProductVariation;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * Class GoodsreceivenoteRepository.
 */
class GoodsreceivenoteRepository extends BaseRepository
{
    use Accounting;
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

        // supplier user filter
        $supplier_id = auth()->user()->supplier_id;
        $q->when($supplier_id, fn($q) => $q->where('supplier_id', $supplier_id));

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
        if (!$data_items) throw ValidationException::withMessages(['Cannot generate GRN without assigning product qty!']);
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
            
            // check if is default product variation or supplier product 
            $prod_variation = $item->productvariation;
            if (@$result->purchaseorder->pricegroup_id && $item->supplier_product) {
                $prod_variation = ProductVariation::where('code', $item->supplier_product->product_code)->first();
            }
            // unit conversion
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

        /** accounting */
        if ($result->invoice_no) {
            $bill = $this->generate_bill($result);
            $this->post_invoiced_grn_bill($bill);
        } else {
            $this->post_uninvoiced_grn($result); 
        }
        
        if ($result) {
            DB::commit();
            return $result;
        }
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

        $result = $goodsreceivenote->update($input);

        // reverse previous stock qty
        foreach ($goodsreceivenote->items as $i => $item) {
            $po_item = $item->purchaseorder_item;
            if (!$po_item) throw ValidationException::withMessages(['Line ' . strval($i+1) . ' related purchase order item does not exist!']);
            $po_item->decrement('qty_received', $item->qty);

            // check if is default product variation or supplier product 
            $prod_variation = $item->productvariation;
            if (@$goodsreceivenote->purchaseorder->pricegroup_id && $item->supplier_product) {
                $prod_variation = ProductVariation::where('code', $item->supplier_product->product_code)->first();
            }
            // apply unit conversion
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
            
            // check if is default product variation or supplier product 
            $prod_variation = $item->productvariation;
            if (@$goodsreceivenote->purchaseorder->pricegroup_id && $item->supplier_product) {
                $prod_variation = ProductVariation::where('code', $item->supplier_product->product_code)->first();
            }
            // apply unit conversion
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
        

        /**accounting */
        if ($goodsreceivenote->invoice_no) {
            $bill = $this->generate_bill($goodsreceivenote); 
            $this->post_invoiced_grn_bill($bill);
        } else {
            $goodsreceivenote->transactions()->delete();
            $this->post_uninvoiced_grn($goodsreceivenote);
        }

        if ($result) {
            DB::commit();
            return $result;
        }
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
        if ($grn_bill) {
            if ($grn_bill->payments()->exists()) 
                throw ValidationException::withMessages(['Not Allowed! Goods Receive Note is billed on Bill No. '. gen4tid('', $grn_bill->tid). 'with associated payments']);
            $grn_bill->transactions()->delete();
            $grn_bill->items()->delete();
            $grn_bill->delete();
        }

        // decrease inventory stock 
        foreach ($goodsreceivenote->items as $item) {
            $po_item = $item->purchaseorder_item;
            if ($po_item) {
                $po_item->decrement('qty_received', $item->qty);
                // check if is default product variation or supplier product 
                $prod_variation = $item->productvariation;
                if (@$goodsreceivenote->purchaseorder->pricegroup_id && $item->supplier_product) {
                    $prod_variation = ProductVariation::where('code', $item->supplier_product->product_code)->first();
                }
                // apply unit conversion
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
        
        $goodsreceivenote->transactions()->delete();
        if ($goodsreceivenote->delete()) {
            DB::commit(); 
            return true;
        }
    }

    /**
     * Generate Bill For Goods Receive with invoice
     * 
     * @param Goodsreceivenote $grn
     * @return void
     */
    public function generate_bill($grn)
    {
        $grn_items = $grn->items()->get()
        ->map(fn($v) => [
            'ref_id' => $v->id,
            'note' => @$v->purchaseorder_item->description ?: '',
            'qty' => $v->qty,
            'subtotal' => $v->qty * $v->rate,
            'tax' => $v->qty * $v->rate * ($v->tax_rate / 100),
            'total' => $v->qty * $v->rate * (1 + $v->tax_rate / 100)
        ])
        ->toArray();       
        
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
        $bill = UtilityBill::where(['ref_id' => $grn->id, 'document_type' => 'goods_receive_note'])->first();
        if ($bill) {
            // update bill
            $bill->update($bill_data);
            foreach ($grn_items as $item) {
                $new_item = UtilityBillItem::firstOrNew(['bill_id' => $bill->id,'ref_id' => $item['ref_id']]);
                $new_item->fill($item);
                $new_item->save();
            }       
            $bill->transactions()->delete();     
        } else {
            // create bill
            $bill_data['tid'] = UtilityBill::max('tid')+1;
            $bill = UtilityBill::create($bill_data);
            // bill items
            $bill_items_data = array_map(function ($v) use($bill) {
                $v['bill_id'] = $bill->id;
                return $v;
            }, $grn_items);
            UtilityBillItem::insert($bill_items_data);
        }        
        return $bill;
    }        
}