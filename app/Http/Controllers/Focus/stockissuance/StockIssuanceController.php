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
        return view('focus.stockissuance.index');
    }

    /**
     * Show the form for issueing stock.
     *
     * @return \Illuminate\Http\Response
     */
    public function issue_stock(Request $request)
    {
        // extract input fields
        $input = (object) $request->only(['product_id', 'item_id', 'issue_qty']);

        DB::beginTransaction();

        $store_product = ProductVariation::find($input->product_id);
        // check if its a saved product, store product, product in stock
        if (!$input->item_id || !$input->issue_qty || !$store_product || ($store_product->qty < 1)) 
            return response()->noContent();
        if ($store_product->qty < $input->issue_qty) 
            $input->issue_qty = $store_product->qty;

        $budget_item = BudgetItem::find($input->item_id);
        // difference between approved qty and issued qty
        $diff = $budget_item->new_qty - $budget_item->issue_qty;        
        if ($diff > 0 && $input->issue_qty > $diff) {
            $input->issue_qty = $diff;
        }
        // reduce stock by input qty
        $store_product->decrement('qty', $input->issue_qty);
        // increament value of issued by input qty
        $budget_item->increment('issue_qty', $input->issue_qty);
        // store log
        $user = auth()->user();
        IssueItemLog::create([
            'item_id' => $input->item_id,
            'issue_qty' => $input->issue_qty,
            'issued_by' => $user->id,
            'issuer' => $user->first_name . ' ' . $user->last_name,
            'reqxn' => $request->reqxn,
            'warehouse' => $store_product->warehouse->title
        ]);

        DB::commit();

        return response()->json([
            'issued' => $input->issue_qty, 
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
        $budget->items = $budget->items()->orderBy('row_index', 'ASC')->get();

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
     * Delete log for issued stock item
     */
    public function delete_log(Request $request)
    {
        DB::beginTransaction();

        $log = IssueItemLog::find($request->id);
        $budget_item = BudgetItem::find($log->item_id);

        ProductVariation::find($budget_item->product_id)->increment('qty', $log->issue_qty);
        $budget_item->decrement('issue_qty', $log->issue_qty);

        $log->delete();

        DB::commit();

        return response()->json(['issue_qty' => $budget_item->issue_qty]);
    }

    /**
     * Post issued stock
     */
    public function post_issuedstock(Request $request)
    {
        // 
        return response()->json(['status' => 'Success', 'message' => 'Issued items successfully posted']);
    }

    /**
     * Budgeted quotes for stock issuance dataTable
     */
    static function getForDataTable()
    {
        $q = Quote::query();
        $q->whereHas('budget');
        
        if (request('start_date') && request('end_date')) {
            $q->whereBetween('date', [
                date_for_database(request('start_date')), 
                date_for_database(request('end_date'))
            ]);
        }

        return $q->get([
            'id', 'notes', 'tid', 'customer_id', 'branch_id', 'lead_id', 'date', 'total', 'status', 'bank_id'
        ]);
    }

    /**
     *  Budgeted Issued items for mergedLog dataTable
     */
    static function stockissuanceLogDataTable()
    {
        $q = IssueItemLog::with('budget_item');

        return $q->get();
    }
}
