<?php

namespace App\Repositories\Focus\quote;

use App\Models\items\QuoteItem;
use App\Models\items\VerifiedItem;

use App\Models\quote\Quote;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;

use App\Models\lead\Lead;
use App\Models\project\Budget;
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
        if (request('pi_page') == 1) $q->where('bank_id', '>', 0);
        else $q->where('bank_id', 0);
        
        $q->when(request('i_rel_type') == 1, function ($q) {
            return $q->where('customer_id', request('i_rel_id', 0));
        });
        if (request('start_date') && request('end_date')) {
            $q->whereBetween('invoicedate', [
                date_for_database(request('start_date')), 
                date_for_database(request('end_date'))
            ]);
        }

        // order by latest updated record
        $q->orderBy('updated_at', 'desc');

        return $q->get([
            'id', 'notes', 'tid', 'customer_id', 'lead_id', 'invoicedate', 'invoiceduedate', 
            'total', 'status', 'bank_id', 'verified', 'revision', 'client_ref', 'lpo_id'
        ]);
    }

    /**
     *  Project Quotes budgeted but not verified
     */
    public function getForVerifyDataTable()
    {
        $q = $this->query();
        $quote_ids = Budget::pluck('quote_id');
        $q->whereIn('id', $quote_ids);
        
        $q->when(request('i_rel_type') == 1, function ($q) {
            return $q->where('customer_id', request('i_rel_id', 0));
        });

        if (request('start_date') && request('end_date')) {
            $q->whereBetween('invoicedate', [
                date_for_database(request('start_date')), 
                date_for_database(request('end_date'))
            ]);
        }
        
        return $q->get([
            'id', 'notes', 'tid', 'customer_id', 'lead_id', 'branch_id', 'invoicedate', 'invoiceduedate', 
            'total', 'bank_id', 'verified', 'client_ref', 'lpo_id', 'revision'
        ]);
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
     * Project Quotes Verified but not invoiced
     */
    public function getForVerifyNotInvoicedDataTable()
    {
        $q = $this->query();
        $q->where(['verified' => 'Yes', 'invoiced' => 'No']);
        $q->whereNotIn('id', function($q) { 
            $q->select('quote_id')->from('invoice_items'); 
        });

        // extract input filter fields
        $customer_id = request('customer_id');
        $lpo_id = request('lpo_number');
        $project_id = request('project_id');

        // apply filtering
        if ($customer_id) $q->where(compact('customer_id'));
        if ($lpo_id) $q->where(compact('lpo_id'));
        if ($project_id) {
            $q->whereIn('id', function($q) use($project_id) {
                $q->select('quote_id')->from('project_quotes')->where(compact('project_id'));
            });
        }

        // order by id
        $q->orderBy('id', 'desc');
        
        return $q->get([
            'id', 'notes', 'tid', 'customer_id', 'lead_id', 'branch_id', 'invoicedate', 'invoiceduedate', 
            'total', 'bank_id', 'verified_total', 'lpo_id', 'project_quote_id'
        ]);
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
        DB::beginTransaction();

        $quote = $input['data'];
        // sanitize values
        foreach ($quote as $key => $val) {
            if (in_array($key, ['invoicedate', 'reference_date'], 1)) {
                $quote[$key] = date_for_database($val);
            } else if (in_array($key, ['total', 'subtotal', 'tax'], 1)) {
                $quote[$key] = numberClean($val);
            }
        }
        $duedate = $quote['invoicedate'] . ' + ' . $quote['validity'] . ' days';
        $quote['invoiceduedate'] = date_for_database($duedate);

        // increament tid
        $ref = Quote::orderBy('tid', 'desc')->where('bank_id', 0)->first('tid');
        if (isset($quote['bank_id'])) {
            $ref = Quote::orderBy('tid', 'desc')->where('bank_id', '>', 0)->first('tid');
        }
        if ($ref && $quote['tid'] <= $ref->tid) {
            $quote['tid'] = $ref->tid + 1;
        }  
        // update lead info
        $lead = Lead::find($quote['lead_id']);
        if ($lead) {
            $quote['customer_id'] = $lead->client_id;
            $quote['branch_id'] = $lead->branch_id;
            // close Lead (status = 1)
            $lead->update(['status' => 1, 'reason' => 'won']);
        }
        $result = Quote::create($quote);

        // quote items
        $quote_items = modify_array($input['data_items']);
        $quote_items = array_map(function($item) use ($result) {
            foreach ($item as $key => $val) {
                if (in_array($key, ['product_price', 'product_subtotal'], 1)) {
                    $item[$key] = numberClean($val);
                }
            }
            return $item + ['quote_id' => $result['id'], 'ins' => $result['ins']];            
        }, $quote_items);
        QuoteItem::insert($quote_items);

        DB::commit();
        if ($result) return $result;
        
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
        DB::beginTransaction();

        $quote = $input['data'];
        // sanitize values
        foreach ($quote as $key => $val) {
            if (in_array($key, ['invoicedate', 'reference_date'], 1)) {
                $quote[$key] = date_for_database($val);
            } else if (in_array($key, ['total', 'subtotal', 'tax'], 1)) {
                $quote[$key] = numberClean($val);
            }
        }
        $duedate = $quote['invoicedate'].' + '.$quote['validity'].' days';
        $quote['invoiceduedate'] = date_for_database($duedate);

        $result = Quote::find($quote['id']);
        // If lead is updated, open previous lead
        if ($result->lead_id != $quote['lead_id']) {
            $lead = Lead::find($quote['lead_id']);
            $quote['customer_id'] = $lead->client_id;
            $quote['branch_id'] = $lead->branch_id;
            // open previous lead (status = 0)
            $lead->update(['status' => 0, 'reason' => 'new']);
        }
        $result->update($quote);

        // update or create new quote item
        $quote_items = modify_array($input['data_items']);
        foreach($quote_items as $item) {
            $new_item = $item + ['quote_id' => $quote['id'], 'ins' => $quote['ins']];
            $quote_item = QuoteItem::firstOrNew([
                'id' => $new_item['item_id'],
                'quote_id' => $quote['id'],
            ]);
            
            foreach ($new_item as $key => $val) {
                if (in_array($key, ['product_price', 'product_subtotal'], 1)) {
                    $quote_item[$key] = numberClean($val);
                } else $quote_item[$key] = $val;
            }

            unset($quote_item['item_id']);
            if ($quote_item['id'] == 0) unset($quote_item['id']);
            print_log(json_encode($quote_item, JSON_PRETTY_PRINT));
            $quote_item->save();
        }

        DB::commit();
        if ($result) return $quote;      
               
        throw new GeneralException('Error Updating Quote');
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Quote $quote
     * @throws GeneralException
     * @return bool
     */
    public function delete($quote)
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

    /**
     * Verify Budgeted Project Quote
     */
    public function verify(array $input)
    {
        DB::beginTransaction();

        // quote properties
        $quote_data = $input['quote'];
        $quote_id = $quote_data['id'];
        $verify_no = $quote_data['verify_no'];
        $ins = auth()->user()->ins;

        // update or create new quote_item
        $quote_items = modify_array($input['quote_items']);
        foreach($quote_items as $item) {
            $new_item = $item + compact('quote_id', 'ins');
            $quote_item = VerifiedItem::firstOrNew([
                'id' => $new_item['item_id'],
                'quote_id' => $quote_id,
            ]);

            foreach($new_item as $key => $val) {
                if (in_array($key, ['product_price', 'product_subtotal'], 1)) {
                    $quote_item[$key] = numberClean($val);
                } else $quote_item[$key] = $val;            
            }

            unset($quote_item['item_id']);
            if ($quote_item['id'] == 0) unset($quote_item['id']);
            $quote_item->save();
        }
        
        // update or create new job_card
        $job_cards = modify_array($input['job_cards']);
        foreach($job_cards as $item) {
            // skip non-unique job_card reference 
            $job_card = VerifiedJc::where(['reference' => $item['reference']])->first();
            if ($job_card) continue;

            $new_item = $item + compact('quote_id', 'verify_no');
            $job_card = VerifiedJc::firstOrNew([
                'id' => $new_item['jcitem_id'],
                'quote_id' => $quote_id,
            ]);

            foreach($new_item as $key => $value) {
                if ($key == 'date') $job_card[$key] = date_for_database($value);
                else $job_card[$key] = $value;
            }

            unset($job_card['jcitem_id']);                
            if ($job_card['id'] == 0) unset($job_card['id']);
            $job_card->save();
        }
        
        $quote = Quote::find($quote_id);
        $result = $quote->update([
            'verified' => 'Yes', 
            'verification_date' => date('Y-m-d'),
            'verified_by' => auth()->user()->id,
            'gen_remark' => $quote_data['gen_remark'],
            'verified_amount' => $quote_data['subtotal'],
            'verified_total' => $quote_data['total'],
            'verified_tax' => $quote_data['tax']
        ]);

        DB::commit();
        if ($result) return $quote;      
        
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
}
