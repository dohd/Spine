<?php

namespace App\Http\Controllers\Focus\stockissuance;

use App\Http\Controllers\Controller;
use App\Models\product\ProductVariation;
use App\Models\project\Budget;
use App\Models\project\BudgetItem;
use App\Models\quote\Quote;
use App\Models\stock\IssueItemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockIssuanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // extract input fields
        $relshp_type = request('rel_type');
        $relshp_id = request('rel_id');

        $segment = array();
        $words = array();
        if ($relshp_type && $relshp_id) {
            if ($relshp_type == 1) {
                $segment = Customer::find($relshp_id);
                $words['name'] = trans('customers.title');
                $words['name_data'] = $segment->name;
            }
            else {
                $segment = Hrm::find($relshp_id);
                $words['name'] = trans('hrms.employee');
                $words['name_data'] = $segment->first_name . ' ' . $segment->last_name;
            }
        }

        $input = array($relshp_type, $relshp_id);

        return view('focus.stockissuance.index', compact('input', 'segment', 'words'));
    }

    /**
     * Show the form for issueing stock.
     *
     * @return \Illuminate\Http\Response
     */
    public function issue_stock(Request $request)
    {
        // extract input fields
        $product_id = $request->product_id;
        $item_id = $request->item_id;
        $input_qty = $request->issue_qty;

        DB::beginTransaction();

        $store_product = ProductVariation::find($product_id);
        // check if its a saved product, store product, product in stock
        if (!$item_id || !$store_product || !intval($store_product->qty) || !$input_qty) 
            return response()->noContent();
        if ($store_product->qty < $input_qty) $input_qty = $store_product->qty;

        $budget_item = BudgetItem::find($item_id);
        // difference between approved qty and issued qty
        $diff = $budget_item->new_qty - $budget_item->issue_qty;        
        if ($diff > 0 && $input_qty > $diff) {
            $input_qty = $diff;
        }
        // reduce stock by input qty
        $store_product->decrement('qty', $input_qty);
        // increament value of issued by input qty
        $budget_item->increment('issue_qty', $input_qty);
        // store log
        $user = auth()->user();
        IssueItemLog::create([
            'item_id' => $item_id,
            'issue_qty' => $input_qty,
            'issued_by' => $user->id,
            'issuer' => $user->first_name . ' ' . $user->last_name,
            'reqxn' => $request->reqxn
        ]);

        DB::commit();

        return response()->json([
            'issued' => $input_qty, 
            'issue_qty' => $budget_item->issue_qty
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $query_str = $request->getQueryString();
        $quote_id = explode('=', $query_str)[0];

        $quote = Quote::find($quote_id);
        $budget = Budget::where('quote_id', $quote->id)->first();

        return view('focus.stockissuance.issue_stock', compact('quote', 'budget'));
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
        $input = $request->only([
            'numbering', 'product_name', 'unit', 'price', 'new_qty', 'product_id', 
            'row_index', 'budget_id', 'a_type'
        ]);

        $input['price'] = numberClean($input['price']);
        $budget_item = BudgetItem::create($input);        

        return response()->json($budget_item);
    }

    /**
     * Get log for issued stock item
     */
    public function get_issued_log($id)
    {
        $logs = IssueItemLog::where('item_id', $id)->orderBy('id', 'desc')->get();

        return response()->json($logs);
    }

    /**
     * Budgeted quotes for stock issuance
     */
    static function getForDataTable()
    {
        $q = Quote::query();
        // Budgeted quotes
        $quote_ids = Budget::get()->pluck('quote_id');
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
            'id', 'notes', 'tid', 'customer_id', 'branch_id', 'lead_id', 'invoicedate', 'invoiceduedate', 
            'total', 'status', 'bank_id'
        ]);
    }
}
