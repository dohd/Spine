<?php

namespace App\Http\Responses\Focus\purchase;

use App\Models\project\Budget;
use App\Models\purchase\Purchase;
use Illuminate\Contracts\Support\Responsable;

class CreateResponse implements Responsable
{
    /**
     * To Response
     *
     * @param \App\Http\Requests\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function toResponse($request)
    {
        $bill_types = bill_helper(3, 9);

        // extract projects having all quotes budgeted
        $projects = array();
        $budgeted_quote_ids = Budget::get()->pluck('quote_id')->toArray();
        foreach($bill_types['projects'] as $project) {
            $quote_ids = $project->quotes->pluck('id')->toArray();
            $unbudgeted_ids = array_diff($quote_ids, $budgeted_quote_ids);
            if (empty($quote_ids) || $unbudgeted_ids) continue;
            // append tids
            $lead_tids = array();
            $quote_tids = array();                
            foreach ($project->quotes as $quote) {
                $lead_tids[] = 'Tkt-' . sprintf('%04d', $quote->lead->reference);
                // quote
                $quote_tid = sprintf('%04d', $quote->tid);
                if ($quote->bank_id) $quote_tids[] = 'PI-'. $quote_tid;
                else $quote_tids[] = 'QT-'. $quote_tid;
            }
            $project['quote_tids'] = implode(', ', $quote_tids);
            $project['lead_tids'] =  implode(', ', $lead_tids);
            $projects[] = $project;
        }
        $bill_types['projects'] = $projects;  

        // assign last_id to resource being created    
        $bill_types['last_id'] = Purchase::orderBy('id', 'desc')->first();

        return view('focus.purchases.create', $bill_types);
    }
}
