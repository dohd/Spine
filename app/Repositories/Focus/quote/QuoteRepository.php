<?php

namespace App\Repositories\Focus\quote;

use App\Models\items\QuoteItem;
use App\Models\items\VerifiedItem;

use App\Models\quote\Quote;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;

use App\Models\lead\Lead;
use App\Models\project\BudgetSkillset;
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
        
        if (request('start_date') && request('end_date')) {
            $q->whereBetween('date', [
                date_for_database(request('start_date')), 
                date_for_database(request('end_date'))
            ]);
        }

        // order by latest updated record
        $q->orderBy('updated_at', 'DESC');

        return $q->get([
            'id', 'notes', 'tid', 'customer_id', 'lead_id', 'date', 'total', 'status', 'bank_id', 
            'verified', 'revision', 'client_ref', 'lpo_id'
        ]);
    }

    /**
     *  Project Quotes budgeted but not verified
     */
    public function getForVerifyDataTable()
    {
        $q = $this->query();
        $q->whereHas('budget');

        if (request('start_date') && request('end_date')) {
            $q->whereBetween('date', [
                date_for_database(request('start_date')), 
                date_for_database(request('end_date'))
            ]);
        }
        
        return $q->get([
            'id', 'notes', 'tid', 'customer_id', 'lead_id', 'branch_id', 
            'total', 'bank_id', 'verified', 'client_ref', 'lpo_id', 'revision'
        ]);
    }

    /**
     * Project Quotes Verified but not invoiced
     */
    public function getForVerifyNotInvoicedDataTable()
    {
        $q = $this->query()
        ->where(['verified' => 'Yes', 'invoiced' => 'No'])
        ->whereNotIn('id', function($q) { 
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
        
        return $q->get([
            'id', 'notes', 'tid', 'customer_id', 'lead_id', 'branch_id', 'date', 
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
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date', 'reference_date'], 1))
                $data[$key] = date_for_database($val);
            if (in_array($key, ['total', 'subtotal', 'tax'], 1))
                $data[$key] = numberClean($val);
        }   
        // increament tid
        if (isset($data['bank_id'])) 
            $last_qt = Quote::orderBy('tid', 'desc')->where('bank_id', '>', 0)->first('tid');
        else $last_qt = Quote::orderBy('tid', 'desc')->where('bank_id', 0)->first('tid');

        if ($last_qt && $data['tid'] <= $last_qt->tid) {
            $data['tid'] = $last_qt->tid + 1;
        } 
        // close lead
        Lead::find($data['lead_id'])->update(['status' => 1, 'reason' => 'won']);
        $result = Quote::create($data);

        $data_items = $input['data_items'];
        foreach ($data_items as $k => $item) {
            $data_items[$k] = array_replace($item, [
                'quote_id' => $result->id, 
                'ins' => $result->ins,
                'product_price' => numberClean($item['product_price']),
                'product_subtotal' => numberClean($item['product_subtotal']),
                'buy_price' => numberClean($item['buy_price']),
            ]);
        }
        QuoteItem::insert($data_items);

        $skill_items = $input['skill_items'];
        foreach ($skill_items as $k => $item) {
            $skill_items[$k] = $item + ['quote_id' => $result->id];
        }
        BudgetSkillset::insert($skill_items);

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
    public function update($quote, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date', 'reference_date'], 1))
                $data[$key] = date_for_database($val);
            if (in_array($key, ['total', 'subtotal', 'tax'], 1)) 
                $data[$key] = numberClean($val);
        }   
        // update lead status
        if ($quote->lead_id != $data['lead_id']) {
            $quote->lead->update(['status' => 0, 'reason' => 'new']);
            Lead::find($data['lead_id'])->update(['status' => 1, 'reason' => 'won']);
        }
        $quote->update($data);

        $data_items = $input['data_items'];
        $item_ids = array_reduce($data_items, function ($init, $item) {
            $init[] = $item['id'];
            return $init;
        }, []);
        $quote->products()->whereNotIn('id', $item_ids)->delete();

        foreach($data_items as $item) {
            $item = $item + ['quote_id' => $quote['id'], 'ins' => $quote['ins']];
            $quote_item = QuoteItem::firstOrNew([
                'id' => $item['id'],
                'quote_id' => $item['quote_id'],
            ]);
            foreach ($item as $key => $val) {
                if (in_array($key, ['product_price', 'product_subtotal', 'buy_price'], 1))
                    $quote_item[$key] = numberClean($val);
                else $quote_item[$key] = $val;
            }
            if (!$quote_item['id']) unset($quote_item['id']);
            $quote_item->save();
        }

        $skill_items = $input['skill_items'];
        $skill_ids = array_reduce($data_items, function ($init, $item) {
            $init[] = $item['id'];
            return $init;
        }, []);
        $quote->skill_items()->whereNotIn('id', $skill_ids)->delete();

        foreach($skill_items as $item) {
            $item = $item + ['quote_id' => $quote->id];
            $skill_item = BudgetSkillset::firstOrNew([
                'id' => $item['skill_id'],
                'quote_id' => $item['quote_id'],
            ]);
            foreach ($item as $key => $val) {
                if ($key == 'skill_id') continue;
                $skill_item[$key] = $val;
            }
            if (!$skill_item['id']) unset($skill_item['id']);
            $skill_item->save();
        }

        DB::commit();
        if ($quote) return $quote;      
               
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
        $quote->lead->update(['status' => 0, 'reason' => 'new']);
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
            $item = $item + compact('quote_id', 'ins');
            $quote_item = VerifiedItem::firstOrNew([
                'id' => $item['item_id'],
                'quote_id' => $quote_id,
            ]);

            foreach($item as $key => $val) {
                if (in_array($key, ['product_price', 'product_subtotal'], 1)) {
                    $quote_item[$key] = numberClean($val);
                } 
                else $quote_item[$key] = $val;            
            }
            unset($quote_item['item_id']);
            if (!$quote_item['id']) unset($quote_item['id']);
            $quote_item->save();
        }
        
        // update or create new job_card
        $job_cards = modify_array($input['job_cards']);
        foreach($job_cards as $item) {
            $job_card = VerifiedJc::where(['reference' => $item['reference']])->first();
            if ($job_card) continue;

            $item = $item + compact('quote_id', 'verify_no');
            $job_card = VerifiedJc::firstOrNew([
                'id' => $item['jcitem_id'],
                'quote_id' => $quote_id,
            ]);

            foreach($item as $key => $value) {
                if ($key == 'date') 
                    $job_card[$key] = date_for_database($value);
                else $job_card[$key] = $value;
            }

            unset($job_card['jcitem_id']);                
            if (!$job_card['id']) unset($job_card['id']);
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
