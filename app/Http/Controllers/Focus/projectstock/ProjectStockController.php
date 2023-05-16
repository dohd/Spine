<?php

namespace App\Http\Controllers\Focus\projectstock;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\customer\Customer;
use App\Models\product\ProductVariation;
use App\Models\project\BudgetItem;
use App\Models\projectstock\Projectstock;
use App\Models\quote\Quote;
use App\Repositories\Focus\projectstock\ProjectStockRepository;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProjectStockController extends Controller
{
    /**
     * Store repository object
     * @var \App\Repositories\Focus\projectstock\ProjectStockRepository
     */
    public $respository;

    public function __construct(ProjectStockRepository $repository)
    {
        $this->respository = $repository;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('focus.projectstock.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $quote_id = $request->quote_id;
        if (!$quote_id) return new RedirectResponse(route('biller.projectstock.quote_index'), []);

        $tid = Projectstock::where('ins', auth()->user()->ins)->max('tid');
        $quote = Quote::find($quote_id);
        
        $budget_items = BudgetItem::where('a_type', 1)->whereHas('budget', function ($q) use($quote_id) { 
            $q->where('quote_id', $quote_id);
        })->with('product')->get();
        $stock = ProductVariation::select(DB::raw('parent_id, warehouse_id, SUM(qty) as qty'))
            ->groupBy(['parent_id', 'warehouse_id'])
            ->whereIn('id', $budget_items->pluck('product_id')->toArray())
            ->with('warehouse')->get();
        
        return view('focus.projectstock.create', compact('tid', 'quote', 'budget_items', 'stock'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->respository->create($request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Issuing Stock', $th);
        }

        return new RedirectResponse(route('biller.projectstock.index'), ['flash_success' => 'Project Stock Created Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\projectstock\Projectstock $projectstock
     * @return \Illuminate\Http\Response
     */
    public function show(Projectstock $projectstock)
    {
        return view('focus.projectstock.view', compact('projectstock'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\projectstock\Projectstock $projectstock
     * @return \Illuminate\Http\Response
     */
    public function edit(Projectstock $projectstock)
    {
        return redirect()->route('biller.projectstock.index');

        // to be updated
        $quote = Quote::find($projectstock->quote_id);
        if (!$quote) throw ValidationException::withMessages(['Related Quote/PI cannot be found']);

        $tid = Projectstock::where('ins', auth()->user()->ins)->max('tid');
        $budget_items = $quote->budget? $quote->budget->items()->with('product')->get() : collect();

        $stock = ProductVariation::select(DB::raw('parent_id, warehouse_id, SUM(qty) as qty'))
            ->groupBy('parent_id', 'warehouse_id')
            ->whereIn('id', $budget_items->pluck('product_id')->toArray())
            ->with('warehouse')
            ->get();

        return view('focus.projectstock.edit', compact('projectstock', 'tid', 'quote', 'budget_items', 'stock'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\projectstock\Projectstock $projectstock
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Projectstock $projectstock)
    {
        try {
            $this->respository->update($projectstock, $request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Project Stock', $th);
        }

        return new RedirectResponse(route('biller.projectstock.index'), ['flash_success' => 'Project Stock  Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\projectstock\Projectstock $projectstock
     * @return \Illuminate\Http\Response
     */
    public function destroy(Projectstock $projectstock)
    {
        try {
            $this->respository->delete($projectstock);
        } catch (\Throwable $th) {
            return errorHandler('Error deleting Project Stock', $th);
        }

        return new RedirectResponse(route('biller.projectstock.index'), ['flash_success' => 'Project Stock Deleted Successfully']);
    }

    /**
     * Project Stock Quotes / PIs
     */
    public function quote_index()
    {
        $customers = Customer::get(['id', 'company']);
        
        return view('focus.projectstock.quote', compact('customers'));
    }
}
