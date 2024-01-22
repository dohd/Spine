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
use Illuminate\Validation\ValidationException;

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
        $q = $this->query()->with('currency');

        // filter by customer login
        if (auth()->user()->customer_id) {
            $q->where('customer_id', auth()->user()->customer_id);
        }
        
        $q->when(request('page') == 'pi', fn($q) => $q->where('bank_id', '>', 0));
        $q->when(request('page') == 'qt', fn($q) => $q->where('bank_id', 0));
        
        $q->when(request('start_date') && request('end_date'), function ($q) {
            $q->whereBetween('date', array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]));
        });

        // client filter
        $q->when(request('client_id'), fn($q) => $q->where('customer_id', request('client_id')));
        
        // status criteria filter
        $status = true;
        if (request('status_filter')) {
            switch (request('status_filter')) {
                case 'Unapproved':
                    $q->whereNull('approved_by');
                    break;
                case 'Approved & Unbudgeted':
                    $q->whereNotNull('approved_by')->whereNull('project_quote_id');
                    break;
                case 'Budgeted & Unverified':
                    $q->whereNotNull('project_quote_id')->whereNull('verified_by');
                    break;
                case 'Verified with LPO & Uninvoiced':
                    $q->whereNotNull('verified_by')->whereNotNull('lpo_id')->where('invoiced', 'No');
                    break;
                case 'Verified without LPO & Uninvoiced':
                    $q->whereNotNull('verified_by')->whereNull('lpo_id')->where('invoiced', 'No');
                    break;
                case 'Approved without LPO & Uninvoiced':
                    $q->whereNotNull('approved_by')->whereNull('lpo_id')->where('invoiced', 'No');
                    break;
                case 'Approved & Uninvoiced':
                    $q->whereNotNull('approved_by')->doesntHave('invoice_product');
                    break;                    
                case 'Invoiced & Due':
                    // quotes in due invoices
                    $q->whereHas('invoice_product', function ($q) {
                        $q->whereHas('invoice', function ($q) {
                            $q->where('status', 'due');
                        });
                    });
                    break;
                case 'Invoiced & Partially Paid':
                    // quotes in partially paid invoices
                    $q->whereHas('invoice_product', function ($q) {
                        $q->whereHas('invoice', function ($q) {
                            $q->where('status', 'partial');
                        });
                    });
                    break;
                case 'Invoiced & Paid':
                    // quotes in partially paid invoices
                    $q->whereHas('invoice_product', function ($q) {
                        $q->whereHas('invoice', function ($q) {
                            $q->where('status', 'paid');
                        });
                    });
                    break;
                case 'Invoiced':
                    $q->whereHas('invoice_product');
                    break;                    
                case 'Cancelled':
                    $status = false;
                    $q->where('status', 'cancelled');
                    break;
            }
        }
        $q->when($status, fn($q) => $q->where('status', '!=', 'cancelled'));

        // project filter
        $q->when(request('project_id'), function($q) {
            $q->whereHas('project', fn($q) => $q->where('projects.id', request('project_id')));
        });
        
        return $q;
    }

    /**
     * Quotes pending verification
     */
    public function getForVerifyDataTable()
    {
        $q = $this->query()->where('status', 'approved');
        
        // customer filter
        if(request('customer_id')) {
            $q->where('customer_id', request('customer_id'));
            if (request('branch_id')) $q->where('branch_id', request('branch_id'));
        } 
        else $q->limit(500);

        // date filter
        $q->when(request('start_date') && request('end_date'), function ($q) {
            $q->whereBetween('date', array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]));
        });

        // state filter
        if (request('verify_state')) $q->where('verified', request('verify_state'));

        // standard quote or budget project quote
        $q->where(fn($q) =>  $q->whereHas('budget')->orWhere('quote_type', 'standard'));
        $q->where('status', '!=', 'cancelled');
        
        return $q->get();
    }

    /**
     * Quotes pending invoicing
     */
    public function getForVerifyNotInvoicedDataTable()
    {
        $q = $this->query();
        
        // standard quote or budget project quote
        $q->where(fn($q) => $q->whereHas('budget')->orWhere('quote_type', 'standard'));

        // verified and uninvoiced quotes
        $q->where(['verified' => 'Yes', 'invoiced' => 'No'])->whereDoesntHave('invoice_product');
                
        $q->when(request('customer_id'), fn($q) => $q->where('customer_id', request('customer_id')));
        $q->when(request('lpo_number'), fn($q) => $q->where('lpo_id', request('lpo_number')));
        $q->when(request('project_id'), function ($q) {
            $q->whereIn('id', function($q) {
                $q->select('quote_id')->from('project_quotes')->where('project_id', request('project_id'));
            });
        });
        
        return $q->get([
            'id', 'notes', 'tid', 'customer_id', 'lead_id', 'branch_id', 'date', 
            'total', 'bank_id', 'verified_total', 'lpo_id', 'project_quote_id', 'currency_id'
        ]);
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return $quote
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date', 'reference_date']))
                $data[$key] = date_for_database($val);
            if (in_array($key, ['total', 'subtotal', 'tax', 'taxable']))
                $data[$key] = numberClean($val);
        }   
        
        $tid = 0;
        if (isset($data['bank_id'])) {
            $tid = Quote::where('ins', $data['ins'])->where('bank_id', '>', 0)->max('tid');
        } else {
            $tid = Quote::where('ins', $data['ins'])->where('bank_id', 0)->max('tid');
        }
        if ($data['tid'] <= $tid) $data['tid'] = $tid+1;

        // close lead
        Lead::find($data['lead_id'])->update(['status' => 1, 'reason' => 'won']);
        $result = Quote::create($data);

        // quote line items
        $data_items = $input['data_items'];
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'quote_id' => $result->id, 
                'ins' => $result->ins,
                'product_price' =>  floatval(str_replace(',', '', $v['product_price'])),
                'product_subtotal' => floatval(str_replace(',', '', $v['product_subtotal'])),
                'buy_price' => floatval(str_replace(',', '', $v['buy_price'])),
            ]);
        }, $data_items);
        QuoteItem::insert($data_items);

        // quote labour items
        $skill_items = $input['skill_items'];
        $skill_items = array_map(function ($v) use($result) {
            return array_replace($v, ['quote_id' => $result->id]);
        }, $skill_items);
        BudgetSkillset::insert($skill_items);
        
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
     * @return $quote
     */
    public function update($quote, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date', 'reference_date']))
                $data[$key] = date_for_database($val);
            if (in_array($key, ['total', 'subtotal', 'tax', 'taxable'])) 
                $data[$key] = numberClean($val);
        }   
        // update lead status
        if ($quote->lead_id != $data['lead_id']) {
            $quote->lead->update(['status' => 0, 'reason' => 'new']);
            Lead::find($data['lead_id'])->update(['status' => 1, 'reason' => 'won']);
        }
        
        $result = $quote->update($data);

        $data_items = $input['data_items'];
        // remove omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $quote->products()->whereNotIn('id', $item_ids)->delete();

        // create or update items
        foreach($data_items as $item) {
            foreach ($item as $key => $val) {
                if (in_array($key, ['product_price', 'product_subtotal', 'buy_price']))
                    $item[$key] = floatval(str_replace(',', '', $val));
            }
            $quote_item = QuoteItem::firstOrNew(['id' => $item['id']]);
            $quote_item->fill(array_replace($item, ['quote_id' => $quote['id'], 'ins' => $quote['ins']]));
            if (!$quote_item->id) unset($quote_item->id);
            $quote_item->save();
        }

        $skill_items = $input['skill_items'];
        // remove omitted items
        $skill_ids = array_map(function ($v) { return $v['skill_id']; }, $skill_items);
        $quote->skill_items()->whereNotIn('id', $skill_ids)->delete();
        // create or update items
        foreach($skill_items as $item) {
            $skillset = BudgetSkillset::firstOrNew(['id' => $item['skill_id']]);         
            $skillset->fill(array_replace($item, ['quote_id' => $quote->id]));
            if (!$skillset->id) unset($skillset->id);
            unset($skillset->skill_id);
            $skillset->save();
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
    public function delete($quote)
    {
        DB::beginTransaction();

        $type = $quote->bank_id ? 'PI' : 'Quote';
        if ($quote->project_quote) 
            throw ValidationException::withMessages([$type . ' is attached to a project!']);
            
        if ($quote->delete()) {
            if ($quote->lead) $quote->lead->update(['status' => 0, 'reason' => 'new']);
            DB::commit();
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.quotes.delete_error'));
    }


    /**
     * Verify Budgeted Project Quote
     */
    public function verify(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        // update quote verification status
        $data = $input['data'];
        $quote = Quote::find($data['id']);
        $result = $quote->update([
            'verified' => 'Yes', 
            'verification_date' => date('Y-m-d'),
            'verified_by' => auth()->user()->id,
            'gen_remark' => $data['gen_remark'],
            'verified_amount' => numberClean($data['subtotal']),
            'verified_total' => numberClean($data['total']),
            'verified_tax' => numberClean($data['tax']), 
            'verified_taxable' => numberClean($data['taxable']), 
        ]);

        // quote verified products
        $data_items = $input['data_items'];
        // delete omitted items
        $item_ids = array_map(fn($v) => $v['item_id'], $data_items);
        VerifiedItem::where('quote_id', $data['id'])->whereNotIn('id', $item_ids)->delete();
        // update or create verified item
        foreach ($data_items as $item) {
            $item = array_replace($item, [
                'quote_id' => $data['id'],
                'product_qty' => numberClean($item['product_qty']),
                'product_price' => floatval(str_replace(',', '', $item['product_price'])),
                'product_subtotal' => floatval(str_replace(',', '', $item['product_subtotal'])),
                'ins' => auth()->user()->ins,
            ]);
            $item['product_tax'] = $item['product_subtotal'] * $item['tax_rate'];

            $verify_item = VerifiedItem::firstOrNew(['id' => $item['item_id']]);
            $verify_item->fill($item);
            unset($verify_item->item_id);
            $verify_item->save();
        }

        // quote verified jobcards
        $job_cards = $input['job_cards'];
        // delete omitted items
        $item_ids = array_map(fn($v) => $v['jcitem_id'], $job_cards);
        VerifiedJc::where('quote_id', $data['id'])->whereNotIn('id', $item_ids)->delete();        
        // duplicate jobcard reference
        $references = array_map(fn($v) => $v['reference'], $job_cards);
        $references = VerifiedJc::whereIn('reference', $references)->pluck('reference')->toArray();
        // update or create verified jobcards
        foreach ($job_cards as $item) {
            // skip duplicate reference
            if (in_array($item['reference'], $references) && !$item['jcitem_id']) continue;
            $item = array_replace($item, [
                'quote_id' => $data['id'],
                'date' => date_for_database($item['date']),
            ]);

            $jobcard = VerifiedJc::firstOrNew(['id' => $item['jcitem_id']]);
            $jobcard->fill($item);
            unset($jobcard->jcitem_id);
            $jobcard->save();
        }

        if ($result) {
            DB::commit();
            return $quote;      
        }
        
        throw new GeneralException('Error Verifying Quote');
    }
}