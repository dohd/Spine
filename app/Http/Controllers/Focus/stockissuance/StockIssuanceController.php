<?php

namespace App\Http\Controllers\Focus\stockissuance;

use App\Http\Controllers\Controller;
use App\Models\product\ProductVariation;
use App\Models\project\Budget;
use App\Models\project\BudgetItem;
use App\Models\quote\Quote;
use Illuminate\Http\Request;

class StockIssuanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $input = $request->only('rel_type', 'rel_id');
        
        $segment = array();
        $words = array();
        if (isset($input['rel_id']) and isset($input['rel_type'])) {
            switch ($input['rel_type']) {
                case 1:
                    $segment = Customer::find($input['rel_id']);
                    $words['name'] = trans('customers.title');
                    $words['name_data'] = $segment->name;
                    break;
                case 2:
                    $segment = Hrm::find($input['rel_id']);
                    $words['name'] = trans('hrms.employee');
                    $words['name_data'] = $segment->first_name . ' ' . $segment->last_name;
                    break;
            }
        }

        return view('focus.stockissuance.index', compact('input', 'segment', 'words'));
    }

    /**
     * Show the form for issueing stock.
     *
     * @return \Illuminate\Http\Response
     */
    public function issue_stock(Quote $quote)
    {
        $budget = Budget::where('quote_id', $quote->id)->first();

        return view('focus.stockissuance.issue_stock', compact('quote', 'budget'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // extract input fields
        $item = $request->only([
            'numbering', 'item_id', 'budget_id', 'product_name', 'unit', 'new_qty', 'issue_qty', 'price',
            'row_index', 'product_id', 'a_type',
        ]);

        // decreament issued item from stock
        if ($item['product_id'])
            ProductVariation::find($item['product_id'])->decrement('qty', $item['issue_qty']); 

        // update or create new item
        $budget_item = BudgetItem::firstOrNew([
            'id' => $item['item_id'],
            'budget_id' => $item['budget_id'],
        ]);
        // assign properties to budget_item
        foreach($item as $key => $value) {
            if ($key == 'price') $budget_item[$key] = numberClean($value);
            else $budget_item[$key] = $value;
        }
        unset($budget_item['item_id']);
        if ($budget_item['id'] == 0) unset($budget_item['id']);
        $budget_item->save();

        return response()->json($budget_item);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    // Stock verification data
    static function getForDataTable()
    {
        $q = Quote::query();

        $q->when(request('i_rel_type') == 1, function ($q) {
            return $q->where('customer_id', request('i_rel_id', 0));
        });

        if (request('start_date') && request('end_date')) {
            $q->whereBetween('invoicedate', [
                date_for_database(request('start_date')), 
                date_for_database(request('end_date'))
            ]);
        }

        // Budgeted quotes
        $quote_ids = Budget::get()->pluck('quote_id');
        $q->whereIn('id', $quote_ids);

        return $q->get([
            'id', 'notes', 'tid', 'customer_id', 'branch_id', 'lead_id', 'invoicedate', 'invoiceduedate', 
            'total', 'status', 'bank_id'
        ]);
    }
}
