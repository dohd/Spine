<?php

namespace App\Repositories\Focus\projectstock;

use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\projectstock\Projectstock;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;

/**
 * Class ProductcategoryRepository.
 */
class ProjectStockRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Projectstock::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        // 
        
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return \App\Models\projectstock\Projectstock $projectstock
     */
    public function create(array $input)
    {
        dd($input);
        DB::beginTransaction();
        // sanitize
        foreach ($input as $key => $val) {
            if ($key == 'date') $input[$key] = date_for_database($val);
            if ($key == 'qty') $input[$key] = array_map(function ($v) { 
                return numberClean($v); 
            }, $val);
        }

        $input['user_id'] = auth()->user()->id;
        $input['ins'] = auth()->user()->ins;
        $result = Projectstock::create($input);

        $data_items = Arr::only($input, ['qty', 'purchaseorder_item_id', 'item_id']);
        $data_items = array_filter(modify_array($data_items), function ($v) {
            return $v['qty'] > 0;
        });
        $data_items = array_map(function ($v) use($result) {
            $v['goods_receive_note_id'] = $result->id;
            return $v;
        }, $data_items);
        ProjectstockItem::insert($data_items);
        
        // increase stock qty
        foreach ($result->items as $item) {
            $po_item = $item->purchaseorder_item;
            $po_item->increment('qty_received', $item->qty);
            // stock subtotal amount
            $result->subtotal += ($item->qty * $po_item->rate / $po_item->qty);
            // apply unit conversion
            $variation = $item->productvariation;
            $unit = $variation->product->unit;
            if ($unit->base_unit == $po_item->uom) {
                $variation->increment('qty', $item->qty);
            } elseif ($unit->compound_unit == $po_item->uom) {
                $qty = $unit->bas_ratio * $po_item->qty;
                $variation->increment('qty', $qty);
            } 
        }

        // update purchase order status
        $received_goods_qty = $result->items->sum('qty');
        $order_goods_qty = $result->purchaseorder->items->sum('qty');
        if ($order_goods_qty > $received_goods_qty) $result->purchaseorder->update(['status' => 'open']);
        else $result->purchaseorder->update(['status' => 'close']);

        /**accounting */
        $this->post_transaction($result);

        DB::commit();
        if ($result) return $result;


        throw new GeneralException('Error Creating Lead');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param \App\Models\projectstock\Projectstock $projectstock
     * @param  array $input
     * @throws GeneralException
     * @return \App\Models\projectstock\Projectstock $projectstock
     */
    public function update(Projectstock $projectstock, array $input)
    {
        dd($input);

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\projectstock\Projectstock $projectstock
     * @throws GeneralException
     * @return bool
     */
    public function delete(Projectstock $projectstock)
    {     
        DB::beginTransaction();
        dd($projectstock);
        // reduce stock qty
        foreach ($projectstock->items as $item) {
            $po_item = $item->purchaseorder_item;
            $po_item->decrement('qty_received', $item->qty);
            // stock subtotal amount
            $projectstock->subtotal += ($item->qty * $po_item->rate / $po_item->qty);
            // apply unit conversion
            $variation = $item->productvariation;
            $unit = $variation->product->unit;
            if ($unit->base_unit == $po_item->uom) {
                $variation->decrement('qty', $item->qty);
            } elseif ($unit->compound_unit == $po_item->uom) {
                $qty = $unit->bas_ratio * $po_item->qty;
                $variation->decrement('qty', $qty);
            } 
        }

        // update purchase order status
        $received_goods_qty = $projectstock->items->sum('qty');
        $order_goods_qty = $projectstock->purchaseorder->items->sum('qty');
        if ($order_goods_qty > $received_goods_qty) $projectstock->purchaseorder->update(['status' => 'open']);
        else $projectstock->purchaseorder->update(['status' => 'close']);

        $result = $projectstock->delete();
        if ($result) {
            DB::commit(); 
            return true;
        }
                
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }

    /**
     * Post Goods Received Account transactions
     * @param \App\Models\projectstock\Projectstock $projectstock
     * @return void
     */
    public function post_transaction($projectstock)
    {
        // credit Uninvoiced Goods Received Note (liability)
        $account = Account::where('system', 'grn')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'grn')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $projectstock->total,
            'tr_date' => $projectstock->date,
            'due_date' => $projectstock->date,
            'user_id' => $projectstock->user_id,
            'note' => $projectstock->note,
            'ins' => $projectstock->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $projectstock->id,
            'user_type' => 'supplier',
            'is_primary' => 1
        ];
        Transaction::create($cr_data);

        // debit Inventory (Stock) Account
        unset($cr_data['credit'], $cr_data['is_primary']);
        $account = Account::where('system', 'stock')->first(['id']);
        $dr_data = array_replace($cr_data, [
            'account_id' => $account->id,
            'debit' => $projectstock->total,
        ]);    
        Transaction::create($dr_data);
    }
}