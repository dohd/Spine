<?php

namespace App\Repositories\Focus\invoice;

use App\Models\account\Account;
use App\Models\Company\ConfigMeta;
use App\Models\invoice\Draft;
use App\Models\items\CustomEntry;
use App\Models\items\DraftItem;
use App\Models\items\InvoiceItem;
use App\Models\invoice\Invoice;
use App\Exceptions\GeneralException;
use App\Models\invoice\PaidInvoice;
use App\Models\items\PaidInvoiceItem;
use App\Models\items\Register;
use App\Models\product\ProductVariation;
use App\Models\transaction\Transaction;
use App\Models\transaction\TransactionHistory;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\purchase\Purchase;
use App\Models\quote\Quote;
use App\Models\transactioncategory\Transactioncategory;
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;

/**
 * Class InvoiceRepository.
 */
class InvoiceRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Invoice::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('i_rel_type') == 1, function ($q) {
            return $q->where('customer_id', '=', request('i_rel_id', 0));
        });

        if (request('project_id')) {
            $q->whereHas('project', function ($sq) {
                return $sq->where('project_id', request('project_id', 0));
            });
        }

        if (request('sub') == 1) $q->where('i_class', '>', 1);
        if (request('sub') == 2) $q->where('i_class', 1);
        else $q->where('i_class', '<', 1);
        
        if (request('start_date') && request('end_date')) {
            $q->whereBetween('invoicedate', [
                date_for_database(request('start_date')), 
                date_for_database(request('end_date'))
            ]);
        }

        return $q->get(['id', 'tid', 'customer_id', 'invoicedate', 'invoiceduedate', 'total', 'status', 'notes', 'type']);
    }

    public function getSelfDataTable($self_id = false)
    {
        if ($self_id) {
            $q = $this->query()->withoutGlobalScopes();
            $q->where('customer_id', '=', $self_id);

            return
                $q->get(['id', 'tid', 'customer_id', 'invoicedate', 'invoiceduedate', 'total', 'status']);
        }
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @return bool
     * @throws GeneralException
     */
    public function create(array $input)
    {

        $purchases_trans_category_id = Transactioncategory::where('code', 'sales')->first();
        $purchases_trans_category_id = $purchases_trans_category_id->id;
        //invoices 
        $input['invoice']['tid'] = $input['invoice']['tid'];
        $input['invoice']['tax_id'] = $input['invoice']['tax_id'];
        $input['invoice']['discount_format'] = $input['invoice']['discount_format'];
        $input['invoice']['tax_format'] = $input['invoice']['tax_format'];
        $input['invoice']['discount_rate'] = $input['invoice']['discount_rate'];
        $input['invoice']['notes'] = $input['invoice']['notes'];



        DB::beginTransaction();

        $input['invoice'] = array_map('strip_tags', $input['invoice']);
        $result = Invoice::create($input['invoice']);

        if ($result) {

            //credited 
            $input['receivable']['payer_type'] = $input['receivable']['payer_type'];
            $input['receivable']['payer'] = $input['receivable']['payer'];
            $input['receivable']['payer_id'] = $input['receivable']['payer_id'];
            $input['receivable']['tid'] = $input['receivable']['tid'];
            $input['receivable']['trans_category_id'] = $purchases_trans_category_id;
            $input['receivable']['taxformat'] = $input['receivable']['taxformat'];
            $input['receivable']['discountformat'] = $input['receivable']['discountformat'];
            $input['receivable']['s_warehouses'] = $input['receivable']['s_warehouses'];
            $input['receivable']['is_bill'] = 0;
            $input['receivable']['transaction_type'] = 'sales';
            $input['receivable']['invoice_id'] = $result->id;

            $input['receivable'] = array_map('strip_tags', $input['receivable']);
            $transaction = Purchase::create($input['receivable']);


            if ($transaction) {


                //debited 
                $input['debit']['payer_type'] = $input['debit']['payer_type'];
                $input['debit']['payer'] = $input['debit']['payer'];
                $input['debit']['payer_id'] = $input['debit']['payer_id'];
                $input['debit']['tid'] = $input['debit']['tid'];
                $input['debit']['taxformat'] = $input['debit']['taxformat'];
                $input['debit']['trans_category_id'] = $purchases_trans_category_id;
                $input['debit']['discountformat'] = $input['debit']['discountformat'];
                $input['debit']['s_warehouses'] = $input['debit']['s_warehouses'];
                $input['debit']['is_bill'] = 2;
                $input['debit']['transaction_type'] = 'sales';
                $input['debit']['bill_id'] = $transaction->id;
                $input['debit']['invoice_id'] = $result->id;

                $input['debit'] = array_map('strip_tags', $input['debit']);
                Purchase::create($input['debit']);

                $products = array();
                $subtotal = 0;
                $total_qty = 0;
                $total_tax = 0;


                //purchase product
                if ($input['inventory_items']['product_name'] > 0) {


                    foreach ($input['inventory_items']['product_id'] as $key => $value) {
                        $products[] = array(
                            'invoice_id' => $result->id,
                            'product_id' => 0,
                            'ins' => $input['inventory_items']['ins'],
                            'product_name' => strip_tags(@$input['inventory_items']['product_name'][$key]),
                            'product_qty' => strip_tags(@$input['inventory_items']['product_qty'][$key]),
                            'product_subtotal' => numberClean(@$input['inventory_items']['product_subtotal'][$key]),
                            'product_price' => numberClean(@$input['inventory_items']['product_price'][$key]),
                            'taxable_amount' => numberClean(@$input['inventory_items']['taxedvalue'][$key]),
                            'total_tax' => numberClean(@$input['inventory_items']['total_tax'][$key]),
                            'product_tax' => numberClean(@$input['inventory_items']['product_tax'][$key]),
                            'product_discount' => numberClean(@$input['inventory_items']['product_discount'][$key]),
                            'total_discount' => numberClean(@$input['inventory_items']['total_discount'][$key]),
                            'product_des' => strip_tags(@$input['inventory_items']['product_description'][$key]),
                            'project_id' => numberClean(@$input['inventory_items']['inventory_project_id'][$key]),
                            'branch_id' => numberClean(@$input['inventory_items']['branch_id'][$key])

                        );
                    }


                    InvoiceItem::insert($products);
                }




                //end inventory items




                //begit tax
                if ($input['tax']['tax_amount'] > 0) {
                    $purchases_trans_category_id = Transactioncategory::where('code', 'p_taxes')->first();
                    $purchases_trans_category_id = $purchases_trans_category_id->id;
                    $account_id = Account::where('system', 'tax')->first();
                    $account_id = $account_id->id;
                    $input['tax']['account_id'] = $account_id;
                    $input['tax']['trans_category_id'] = $purchases_trans_category_id;
                    $input['tax']['secondary_account_id'] = $account_id;
                    $input['tax']['tax_type'] = 'sales_purchases';
                    $input['tax']['transaction_type'] = 'sales';
                    $input['tax']['invoice_id'] = $result->id;



                    $input['tax'] = array_map('strip_tags', $input['tax']);
                    Purchase::create($input['tax']);
                }

                DB::commit();
                return $transaction;
            }

            //end tax


        }
        throw new GeneralException(trans('exceptions.backend.invoices.create_error'));

        /*
        $extra_discount = numberClean($input['invoice']['after_disc']);
        $input['invoice']['invoicedate'] = date_for_database($input['invoice']['invoicedate']);
        $input['invoice']['subtotal'] = numberClean($input['invoice']['subtotal']);
        $input['invoice']['shipping'] = numberClean($input['invoice']['shipping']);
        $input['invoice']['discount_rate'] = numberClean($input['invoice']['discount_rate']);
        $input['invoice']['after_disc'] = numberClean($input['invoice']['after_disc']);
        $input['invoice']['total'] = numberClean($input['invoice']['total']);
        $input['invoice']['ship_tax_rate'] = numberClean($input['invoice']['ship_rate']);
        $input['invoice']['ship_tax'] = numberClean($input['invoice']['ship_tax']);
        $input['invoice']['extra_discount'] = $extra_discount;

        $total_discount = $extra_discount;
        if ($input['invoice']['sub']) {
            $input['invoice']['i_class'] = 2;
            $input['invoice']['r_time'] = $input['invoice']['recur_after'];
            $input['invoice']['invoiceduedate'] = date("Y-m-d", strtotime($input['invoice']['invoicedate'] . " +" . $input['invoice']['r_time'] . 's'));
            unset($input['invoice']['recur_after']);
        } else {
            $input['invoice']['invoiceduedate'] = date_for_database($input['invoice']['invoiceduedate']);
        }
        $p = @$input['invoice']['p'];
        unset($input['invoice']['after_disc']);
        unset($input['invoice']['ship_rate']);
        unset($input['invoice']['sub']);
        unset($input['invoice']['p']);

        if(!isset($input['invoice_items']['product_id'])){
           return false;
        }


        DB::beginTransaction();
         $input['invoice'] = array_map( 'strip_tags', $input['invoice']);
        $result = Invoice::create($input['invoice']);
        if ($result) {
            $products = array();
            $subtotal = 0;
            $total_qty = 0;
            $total_tax = 0;
            $stock_update = array();
            $serial_track = array();
            foreach ($input['invoice_items']['product_id'] as $key => $value) {

                $subtotal += numberClean(@$input['invoice_items']['product_price'][$key]) * numberClean(@$input['invoice_items']['product_qty'][$key]);
                $total_qty += numberClean(@$input['invoice_items']['product_qty'][$key]);
                $total_tax += numberClean(@$input['invoice_items']['total_tax'][$key]);
                $total_discount += numberClean(@$input['invoice_items']['total_discount'][$key]);
                if ($input['invoice_items']['serial'][$key]) $serial_track[] = array('rel_type' => 2, 'rel_id' => 1, 'ref_id' => $input['invoice_items']['product_id'][$key], 'value' => strip_tags($input['invoice_items']['serial'][$key]), 'value2' => $result->id);
                if ($input['invoice_items']['unit_m'][$key] > 1) {
                    $unit_val = $input['invoice_items']['unit_m'][$key];
                    $qty = $unit_val * numberClean($input['invoice_items']['product_qty'][$key]);
                } else {
                    $unit_val = 1;
                    $qty = numberClean($input['invoice_items']['product_qty'][$key]);
                }
                $products[] = array('invoice_id' => $result->id,
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
                    'unit_value' => $unit_val,
                    'serial' => @$input['invoice_items']['serial'][$key],
                    'i_class' => 0,
                    'unit' => $input['invoice_items']['unit'][$key], 'ins' => $result->ins);
                $stock_update[] = array('id' => $input['invoice_items']['product_id'][$key], 'qty' => $qty);
            }

            InvoiceItem::insert($products);
            $invoice_d = Invoice::find($result->id);
            $invoice_d->subtotal = $subtotal;
            $invoice_d->tax = $total_tax;
            $invoice_d->discount = $total_discount;
            $invoice_d->items = $total_qty;
            $invoice_d->save();
            if (@$result->id) {
                $fields = array();
                if (isset($input['data2']['custom_field'])) {
                    foreach ($input['data2']['custom_field'] as $key => $value) {
                        $fields[] = array('custom_field_id' => $key, 'rid' => $result->id, 'module' => 2, 'data' => strip_tags($value), 'ins' => $input['data2']['ins']);
                    }
                    CustomEntry::insert($fields);
                }
            }
            $update_variation = new ProductVariation;
            $index = 'id';
            Batch::update($update_variation, $stock_update, $index, true);
            $update_variation = new ProductMeta;
            $index = 'value';
            $index2 = 'ref_id';
            $out_s = $this->update_dual($update_variation, $serial_track, $index, $index2);
            if ($p > 0) {
                ProjectRelations::create(array('project_id' => $p, 'related' => 7, 'rid' => $result->id));
                $result['p'] = $p;
            }
            DB::commit();
            return $result;
        }
        throw new GeneralException(trans('exceptions.backend.invoices.create_error'));*/
    }

    /**
     * Create project invoice
     */
    public function create_project_invoice(array $input)
    {
        DB::beginTransaction();

        $bill = $input['bill'];
        foreach ($bill as $key => $val) {
            if (in_array($key, ['total', 'subtotal', 'tax', 'invoicedate'], 1)) {
                if ($key == 'invoicedate') $bill[$key] = date_for_database($val);                
                else $bill[$key] = numberClean($val);
            }
        }
        $duedate = $bill['invoicedate'] . ' + ' . $bill['validity'] . ' days';
        $bill['invoiceduedate'] = date_for_database($duedate);
        // increament tid
        $last_inv = Invoice::orderBy('id', 'DESC')->first('tid');
        if ($last_inv && $bill['tid'] <= $last_inv->tid) {
            $bill['tid'] = $last_inv->tid + 1;
        }
        $result = Invoice::create($bill);
        
        $bill_items = $input['bill_items'];
        foreach ($bill_items as $k => $item) {
            $bill_items[$k]['product_price'] = numberClean($item['product_price']);
        }
        InvoiceItem::insert($bill_items);

        // update Quote or PI invoice status
        Quote::whereIn('id', function($q) use ($result) {
            $q->select('quote_id')->from('invoice_items')->where('invoice_id', $result->id);
        })->update(['invoiced' => 'Yes']);
        
        /** accounting */
        // debit payable
        $account = Account::where('system', 'payable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'RCPT')->first(['id', 'code']);
        $dr_data = [
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'debit' => $result->total,
            'tr_date' => date('Y-m-d'),
            'due_date' => $result->invoiceduedate,
            'user_id' => $result->user_id,
            'note' => $result->notes,
            'ins' => $result->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $result->id,
            'user_type' => 'supplier',
            'is_primary' => 1
        ];
        Transaction::create($dr_data);

        // credit income and tax
        unset($dr_data['credit'], $dr_data['is_primary']);
        $income_cr_data = array_replace($dr_data, [
            'account_id' => $result->account_id,
            'credit' => $result->subtotal,
        ]);
        $account = Account::where('system', 'tax')->first(['id']);
        $tax_cr_data = array_replace($dr_data, [
            'account_id' => $account->id,
            'credit' => $result->tax,
        ]);
        Transaction::insert([$income_cr_data, $tax_cr_data]);

        // update account ledgers debit and credit totals
        $tr_totals = Transaction::where('tr_ref', $result->id)
            ->select(DB::raw('account_id as id, SUM(credit) as credit_ttl, SUM(debit) as debit_ttl'))
            ->groupBy('account_id')
            ->get()->toArray();
        Batch::update(new Account, $tr_totals, 'id');

        DB::commit();
        if ($result) return $result;

        throw new GeneralException('Error Creating Invoice');
    }

    /**
     * Create invoice payment
     */
    public function create_invoice_payment(array $input)
    {
        DB::beginTransaction();

        $bill = $input['bill'];
        foreach ($bill as $key => $val) {
            if (in_array($key, ['date'], 1)) {
                $bill[$key] = date_for_database($val);
            }
            if (in_array($key, ['amount_ttl', 'deposit_ttl'], 1)) {
                $bill[$key] = numberClean($val);
            }
        }
        $result = PaidInvoice::create($bill);

        $bill_items = $input['bill_items'];
        foreach ($bill_items as $k => $item) {
            $item = $item + ['paidinvoice_id' => $result->id];
            $item['paid'] = numberClean($item['paid']);
            $bill_items[$k] = $item;
        }
        PaidInvoiceItem::insert($bill_items);

        DB::commit();
        if ($result) return true;
    }


    private function update_dual(Model $table, array $values, string $index = null, $index2 = null)
    {
        $final = [];

        if (!count($values)) {
            return false;
        }

        $whr = '';
        foreach ($values as $key => $val) {

            $q = '';
            $i = 0;
            foreach (array_keys($val) as $field) {
                if ($field != $index and $field != $index2) {

                    if ($i < 2) $q .= $field . '=' . $val[$field] . ',';
                    if ($i == 2) $q .= $field . '=' . $val[$field] . ' ';

                    $i++;
                }
            }
            $whr .= "UPDATE `" . $table->getTable() . "` SET $q";
            $whr .= 'WHERE (`' . $index . '` = "' . $val[$index] . '" AND `' . $index2 . '` = "' . $val[$index2] . '"  AND `value2` IS NULL);';
        }

        return DB::statement($whr);
    }


    /**
     * For updating the respective Model in storage
     *
     * @param Invoice $invoice
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Invoice $invoice, array $input)
    {
        $p = @$input['invoice']['p'];

        $id = $input['invoice']['id'];
        $extra_discount = numberClean($input['invoice']['after_disc']);
        $input['invoice']['invoicedate'] = date_for_database($input['invoice']['invoicedate']);
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
        unset($input['invoice']['sub']);
        unset($input['invoice']['p']);

        DB::beginTransaction();
        $result = Invoice::find($id);
        if ($result->status == 'canceled') return false;
        if ($result->i_class > 1) {
            $input['invoice']['r_time'] = $input['invoice']['recur_after'];
            $input['invoice']['invoiceduedate'] = date("Y-m-d", strtotime($input['invoice']['invoicedate'] . " +" . $input['invoice']['r_time'] . 's'));
            unset($input['invoice']['recur_after']);
        } else {
            $input['invoice']['invoiceduedate'] = date_for_database($input['invoice']['invoiceduedate']);
        }
        $input['invoice'] = array_map('strip_tags', $input['invoice']);
        $result->update($input['invoice']);

        if ($result) {
            InvoiceItem::where('invoice_id', $id)->delete();
            $products = array();
            $subtotal = 0;
            $total_qty = 0;
            $total_tax = 0;
            foreach ($input['invoice_items']['product_id'] as $key => $value) {

                if ($input['invoice_items']['unit_m'][$key] > 1) {
                    $unit_val = $input['invoice_items']['unit_m'][$key];
                    $qty = $unit_val * numberClean($input['invoice_items']['product_qty'][$key]);
                    $old_qty = $unit_val * numberClean(@$input['invoice_items']['old_product_qty'][$key]);
                } else {
                    $unit_val = 1;
                    $qty = numberClean($input['invoice_items']['product_qty'][$key]);
                    $old_qty = numberClean(@$input['invoice_items']['old_product_qty'][$key]);
                }

                $subtotal += numberClean(@$input['invoice_items']['product_price'][$key]) * numberClean(@$input['invoice_items']['product_qty'][$key]);
                //$qty = numberClean($input['invoice_items']['product_qty'][$key]);

                $total_qty += $qty;
                $total_tax += numberClean(@$input['invoice_items']['product_tax'][$key]);
                $total_discount += numberClean(@$input['invoice_items']['total_discount'][$key]);
                $products[] = array(
                    'invoice_id' => $id,
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
                    'product_des' =>  strip_tags(@$input['invoice_items']['product_description'][$key], config('general.allowed')),
                    'unit_value' => $unit_val,
                    'i_class' => 0,
                    'unit' => $input['invoice_items']['unit'][$key], 'ins' => $input['invoice']['ins']
                );

                if ($old_qty > 0) {
                    $stock_update[] = array('id' => $input['invoice_items']['product_id'][$key], 'qty' => $qty - $old_qty);
                } else {
                    $stock_update[] = array('id' => $input['invoice_items']['product_id'][$key], 'qty' => $qty);
                }
            }
            InvoiceItem::insert($products);
            $invoice_d = Invoice::find($id);
            $invoice_d->subtotal = $subtotal;
            $invoice_d->tax = $total_tax;
            $invoice_d->discount = $total_discount;
            $invoice_d->items = $total_qty;
            $invoice_d->save();

            if (isset($input['data2']['custom_field'])) {
                foreach ($input['data2']['custom_field'] as $key => $value) {
                    $fields[] = array('custom_field_id' => $key, 'rid' => $id, 'module' => 2, 'data' =>  strip_tags($value), 'ins' => $input['invoice']['ins']);
                    CustomEntry::where('custom_field_id', '=', $key)->where('rid', '=', $id)->delete();
                }
                CustomEntry::insert($fields);
            }
            $update_variation = new ProductVariation;
            $index = 'id';
            Batch::update($update_variation, $stock_update, $index, true, '-');

            if (is_array($re_stock)) {
                $stock_update_one = array();
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


        throw new GeneralException(trans('exceptions.backend.invoices.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Invoice $invoice
     * @return bool
     * @throws GeneralException
     */
    public function delete(Invoice $invoice)
    {
        if ($invoice->invoice_items()->delete() && $invoice->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.invoices.delete_error'));
    }

    public function convert(array $input)
    {

        $last_invoice = Invoice::orderBy('id', 'desc')->where('i_class', '=', 0)->first();

        $extra_discount = numberClean($input['invoice']['after_disc']);
        $input['invoice']['tid'] = @$last_invoice->tid + 1;
        $input['invoice']['extra_discount'] = $extra_discount;
        $total_discount = $extra_discount;
        unset($input['invoice']['after_disc']);
        unset($input['invoice']['ship_rate']);

        //   DB::beginTransaction();

        $result = Invoice::create($input['invoice']);
        if ($result) {


            $products = array();
            $subtotal = 0;
            $total_qty = 0;
            $total_tax = 0;
            $stock_update = array();


            foreach ($input['invoice_items'] as $row) {
                $subtotal += (@$row['product_price']) * (@$row['product_qty']);
                $total_qty += (@$row['product_qty']);
                $total_tax += (@$row['total_tax']);
                $total_discount += (@$row['total_discount']);
                $products[] = array(
                    'invoice_id' => $result->id,
                    'product_id' => @$row['product_id'],
                    'product_name' => @$row['product_name'],
                    'code' => @$row['code'],
                    'product_qty' => (@$row['product_qty']),
                    'product_price' => (@$row['product_price']),
                    'product_tax' => (@$row['product_tax']),
                    'product_discount' => (@$row['product_discount']),
                    'product_subtotal' => (@$row['product_subtotal']),
                    'total_tax' => (@$row['total_tax']),
                    'total_discount' => (@$row['total_discount']),
                    'product_des' => @$row['product_des'],
                    'i_class' => 0,
                    'unit' => $row['unit'], 'ins' => $result->ins
                );
            }

            $stock_update[] = array('id' => $row['product_id'], 'qty' => $row['product_qty']);
            InvoiceItem::insert($products);
            $invoice_d = Invoice::find($result->id);
            $invoice_d->subtotal = $subtotal;
            $invoice_d->tax = $total_tax;
            $invoice_d->discount = $total_discount;
            $invoice_d->items = $total_qty;
            $invoice_d->save();


            if (@$result->id) {
                $fields = array();
                if (isset($input['data2']['custom_field'])) {
                    foreach ($input['data2']['custom_field'] as $key => $value) {
                        $fields[] = array('custom_field_id' => $key, 'rid' => $result->id, 'module' => 2, 'data' => $value, 'ins' => $input['data2']['ins']);
                    }
                    CustomEntry::insert($fields);
                }
            }

            $update_variation = new ProductVariation;
            $index = 'id';
            Batch::update($update_variation, $stock_update, $index, true);


            //     DB::commit();
            return $result;
        }
        throw new GeneralException(trans('exceptions.backend.invoices.create_error'));
    }

    public function payment($invoice, $payment)
    {
        DB::beginTransaction();
        $payments = array();
        $default_category = ConfigMeta::withoutGlobalScopes()->where('feature_id', '=', 8)->first('feature_value');
        $words['prefix'] = prefix(1);
        $total_amount = 0;
        $register_update = array();
        foreach ($payment['p_amount'] as $key => $amount) {
            $pay_method = $payment['p_method'][$key];
            $amount = numberClean($amount);

            if (!isset($register_update[$pay_method])) {
                $register_update[$pay_method] = $amount;
            } else {
                $register_update[$pay_method] = $register_update[$pay_method] + $amount;
            }

            if ($pay_method == 'Wallet') {
                $available_balance = $invoice->customer->balance;
                if ($available_balance >= $amount) {
                    $r_wallet = $available_balance - $amount;
                    $invoice->customer->balance = $r_wallet;
                    $invoice->customer->save();
                } else {
                    $amount = 0;
                }
            }
            $transaction = array();
            if ($amount > 0) {

                $transaction['ins'] = auth()->user()->ins;
                $transaction['user_id'] = auth()->user()->id;
                $transaction['credit'] = $amount;
                $transaction['debit'] = 0;
                $transaction['payment_date'] = $invoice->invoicedate;
                $transaction['credit'] = $amount;
                $transaction['payer_id'] = $invoice->customer_id;
                $transaction['payer'] = $invoice->customer->name;
                $transaction['trans_category_id'] = $default_category['feature_value'];
                $transaction['method'] = $pay_method;
                $transaction['account_id'] = $payment['p_account'];
                $transaction['note'] = trans('invoices.payment_for_invoice') . ' ' . $words['prefix'] . '#' . $invoice->tid;
                $transaction['bill_id'] = $invoice->id;
                $transaction['relation_id'] = 0;
                $payments[] = $transaction;
                $total_amount += $amount;
            }
        }

        try {
            if (count($transaction) > 0) {

                $result = Transaction::insert($payments);
                $note = trans('payments.paid_amount') . ' ' . amountFormat($total_amount);
                TransactionHistory::create(array('party_id' => $invoice->customer->id, 'user_id' => auth()->user()->id, 'note' =>  strip_tags($note), 'relation_id' => 11, 'ins' => auth()->user()->ins));
                $new_data = array();
                $register = Register::orderBy('id', 'desc')->where('user_id', '=', auth()->user()->id)->whereNull('closed_at')->first();
                $items = json_decode($register->data, true);
                $register_update['Change'] = numberClean($payment['b_change']);

                foreach ($items as $key => $reg) {

                    if (isset($register_update[$key])) $new_data[$key] = $register_update[$key] + $reg;
                    else $new_data[$key] = $reg;
                }
                $register->data = json_encode($new_data);
                $register->save();
            }
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            echo json_encode(array('status' => 'Error', 'message' => trans('exceptions.valid_entry_account') . $e->getCode()));
            return false;
        }

        $dual_entry = ConfigMeta::withoutGlobalScopes()->where('feature_id', '=', 13)->first();
        if ($dual_entry['feature_value']) {
            $payments2 = array();
            foreach ($payments as $payment_row) {
                $payment_row['debit'] = $payment_row['credit'];
                $payment_row['credit'] = 0;
                $payments2[] = $payment_row;
            }
            Transaction::insert($payments2);
        }

        if (isset($result)) {

            $account = Account::find($payment['p_account']);
            $account->balance = $account->balance + $total_amount;
            $account->save();
            if ($dual_entry['feature_value']) {
                $account = Account::find($dual_entry['value1']);
                $account->balance = $account->balance - $total_amount;
                $account->save();
            }
            $due = $invoice->total - ($total_amount + $invoice->pamnt);
            $invoice->pmethod = $transaction['method'];

            if ($due <= 0) {

                $invoice->pamnt = $invoice->total;
                $invoice->status = 'paid';
            } elseif ($total_amount < $invoice->total and $total_amount > 0) {

                $invoice->pamnt = $invoice->pamnt + $total_amount;

                $invoice->status = 'partial';
            }
            $invoice->save();
        } elseif ($invoice->pamnt >= $invoice->total) {
            $invoice->status = 'paid';
            $invoice->pamnt = $invoice->total;

            $invoice->save();
        } elseif ($invoice->pamnt > 0) {
            $invoice->status = 'partial';

            $invoice->save();
        }
        if (isset(auth()->valid)) DB::commit();
        else  DB::rollBack();

        return true;
    }

    public function create_draft(array $input)
    {
        $extra_discount = numberClean($input['invoice']['after_disc']);
        $input['invoice']['invoicedate'] = date_for_database($input['invoice']['invoicedate']);
        $input['invoice']['subtotal'] = numberClean($input['invoice']['subtotal']);
        $input['invoice']['shipping'] = numberClean($input['invoice']['shipping']);
        $input['invoice']['discount_rate'] = numberClean($input['invoice']['discount_rate']);
        $input['invoice']['after_disc'] = numberClean($input['invoice']['after_disc']);
        $input['invoice']['total'] = numberClean($input['invoice']['total']);
        $input['invoice']['ship_tax_rate'] = numberClean($input['invoice']['ship_rate']);
        $input['invoice']['ship_tax'] = numberClean($input['invoice']['ship_tax']);
        $input['invoice']['extra_discount'] = $extra_discount;
        $input['invoice']['i_class'] = 1;
        $total_discount = $extra_discount;
        if ($input['invoice']['sub']) {
            $input['invoice']['i_class'] = 2;
            $input['invoice']['r_time'] = $input['invoice']['recur_after'];
            $input['invoice']['invoiceduedate'] = date("Y-m-d", strtotime($input['invoice']['invoicedate'] . " +" . $input['invoice']['r_time'] . 's'));
            unset($input['invoice']['recur_after']);
        } else {
            $input['invoice']['invoiceduedate'] = date_for_database($input['invoice']['invoiceduedate']);
        }
        $p = @$input['invoice']['p'];
        unset($input['invoice']['after_disc']);
        unset($input['invoice']['ship_rate']);
        unset($input['invoice']['sub']);
        unset($input['invoice']['p']);


        DB::beginTransaction();

        $result = Draft::create($input['invoice']);
        if ($result) {
            $products = array();
            $subtotal = 0;
            $total_qty = 0;
            $total_tax = 0;
            $stock_update = array();
            $serial_track = array();
            foreach ($input['invoice_items']['product_id'] as $key => $value) {

                $subtotal += numberClean(@$input['invoice_items']['product_price'][$key]) * numberClean(@$input['invoice_items']['product_qty'][$key]);
                $total_qty += numberClean(@$input['invoice_items']['product_qty'][$key]);
                $total_tax += numberClean(@$input['invoice_items']['total_tax'][$key]);
                $total_discount += numberClean(@$input['invoice_items']['total_discount'][$key]);
                if ($input['invoice_items']['serial'][$key]) $serial_track[] = array('rel_type' => 2, 'rel_id' => 1, 'ref_id' => $input['invoice_items']['product_id'][$key], 'value' => $input['invoice_items']['serial'][$key], 'value2' => $result->id);
                if ($input['invoice_items']['unit_m'][$key] > 1) {
                    $unit_val = $input['invoice_items']['unit_m'][$key];
                    $qty = $unit_val * numberClean($input['invoice_items']['product_qty'][$key]);
                } else {
                    $unit_val = 1;
                    $qty = numberClean($input['invoice_items']['product_qty'][$key]);
                }
                $products[] = array(
                    'invoice_id' => $result->id,
                    'product_id' => $input['invoice_items']['product_id'][$key],
                    'product_name' => @$input['invoice_items']['product_name'][$key],
                    'code' => @$input['invoice_items']['code'][$key],
                    'product_qty' => numberClean(@$input['invoice_items']['product_qty'][$key]),
                    'product_price' => numberClean(@$input['invoice_items']['product_price'][$key]),
                    'product_tax' => numberClean(@$input['invoice_items']['product_tax'][$key]),
                    'product_discount' => numberClean(@$input['invoice_items']['product_discount'][$key]),
                    'product_subtotal' => numberClean(@$input['invoice_items']['product_subtotal'][$key]),
                    'total_tax' => numberClean(@$input['invoice_items']['total_tax'][$key]),
                    'total_discount' => numberClean(@$input['invoice_items']['total_discount'][$key]),
                    'product_des' => @$input['invoice_items']['product_description'][$key],
                    'unit_value' => $unit_val,
                    'serial' => @$input['invoice_items']['serial'][$key],
                    'i_class' => 0,
                    'unit' => $input['invoice_items']['unit'][$key], 'ins' => $result->ins
                );
                $stock_update[] = array('id' => $input['invoice_items']['product_id'][$key], 'qty' => $qty);
            }

            DraftItem::insert($products);
            $invoice_d = Draft::find($result->id);
            $invoice_d->subtotal = $subtotal;
            $invoice_d->tax = $total_tax;
            $invoice_d->discount = $total_discount;
            $invoice_d->items = $total_qty;
            $invoice_d->save();


            DB::commit();
            return $result;
        }
        throw new GeneralException(trans('exceptions.backend.invoices.create_error'));
    }
}
