<?php

namespace App\Http\Controllers\Focus\issuance;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\issuance\Issuance;
use App\Models\quote\Quote;
use App\Models\warehouse\Warehouse;
use App\Repositories\Focus\issuance\IssuanceRepository;
use Illuminate\Http\Request;

class IssuanceController extends Controller
{
    /**
     * variable to store the repository object
     * @var IssuanceRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param IssuanceRepository $repository ;
     */
    public function __construct(IssuanceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.issuance.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $quote = Quote::find(request('id'));
        $warehouses = Warehouse::whereHas('products', function ($q) {
            $q->where('qty', '>', 0);
        })->get();

        return new ViewResponse('focus.issuance.create', compact('quote', 'warehouses'));
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
        $data = $request->only(['quote_id', 'date', 'note', 'tool_ref', 'subtotal', 'tax', 'total']);
        $data_items = $request->only(['product_id', 'warehouse_id', 'qty', 'ref']);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function ($v) { return $v['qty'] > 0; });
        
        $result = $this->repository->create(compact('data', 'data_items'));

        $msg = ['flash_success' => 'Products issued successfully'];
        if (!$result) $msg = ['flash_error' => 'Products not in stock'];

        return new RedirectResponse(route('biller.issuance.index'), $msg);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Issuance $issuance)
    {
        return new ViewResponse('focus.issuance.view', compact('issuance'));
    }

    /**
     * Get issuance items
     */
    public function get_items()
    {
        $items = Issuance::find(request('id'))->items()
            ->with('product', 'warehouse')->get();

        return response()->json($items);
    }

    /**
     * Update issuance status
     */
    public function update_status(Request $request)
    {
        Issuance::find($request->id)->quote
            ->update(['issuance_status' => $request->status]);

        return redirect()->back();
    }
}
