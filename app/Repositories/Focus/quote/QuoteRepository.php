<?php

namespace App\Repositories\Focus\quote;


use App\Models\items\QuoteItem;
use App\Models\items\VerifiedItem;

use App\Models\quote\Quote;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

use App\Models\items\CustomEntry;
use App\Models\product\ProductVariation;
use Illuminate\Support\Facades\DB;
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;
use Carbon\Carbon;

/**
 * Class QuoteRepository.
 */
class QuoteRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Quote::class;

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

        if (request('start_date')) {
            $q->whereBetween('invoicedate', [date_for_database(request('start_date')), date_for_database(request('end_date'))]);
        }

        return
            $q->get(['id', 'tid', 'customer_id', 'invoicedate', 'invoiceduedate', 'total', 'status']);
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
     * @throws GeneralException
     * @return bool
     */
    public function create(array $input)
    {
        $invoice = $input['invoice'];
        $extra_discount = numberClean($invoice['after_disc']);

        $date = Carbon::createFromFormat('Y-m-d', date_for_database($invoice['invoicedate']));
        $daysToAdd = date_for_database($invoice['validity']);
        $invoiceduedate = $date->addDays($daysToAdd);

        $invoice['invoicedate'] = date_for_database($invoice['invoicedate']);
        $invoice['invoiceduedate'] = date_for_database($invoiceduedate);
        $invoice['subtotal'] = numberClean($invoice['subtotal']);
        $invoice['tax'] = numberClean($invoice['tax']);
        //$invoice['discount_rate'] = numberClean($invoice['discount_rate']);
        //$invoice['after_disc'] = numberClean($invoice['after_disc']);
        $invoice['total'] = numberClean($invoice['total']);
        // $invoice['ship_tax_rate'] = numberClean($invoice['ship_rate']);
        //$invoice['ship_tax'] = numberClean($invoice['ship_tax']);

        $invoice['extra_discount'] = $extra_discount;
        $total_discount = $extra_discount;
        unset($invoice['after_disc']);
        unset($invoice['ship_rate']);
        //dd($invoice );
        DB::beginTransaction();
        $proposal = $invoice['proposal'];
        $invoice = array_map('strip_tags', $invoice);
        $invoice['proposal'] = strip_tags($proposal, config('general.allowed'));
        $result = Quote::create($invoice);

        if ($result) {
            // dd($result->id);
            $products = array();
            $subtotal = 0;
            $total_qty = 0;
            $total_tax = 0;
            $stock_update = array();

            foreach ($input['invoice_items']['numbering'] as $key => $value) {
                $subtotal += numberClean(@$input['invoice_items']['product_price'][$key]) * numberClean(@$input['invoice_items']['product_qty'][$key]);
                $total_qty += numberClean(@$input['invoice_items']['product_qty'][$key]);
                $total_tax += numberClean(@$input['invoice_items']['total_tax'][$key]);
                $total_discount += numberClean(@$input['invoice_items']['total_discount'][$key]);
                $products[] = array(
                    'quote_id' => $result->id,
                    'product_id' => $input['invoice_items']['product_id'][$key],
                    'product_name' => strip_tags(@$input['invoice_items']['product_name'][$key]),
                    //'code' => @$input['invoice_items']['code'][$key],
                    'product_qty' => numberClean(@$input['invoice_items']['product_qty'][$key]),
                    'product_price' => numberClean(@$input['invoice_items']['product_price'][$key]),
                    //'product_tax' => numberClean(@$input['invoice_items']['product_tax'][$key]),
                    'product_exclusive' => numberClean(@$input['invoice_items']['product_exclusive'][$key]),
                    'product_subtotal' => numberClean(@$input['invoice_items']['product_subtotal'][$key]),
                    'total_tax' => numberClean(@$input['invoice_items']['total_tax'][$key]),
                    'total_discount' => numberClean(@$input['invoice_items']['total_discount'][$key]),
                    'a_type' => numberClean(@$input['invoice_items']['a_type'][$key]),
                    'numbering' => strip_tags(@$input['invoice_items']['numbering'][$key]),
                    'i_class' => 0,
                    'unit' => $input['invoice_items']['unit'][$key], 'ins' => $result->ins
                );
            }

            //dd($products);
            $stock_update[] = array('id' => $input['invoice_items']['product_id'][$key], 'qty' => numberClean($input['invoice_items']['product_qty'][$key]));
            QuoteItem::insert($products);
            $invoice_d = Quote::find($result->id);
            // $invoice_d->subtotal = $subtotal;
            //$invoice_d->tax = $total_tax;
            //$invoice_d->discount = $total_discount;
            $invoice_d->items = $total_qty;
            $invoice_d->save();

            if (@$result->id) {
                $fields = array();
                if (isset($input['data2']['custom_field'])) {
                    foreach ($input['data2']['custom_field'] as $key => $value) {
                        $fields[] = array('custom_field_id' => $key, 'rid' => $result->id, 'module' => 4, 'data' => strip_tags($value), 'ins' => $input['data2']['ins']);
                    }
                    CustomEntry::insert($fields);
                }
            }

            DB::commit();
            return $result;
        }
        throw new GeneralException(trans('exceptions.backend.quotes.create_error'));
    }

    public function verify(array $input)
    {
        DB::beginTransaction();

        $result = Quote::find($input['invoice']['quote_id']);
        $result->verified = 'Yes';
        $result->verified_total = numberClean(@$input['invoice']['verified_total']);
        $result->verified_disc = numberClean(@$input['invoice']['verified_disc']);
        $result->verified_tax = numberClean(@$input['invoice']['verified_tax']);
        $result->verified_amount = numberClean(@$input['invoice']['verified_amount']);
        $result->verified_by = $input['invoice']['user_id'];
        $result->verification_date = date('Y-m-d');
        $result->save();

        if ($result) {
            VerifiedItem::where('quote_id', $result->id)->delete();

            $products = array();
            $subtotal = 0;
            $total_qty = 0;
            $total_tax = 0;
            $stock_update = array();

            foreach ($input['invoice_items']['numbering'] as $key => $value) {
                $subtotal += numberClean(@$input['invoice_items']['product_price'][$key]) * numberClean(@$input['invoice_items']['product_qty'][$key]);
                $total_qty += numberClean(@$input['invoice_items']['product_qty'][$key]);
                $total_tax += numberClean(@$input['invoice_items']['total_tax'][$key]);
                $total_discount += numberClean(@$input['invoice_items']['total_discount'][$key]);
                $products[] = array(
                    'quote_id' => $result->id,
                    'product_id' => $input['invoice_items']['product_id'][$key],
                    'product_name' => strip_tags(@$input['invoice_items']['product_name'][$key]),
                    'product_qty' => numberClean(@$input['invoice_items']['product_qty'][$key]),
                    'product_price' => numberClean(@$input['invoice_items']['product_price'][$key]),
                    'product_exclusive' => numberClean(@$input['invoice_items']['product_exclusive'][$key]),
                    'product_subtotal' => numberClean(@$input['invoice_items']['product_subtotal'][$key]),
                    'total_tax' => numberClean(@$input['invoice_items']['total_tax'][$key]),
                    'total_discount' => numberClean(@$input['invoice_items']['total_discount'][$key]),
                    'a_type' => numberClean(@$input['invoice_items']['a_type'][$key]),
                    'numbering' => strip_tags(@$input['invoice_items']['numbering'][$key]),
                    'i_class' => 0,
                    'unit' => $input['invoice_items']['unit'][$key], 'ins' => $result->ins
                );
            }

            VerifiedItem::insert($products);

            DB::commit();
            return $result;
        }
        throw new GeneralException(trans('exceptions.backend.quotes.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Quote $quote
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(array $input)
    {
        print_log($input);

        return true;


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

        $result = Quote::find($id);
        if ($result->status == 'canceled') return false;

        $input['invoice'] = array_map('strip_tags', $input['invoice']);
        $result->update($input['invoice']);

        if ($result) {
            QuoteItem::where('quote_id', $id)->delete();
            $products = array();
            $subtotal = 0;
            $total_qty = 0;
            $total_tax = 0;
            foreach ($input['invoice_items']['product_id'] as $key => $value) {
                $subtotal += numberClean(@$input['invoice_items']['product_price'][$key]) * numberClean(@$input['invoice_items']['product_qty'][$key]);
                $qty = numberClean($input['invoice_items']['product_qty'][$key]);

                $total_qty += $qty;
                $total_tax += numberClean(@$input['invoice_items']['product_tax'][$key]);
                $total_discount += numberClean(@$input['invoice_items']['total_discount'][$key]);
                $products[] = array(
                    'quote_id' => $id,
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
                    'product_des' => strip_tags(@$input['invoice_items']['product_description'][$key], config('general.allowed')),
                    'i_class' => 0,
                    'unit' => $input['invoice_items']['unit'][$key], 'ins' => $input['invoice']['ins']
                );
            }
            QuoteItem::insert($products);
            $invoice_d = Quote::find($id);
            $invoice_d->subtotal = $subtotal;
            $invoice_d->tax = $total_tax;
            $invoice_d->discount = $total_discount;
            $invoice_d->items = $total_qty;
            $invoice_d->save();

            if (isset($input['data2']['custom_field'])) {
                foreach ($input['data2']['custom_field'] as $key => $value) {
                    $fields[] = array('custom_field_id' => $key, 'rid' => $id, 'module' => 4, 'data' => strip_tags($value), 'ins' => $input['invoice']['ins']);
                    CustomEntry::where('custom_field_id', '=', $key)->where('rid', '=', $id)->delete();
                }
                CustomEntry::insert($fields);
            }

            DB::commit();
            return $result;
        }

        throw new GeneralException(trans('exceptions.backend.quotes.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Quote $quote
     * @throws GeneralException
     * @return bool
     */
    public function delete(Quote $quote)
    {
        if ($quote->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.quotes.delete_error'));
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function create_pi(array $input)
    {
        $quote = $input['data'];
        // $item_titles = $input['item_titles'];
        
        // change date values to database format
        foreach ($quote as $key => $value) {
            if ($key == 'invoicedate' || $key == 'reference_date') {
                $quote[$key] = date_for_database($value);
            }
        }
        $duedate = $quote['invoicedate'].' + '.$quote['validity'].' days';
        $quote['invoiceduedate'] = date_for_database($duedate);
        $quote['quote_type'] = 'lead';
        $quote['client_type'] = 'lead';

        DB::beginTransaction();
        $result = Quote::create($quote);
        $items_count = count($input['data_items']['product_name']);
        // bulk update quote items
        if ($result && $items_count) {
            $quote_items = $this->array_items(
                $items_count, 
                $input['data_items'], 
                array('quote_id' => $result->id, 'ins' => $result->ins)
            );
            QuoteItem::insert($quote_items);
            DB::commit();
            return $result;
        }
        
        throw new GeneralException('Error Creating PI');
    }

    /**
     * For Updating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function update_pi(array $input)
    {
        $quote = $input['data'];
        // $item_titles = $input['item_titles'];
        
        // change date values to database format
        foreach ($quote as $key => $value) {
            if ($key == 'invoicedate' || $key == 'reference_date') {
                $quote[$key] = date_for_database($value);
            }
        }
        $duedate = $quote['invoicedate'].' + '.$quote['validity'].' days';
        $quote['invoiceduedate'] = date_for_database($duedate);

        DB::beginTransaction();
        $result = Quote::where('id', $quote['id'])->update($quote);
        $items_count = count($input['data_items']['product_name']);

        if ($result && $items_count) {
            $quote_items = $this->array_items(
                $items_count, 
                $input['data_items'], 
                array('quote_id' => $result->id, 'ins' => $result->ins)
            );
            // update or create new quote_items
            foreach($quote_items as $item) {
                $quote_item = QuoteItem::firstOrNew([
                    'quote_id' => $item['quote_id'],
                    'product_name' => $item['product_name']
                ]);
                // assign properties to the item
                foreach($item as $key => $value) {
                    if ($key == 'quote_id' || $key == 'product_name') continue;
                    $quote_item[$key] = $value;
                }
                $quote_item->save();
            }

            DB::commit();
            return $result;
        }
        
        throw new GeneralException('Error Updating PI');
    }


    // convert array elements
    protected function array_items($count, $item, $extra=array())
    {
        $data_items = array();
        for ($i = 0; $i < $count; $i++) {
            $row = $extra;
            foreach (array_keys($item) as $key) {
                $row[$key] = $item[$key][$i];
            }
            $data_items[] = $row;
        }
        return $data_items;
    }
}
