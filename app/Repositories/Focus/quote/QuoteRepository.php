<?php

namespace App\Repositories\Focus\quote;


use App\Models\items\QuoteItem;
use App\Models\items\VerifiedItem;

use App\Models\quote\Quote;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;

use App\Models\lead\Lead;
use App\Models\verifiedjcs\VerifiedJc;
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

        // distinguish pi from quote
        if (request('pi_page') == 1) {
            $q->where('bank_id', '>', 0);
        } else {
            $q->where('bank_id', 0);
        }

        $q->when(request('i_rel_type') == 1, function ($q) {
            return $q->where('customer_id', '=', request('i_rel_id', 0));
        });

        if (request('start_date')) {
            $q->whereBetween('invoicedate', [date_for_database(request('start_date')), date_for_database(request('end_date'))]);
        }

        return $q->get(['id', 'notes', 'tid', 'customer_id', 'lead_id', 'invoicedate', 'invoiceduedate', 'total', 'status', 'bank_id', 'verified']);
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
        $duedate = $quote['invoicedate'] . ' + ' . $quote['validity'] . ' days';
        $quote['invoiceduedate'] = date_for_database($duedate);

        // increament tid
        $ref = Quote::orderBy('tid', 'desc')->where('bank_id', 0)->first('tid');
        if (isset($quote['bank_id'])) {
            $ref = Quote::orderBy('tid', 'desc')->where('bank_id', '>', 0)->first('tid');
        }
        if (isset($ref->tid) && $quote['tid'] <= $ref->tid) {
            $quote['tid'] = $ref->tid + 1;
        }  

        // default fields
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

        // assign unit field if non-existant
        foreach ($quote_items as $key => $val) {
            if (!isset($val['unit'])) {
                $quote_items[$key]['unit'] = '';
            }
        }

        QuoteItem::insert($quote_items);
        if ($result) {
            DB::commit();
            return $result;
        }
        
        throw new GeneralException('Error Creating Quote');
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

        // update or create new quote item
        foreach($quote_items as $item) {
            $quote_item = QuoteItem::firstOrNew([
                'id' => $item['item_id'],
                'quote_id' => $item['quote_id'],
            ]);
            // assign properties to the item
            foreach($item as $key => $value) {
                $quote_item[$key] = $value;
            }
            // remove stale attributes and save
            if ($quote_item['id'] == 0) unset($quote_item['id']);
            unset($quote_item['item_id']);
            $quote_item->save();
        }

        if ($result) {
            DB::commit();
            return $quote;    
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
        if ($quote->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.quotes.delete_error'));
    }

    // Delete Quote product
    public function delete_product($id)
    {        
        if (QuoteItem::destroy($id)) return true;

        throw new GeneralException(trans('Error deleting product'));
    }

    public function verify(array $input)
    {
        // quote properties
        $quote_id = $input['quote']['id'];
        $ins = $input['quote']['ins'];
        $verify_no = $input['quote']['verify_no'];

        // quote items
        $items_count = count($input['quote_items']['product_name']);
        $quote_items = $this->array_items(
            $items_count, 
            $input['quote_items'],
            compact('quote_id', 'ins')
        );
        
        // job cards
        $job_cards = array();
        $jc_count = count($input['job_cards']['reference']);
        $item = $input['job_cards'];
        for ($i = 0; $i < $jc_count; $i++) {
            $row = compact('quote_id', 'verify_no');
            foreach (array_keys($item) as $key) {
                if ($key == 'date') {
                    $item[$key][$i] = date_for_database($item[$key][$i]);
                }
                $row[$key] = $item[$key][$i];             
            }
            $job_cards[] = $row;
        }


        DB::beginTransaction();
        // update or create new quote_item
        foreach($quote_items as $item) {
            $quote_item = VerifiedItem::firstOrNew([
                'id' => $item['item_id'],
                'quote_id' => $item['quote_id'],
            ]);
            // assign properties to the item
            foreach($item as $key => $value) {
                $quote_item[$key] = $value;
            }
            // remove stale attributes and save
            if ($quote_item['id'] == 0) unset($quote_item['id']);
            unset($quote_item['item_id']);
            $quote_item->save();
        }
        // update or create new job_card
        foreach($job_cards as $item) {
            $job_card = VerifiedJc::firstOrNew([
                'id' => $item['jcitem_id'],
                'quote_id' => $item['quote_id'],
            ]);
            // assign properties to the item
            foreach($item as $key => $value) {
                $job_card[$key] = $value;
            }
            // remove stale attributes and save
            if ($job_card['id'] == 0) unset($job_card['id']);
            unset($job_card['jcitem_id']);                
            $job_card->save();
        }
        
        $result = Quote::find($quote_id)->update(['verified' => 'Yes', 'verification_date' => date('Y-m-d')]);
        if ($result) return DB::commit();
        
        throw new GeneralException('Error Verifying Quote');
    }

    // Delete verified Quote product
    public function delete_verified_item($id)
    {        
        if (VerifiedItem::destroy($id)) return true;

        throw new GeneralException(trans('Error deleting verified product'));
    }

    // Delete verified Job card
    public function delete_verified_jcs($id)
    {        
        if (VerifiedJc::destroy($id)) return true;

        throw new GeneralException(trans('Error deleting verified job card'));
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
