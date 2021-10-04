<?php

namespace App\Repositories\Focus\makepayment;


use App\Models\items\PurchaseItem;
use App\Models\purchase\Purchase;
use App\Models\account\Account;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

use App\Models\items\CustomEntry;
use App\Models\items\InvoiceItem;
use App\Models\product\ProductVariation;
use Illuminate\Support\Facades\DB;
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;

/**
 * Class PurchaseorderRepository.
 */
class MakepaymentRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Makepayment::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        $q=$this->query();
       $q->when(request('i_rel_type')==1, function ($q) {

            return $q->where('supplier_id', '=',request('i_rel_id',0));
        });

        return
            $q->get();
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


       $purchases_trans_category_id = Transactioncategory::where('code', 'bc_transactions')->first();
       $purchases_trans_category_id=$purchases_trans_category_id->id;
        $input['invoice']['bill_id'] = $input['invoice']['id'];
        $input['invoice']['payer_id'] = $input['invoice']['payer_id'];
        $input['invoice']['tid'] = $input['invoice']['tid'];
        $input['invoice']['refer_no'] = $input['invoice']['refer_no'];
        $input['invoice']['method'] = $input['invoice']['method'];
        $input['invoice']['note'] = strip_tags(@$input['invoice']['note']);
        $input['invoice']['trans_category_id'] = $purchases_trans_category_id;
        $input['invoice']['account_id'] = $input['invoice']['account_id'];
        $input['invoice']['transaction_type'] ='purchases';
       

      
       

        DB::beginTransaction();
        $input['invoice'] = array_map( 'strip_tags', $input['invoice']);
        $result = Purchase::create($input['invoice']);


        if ($result) {
                

        
$update_value = [
     [
         'id' => $input['invoice']['id'],
         'total_paid_amount' => $input['invoice']['credit'],
         
     ] ,
     
];
$purchase = new Purchase;
$index = 'id';

Batch::update($purchase, $update_value, $index, true,'+');



                
         //begin debit entry for payment

        $invoice_d = Purchase::where('id',$input['debit_entry']['id'])->first();
        $input['debit_entry']['account_id'] =$invoice_d->account_id;
        $input['debit_entry']['trans_category_id'] =$invoice_d->trans_category_id;
        $input['debit_entry']['bill_id'] = $input['debit_entry']['id'];
        $input['debit_entry']['tid'] = $input['debit_entry']['tid'];
        $input['debit_entry']['refer_no'] = $input['debit_entry']['refer_no'];
        $input['debit_entry']['method'] = $input['debit_entry']['method'];
        $input['debit_entry']['note'] = strip_tags(@$input['debit_entry']['note']);
        $input['debit_entry']['second_trans'] = 1;
        $input['debit_entry']['transaction_type'] ='purchases';
        
         Purchase::create($input['debit_entry']);




            DB::commit();
            return $result;
        }
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
    public function update(Purchaseorder $purchaseorder, array $input)
    {
    	$id = $input['invoice']['id'];
        $extra_discount = numberClean($input['invoice']['after_disc']);
        $input['invoice']['invoicedate'] = date_for_database($input['invoice']['invoicedate']);
        $input['invoice']['invoiceduedate'] = date_for_database($input['invoice']['invoiceduedate']);
        $input['invoice']['subtotal'] = numberClean($input['invoice']['subtotal']);
        $input['invoice']['shipping'] = numberClean($input['invoice']['shipping']);
        $input['invoice']['discount_rate'] = numberClean($input['invoice']['discount_rate']);
        $input['invoice']['after_disc'] = numberClean($input['invoice']['after_disc']);
        $input['invoice']['total'] = numberClean($input['invoice']['total']);
        $input['invoice']['ship_tax_rate'] = numberClean($input['invoice']['ship_rate']);
        $input['invoice']['ship_tax'] = numberClean($input['invoice']['ship_tax']);
        $input['invoice']['extra_discount'] = $extra_discount;
        $total_discount = $extra_discount;
        $re_stock = @$input['invoice']['restock'];
        unset($input['invoice']['after_disc']);
        unset($input['invoice']['ship_rate']);
        unset($input['invoice']['id']);
        unset($input['invoice']['restock']);
        $result = Purchaseorder::find($id);
        if ($result->status == 'canceled') return false;
         $input['invoice'] = array_map( 'strip_tags', $input['invoice']);
        $result->update($input['invoice']);
        if ($result) {
            PurchaseItem::where('bill_id', $id)->delete();
            $products = array();
            $subtotal = 0;
            $total_qty = 0;
            $total_tax = 0;
            foreach ($input['invoice_items']['product_id'] as $key => $value) {
                $subtotal += numberClean(@$input['invoice_items']['product_price'][$key]) * numberClean(@$input['invoice_items']['product_qty'][$key]);
                $qty=numberClean($input['invoice_items']['product_qty'][$key]);
                $old_qty=numberClean(@$input['invoice_items']['old_product_qty'][$key]);
                $total_qty += $qty;
                $total_tax += numberClean(@$input['invoice_items']['product_tax'][$key]);
                $total_discount += numberClean(@$input['invoice_items']['total_discount'][$key]);
                $products[] = array('bill_id' => $id,
                    'product_id' => $input['invoice_items']['product_id'][$key],
                    'product_name' => strip_tags(@$input['invoice_items']['product_name'][$key]),
                    'code' => @$input['invoice_items']['code'][$key],
                    'product_qty' => numberClean(@$input['invoice_items']['product_qty'][$key]),
                    'product_price' => numberClean(@$input['invoice_items']['product_price'][$key]),
                    'product_tax' => numberClean(@$input['invoice_items']['product_tax'][$key]),
                    'product_discount' => numberClean(@$input['invoice_items']['product_discount'][$key]),
                    'product_subtotal' => numberClean(@$input['invoice_items']['product_subtotal'][$key]),
                    'total_tax' => numberClean(@$input['invoice_items']['total_tax'][$key]),
                    'total_discount' => numberClean(@$input['invoice_items']['total_discount'][$key]),
                    'product_des' => strip_tags(@$input['invoice_items']['product_description'][$key],config('general.allowed')),
                    'i_class' => 0,
                    'unit' => $input['invoice_items']['unit'][$key], 'ins' => $input['invoice']['ins']);

                if($old_qty>0){
                     $stock_update[] = array('id' => $input['invoice_items']['product_id'][$key], 'qty' => $qty-$old_qty);
                }
                else {
                      $stock_update[] = array('id' => $input['invoice_items']['product_id'][$key], 'qty' => $qty);
                }
            }
            PurchaseItem::insert($products);
            $invoice_d = Purchaseorder::find($id);
            $invoice_d->subtotal = $subtotal;
            $invoice_d->tax = $total_tax;
            $invoice_d->discount = $total_discount;
            $invoice_d->items = $total_qty;
            $invoice_d->save();
            if (isset($input['data2']['custom_field'])) {
                foreach ($input['data2']['custom_field'] as $key => $value) {
                    $fields[] = array('custom_field_id' => $key, 'rid' => $id, 'module' => 9, 'data' => strip_tags($value), 'ins' => $input['invoice']['ins']);
                    CustomEntry::where('custom_field_id', '=', $key)->where('rid', '=', $id)->delete();
                }
                CustomEntry::insert($fields);
            }
            $update_variation = new ProductVariation;
            $index = 'id';
            Batch::update($update_variation, $stock_update, $index, true);
            if (is_array($re_stock)) {
                $stock_update_one=array();
                foreach ($re_stock as $key => $value) {
                    $myArray = explode('-', $value);
                    $s_id = $myArray[0];
                    $s_qty = numberClean($myArray[1]);
                    if ($s_id) $stock_update_one[] = array('id' => $s_id, 'qty' => $s_qty);
                }
            Batch::update($update_variation, $stock_update_one, $index, true, '+');
            }
            DB::commit();
            return $result;
        }
        throw new GeneralException(trans('exceptions.backend.purchaseorders.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Purchaseorder $purchaseorder
     * @throws GeneralException
     * @return bool
     */
    public function delete(Purchaseorder $purchaseorder)
    {
        if ($purchaseorder->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.purchaseorders.delete_error'));
    }
}
