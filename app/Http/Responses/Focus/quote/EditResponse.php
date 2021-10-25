<?php

namespace App\Http\Responses\Focus\quote;

use App\Models\customfield\Customfield;
use App\Models\items\CustomEntry;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\quote\Quote
     */
    protected $quote;

    /**
     * @param App\Models\quote\Quote $quote
     */
    public function __construct($quote)
    {
        $this->quote = $quote;
    }

    /**
     * To Response
     *
     * @param \App\Http\Requests\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function toResponse($request)
    {
        $fields = Customfield::where('module_id', '=', 4)->get()->groupBy('field_type');
        $fields_raw = array();

        if (isset($fields['text'])) {
            foreach ($fields['text'] as $row) {
                $data = CustomEntry::where('custom_field_id', '=', $row['id'])->where('module', '=', 4)->where('rid', '=', $this->quote->id)->first();
                $fields_raw['text'][] = array('id' => $row['id'], 'name' => $row['name'], 'default_data' => $data['data']);
            }
        }
        if (isset($fields['number'])) {
            foreach ($fields['number'] as $row) {
                $data = CustomEntry::where('custom_field_id', '=', $row['id'])->where('module', '=', 4)->where('rid', '=', $this->quote->id)->first();
                $fields_raw['number'][] = array('id' => $row['id'], 'name' => $row['name'], 'default_data' => $data['data']);
            }
        }

        $fields_data = custom_fields($fields_raw);

        $this->quote['validity'] = 14;

        browser_log($this->quote);

        return view('focus.quotes.edit')->with(['quote' => $this->quote])->with(bill_helper(2))->with(['fields_data' => $fields_data]);
    }

    public function get_name($lead) {
        if ($lead->client_status == "customer") {
            return $lead->customer->company.' '. $lead->branch->name;                                                                
        } 
        return $lead->client_name;    
    }
}
