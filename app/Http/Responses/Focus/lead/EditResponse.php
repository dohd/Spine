<?php

namespace App\Http\Responses\Focus\lead;

use App\Models\customer\Customer;
use App\Models\branch\Branch;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\Lead
     */
    protected $lead;

    /**
     * @param App\Models\Lead $lead
     */
    public function __construct($lead)
    {
        $this->lead = $lead;
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
        $branch = Branch::find($this->lead['branch_id'], ['id', 'name']);
        $customer=Customer::where('employee_id', '=', $this->lead['employee_id'])->first(['id','name']);
            
        return view('focus.leads.edit')->with([
            'lead' => $this->lead,
            'branch' => $branch,
            'customer' => $customer
        ]);
    }
}