<?php

namespace App\Repositories\Focus\quote;


use App\Models\items\QuoteItem;
use App\Models\items\VerifiedItem;

use App\Models\quote\Quote;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;

use App\Models\lead\Lead;
use Illuminate\Support\Facades\DB;

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

        return $q->get(['id', 'notes', 'tid', 'customer_id', 'lead_id', 'invoicedate', 'invoiceduedate', 'total', 'status']);
    }

    public function getSelfDataTable($self_id = false)
    {
        if ($self_id) {
            $q = $this->query()->withoutGlobalScopes();
            $q->where('customer_id', '=', $self_id);

            return $q->get(['id', 'tid', 'customer_id', 'invoicedate', 'invoiceduedate', 'total', 'status']);
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
        $quote = $input['data'];
        // format date values
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
        $lead = Lead::find($quote['lead_id']);
        $quote['customer_id'] = $lead->client_id;
        $quote['branch_id'] = $lead->branch_id;

        $result = Quote::create($quote);

        // quote items
        $items_count = count($input['data_items']['product_name']);
        $quote_items = $this->array_items(
            $items_count, 
            $input['data_items'], 
            ['quote_id' => $result['id'], 'ins' => $result['ins']]
        );
        
        // bulk insert quote_items
        if ($result && $items_count) {
            QuoteItem::insert($quote_items);
            DB::commit();
            return $result;
        }
        
        throw new GeneralException('Error Creating Quote');
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
        $quote = $input['data'];
        // change date values to database format
        foreach ($quote as $key => $value) {
            if ($key == 'invoicedate' || $key == 'reference_date') {
                $quote[$key] = date_for_database($value);
            }
        }
        $duedate = $quote['invoicedate'].' + '.$quote['validity'].' days';
        $quote['invoiceduedate'] = date_for_database($duedate);

        DB::beginTransaction();
        $lead = Lead::find($quote['lead_id']);
        $quote['customer_id'] = $lead->client_id;
        $quote['branch_id'] = $lead->branch_id;

        $result = Quote::where('id', $quote['id'])->update($quote);

        // quote items
        $items_count = count($input['data_items']['product_name']);
        $quote_items = $this->array_items(
            $items_count, 
            $input['data_items'], 
            ['quote_id' => $quote['id'], 'ins' => $quote['ins']]
        );

        // update or create new quote_item
        if ($result && $items_count) {
            foreach($quote_items as $item) {
                $quote_item = QuoteItem::firstOrNew([
                    'id' => $item['item_id'],
                    'quote_id' => $item['quote_id'],
                ]);
                // assign properties to the item
                foreach($item as $key => $value) {
                    $quote_item[$key] = $value;
                }
                // remove stale attributes
                if ($quote_item['id'] == 0) unset($quote_item['id']);
                unset($quote_item['item_id']);

                $quote_item->save();
            }

            DB::commit();
            return $result;
        }
        
        throw new GeneralException('Error Updating Quote');
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
     * For deleting the respective model from storage
     *
     * @param Quote $quote
     * @throws GeneralException
     * @return bool
     */
    public function delete_product($id)
    {        
        if (QuoteItem::destroy($id)) {
            return true;
        }

        throw new GeneralException(trans('Error deleting product'));
    }

    // convert array to database collection format
    protected function array_items($count=0, $item=[], $extra=[])
    {
        $data_items = array();
        for ($i = 0; $i < $count; $i++) {
            $row = $extra;
            foreach (array_keys($item) as $key) {
                if (isset($item[$key][$i])) {
                    $row[$key] = $item[$key][$i];
                }
            }
            $data_items[] = $row;
        }
        return $data_items;
    }
}
