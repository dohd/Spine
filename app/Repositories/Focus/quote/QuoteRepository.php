<?php

namespace App\Repositories\Focus\quote;

use App\Models\items\QuoteItem;
use App\Models\items\VerifiedItem;

use App\Models\quote\Quote;
use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\invoice\Invoice;
use App\Repositories\BaseRepository;

use App\Models\lead\Lead;
use App\Models\project\BudgetSkillset;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
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
        $q = $this->query();
        
        $q->when(request('page') == 'pi', function ($q) {
            $q->where('bank_id', '>', 0);
        })->when(request('page') == 'qt', function ($q) {
            $q->where('bank_id', 0);
        });
        
        // date filter
        if (request('start_date') && request('end_date')) {
            $q->whereBetween('date', [
                date_for_database(request('start_date')), 
                date_for_database(request('end_date'))
            ]);
        }

        // client filter
        if (request('client_id')) $q->where('customer_id', request('client_id'));

        // status criteria filter
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
                case 'Invoiced & Due':
                    // quotes in due invoices
                    $q->whereHas('invoice_product', function ($q) {
                        $q->whereHas('invoice', function ($q) {
                            $q->where('status', 'due');
                        });
                    });
                    break;
            }
        }

        if (request('client_id') || request('status_filter')) 
            $q->where('status', '!=', 'cancelled');

        return $q->get([
            'id', 'notes', 'tid', 'customer_id', 'lead_id', 'date', 'total', 'status', 'bank_id', 
            'verified', 'revision', 'client_ref', 'lpo_id', 'closed_by'
        ]);
    }

    /**
     *  Project Quotes budgeted but not verified
     */
    public function getForVerifyDataTable()
    {
        $q = $this->query()->whereHas('budget');

        $q->when(request('start_date') && request('end_date'), function ($q) {
            $q->whereBetween('date', [
                date_for_database(request('start_date')), 
                date_for_database(request('end_date'))
            ]);
        });

        $q->when(request('verify_state'), function ($q) {
            $q->where('verified', request('verify_state'));
        });
        
        return $q->get([
            'id', 'notes', 'tid', 'customer_id', 'lead_id', 'branch_id', 'total', 'bank_id', 'verified',
            'client_ref', 'lpo_id', 'revision', 'issuance_status', 'verified_total'
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
     * @return $quote
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
        if (isset($data['bank_id'])) $last_tid = Quote::where('bank_id', '>', 0)->max('tid');
        else $last_tid = Quote::where('bank_id', 0)->max('tid');
        if ($data['tid'] <= $last_tid) $data['tid'] = $last_tid + 1;
            
        // close lead
        Lead::find($data['lead_id'])->update(['status' => 1, 'reason' => 'won']);
        $result = Quote::create($data);

        $data_items = $input['data_items'];
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'quote_id' => $result->id, 
                'ins' => $result->ins,
                'product_price' => numberClean($v['product_price']),
                'product_subtotal' => numberClean($v['product_subtotal']),
                'buy_price' => numberClean($v['buy_price']),
            ]);
        }, $data_items);
        QuoteItem::insert($data_items);

        $skill_items = $input['skill_items'];
        $skill_items = array_map(function ($v) use($result) {
            return array_replace($v, ['quote_id' => $result->id]);
        }, $skill_items);
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
     * @return $quote
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
        unset($data['tid']);
        $result = $quote->update($data);

        $data_items = $input['data_items'];
        // remove omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $quote->products()->whereNotIn('id', $item_ids)->delete();

        // create or update items
        foreach($data_items as $item) {
            foreach ($item as $key => $val) {
                if (in_array($key, ['product_price', 'product_subtotal', 'buy_price']))
                    $item[$key] = numberClean($val);
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
        $type = $quote->bank_id ? 'PI' : 'Quote';
        if ($quote->project_quote) 
            throw ValidationException::withMessages([$type . ' is attached to a project!']);
            
        if ($quote->delete()) {
            $quote->lead->update(['status' => 0, 'reason' => 'new']);
            return $type;
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
        ]);

        $data_items = $input['data_items'];
        // delete omitted items
        $item_ids = array_map(function ($v) { return $v['item_id']; }, $data_items);
        VerifiedItem::where('quote_id', $data['id'])->whereNotIn('id', $item_ids)->delete();
        // update or create verified item
        foreach ($data_items as $item) {
            $item = array_replace($item, [
                'quote_id' => $data['id'],
                'product_qty' => numberClean($item['product_qty']),
                'product_price' => numberClean($item['product_price']),
                'product_subtotal' => numberClean($item['product_subtotal']),
                'ins' => auth()->user()->ins
            ]);
            $verify_item = VerifiedItem::firstOrNew(['id' => $item['item_id']]);
            $verify_item->fill($item);
            if (!$verify_item->id) unset($verify_item->id);
            unset($verify_item->item_id);
            $verify_item->save();
        }

        $job_cards = $input['job_cards'];
        // delete omitted items
        $item_ids = array_map(function ($v) { return $v['jcitem_id']; }, $job_cards);
        VerifiedJc::where('quote_id', $data['id'])->whereNotIn('id', $item_ids)->delete();        
        // duplicate jobcard reference
        $references = array_map(function ($v) { return $v['reference']; }, $job_cards);
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
            if (!$jobcard->id) unset($jobcard->id);
            unset($jobcard->jcitem_id);
            $jobcard->save();
        }

        DB::commit();
        if ($result) return $quote;      
        
        throw new GeneralException('Error Verifying Quote');
    }

    /**
     * Close quote or pi
     */
    public function close_quote($quote, array $input)
    {
        DB::beginTransaction();

        $result = $quote->update(['closed_by' => $input['user_id']]);
        
        $id = $quote->id;
        $invoice = Invoice::whereHas('products', function($q) use($id) {
            $q->where('quote_id', $id);
        })->first();

        $quote_ids = $invoice->products()->pluck('quote_id')->toArray();
        $no_quotes = Quote::whereIn('id', $quote_ids)->count();
        $no_closed_quotes = Quote::whereIn('id', $quote_ids)->where('closed_by', '>', 0)->count();

        /**accounts */
        if ($no_quotes == $no_closed_quotes) {
            $quotes = Quote::whereIn('id', $quote_ids)->get();
            $this->post_transaction($invoice, $quotes);
        }
            
            
        DB::commit();
        if ($result) return true;
    }

    // transaction
    public function post_transaction($invoice, $quotes)
    {
        $tr_data = array();
        $tr_category = Transactioncategory::where('code', 'endprj')->first(['id', 'code']);
        $tid = Transaction::max('tid') + 1;
        $data = [
            'tid' => $tid,
            'tr_date' => date('Y-m-d'),
            'due_date' => $invoice->invoiceduedate,
            'user_id' => $invoice->user_id,
            'ins' => $invoice->ins,
            'trans_category_id' => $tr_category->id,
            'tr_type' => $tr_category->code,
            'tr_ref' => $invoice->id,
            'is_primary' => 0,
            'user_type' => 'customer',
            'note' => $invoice->notes,
        ];

        // debit Customer Income
        $inc_account = Account::where('system', 'client_income')->first(['id']);
        $tr_data[] = array_replace($data, [
            'account_id' => $inc_account->id,
            'debit' => $invoice->subtotal,
            'is_primary' => 1,
        ]);
        // credit Revenue Account
        $tr_data[] = array_replace($data, [
            'account_id' => $invoice->account_id,
            'credit' => $invoice->subtotal,
        ]);

        // check if invoice has creditnote or debitnote
        if ($invoice->creditnotes->count()) {
            $subtotal = $invoice->creditnotes->sum('subtotal');
            // credit Customer Income
            $tr_data[] = array_replace($data, [
                'account_id' => $inc_account->id,
                'credit' => $subtotal,
                'is_primary' => 1,
            ]);
            // debit Revenue Account
            $tr_data[] = array_replace($data, [
                'account_id' => $invoice->account_id,
                'debit' => $subtotal,
            ]);
        } elseif ($invoice->debitnotes->count()) {
            $subtotal = $invoice->debitnotes->sum('subtotal');
            // debit Customer Income
            $tr_data[] = array_replace($data, [
                'account_id' => $inc_account->id,
                'debit' => $subtotal,
                'is_primary' => 1,
            ]);
            // credit Revenue Account
            $tr_data[] = array_replace($data, [
                'account_id' => $invoice->account_id,
                'credit' => $subtotal,
            ]);
        }

        // issued items
        $store_inventory_amount = 0;
        $dirpurch_inventory_amount = 0;
        $dirpurch_expense_amount = 0;
        foreach ($quotes as $quote) {
            $store_inventory_amount += $quote->issuance->sum('total');
            if (isset($quote->project_quote->project)) {
                foreach ($quote->project_quote->project->purchase_items as $item) {
                    $subttl = $item['amount'] - $item['taxrate'];
                    if ($item['type'] == 'Expense') $dirpurch_expense_amount += $subttl;
                    if ($item['type'] == 'Stock') $dirpurch_inventory_amount += $subttl;
                }
            }
        }
        // credit WIP account and debit COG
        $wip_account = Account::where('system', 'wip')->first(['id']);
        $cog_account = Account::where('system', 'cog')->first(['id']);
        $cr_data = array_replace($data, ['account_id' => $wip_account->id, 'is_primary' => 1]);
        $dr_data = array_replace($data, ['account_id' => $cog_account->id]);
        if ($dirpurch_inventory_amount > 0) {
            $tr_data[] = array_replace($cr_data, ['credit' => $dirpurch_inventory_amount]);
            $tr_data[] = array_replace($dr_data, ['debit' => $dirpurch_inventory_amount]);
        }  elseif ($dirpurch_expense_amount > 0) {
            $tr_data[] = array_replace($cr_data, ['credit' => $dirpurch_expense_amount]);
            $tr_data[] = array_replace($dr_data, ['debit' => $dirpurch_expense_amount]);
        } elseif ($store_inventory_amount > 0) {
            $tr_data[] = array_replace($cr_data, ['credit' => $store_inventory_amount]);
            $tr_data[] = array_replace($dr_data, ['debit' => $store_inventory_amount]);
        }

        $tr_data = array_map(function ($v) {
            if (isset($v['debit']) && $v['debit'] > 0) $v['credit'] = 0;
            elseif (isset($v['credit']) && $v['credit'] > 0) $v['debit'] = 0;
            return $v;
        }, $tr_data);
        Transaction::insert($tr_data);
        aggregate_account_transactions();       
    }
}