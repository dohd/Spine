<?php

namespace App\Http\Controllers\Focus\bills;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\bill\Bill;
use App\Repositories\Focus\bill\BillRepository;
use Illuminate\Http\Request;

class BillsController extends Controller
{
    /**
     * variable to store the repository object
     * @var BillRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param BillRepository $repository ;
     */
    public function __construct(BillRepository $repository)
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
        return new ViewResponse('focus.bills.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return new ViewResponse('focus.bills.create');
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
        $bill = $request->only([
            'supplier_id', 'tid', 'date', 'due_date', 'payment_mode', 'deposit', 'doc_ref_type',
            'doc_ref', 'amount_ttl', 'deposit_ttl'
        ]);
        $bill_items = $request->only(['bill_id', 'paid']);

        $bill['ins'] = auth()->user()->ins;
        $bill['user_id'] = auth()->user()->id;

        $bill_items = modify_array($bill_items);

        $result = $this->repository->create(compact('bill', 'bill_items'));

        return new RedirectResponse(route('biller.bills.index'), ['flash_success' => 'Bills payment successfully received']);
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Fetch Supplier bills
     */
    public function supplier_bills(Request $request)
    {
        $bills = Bill::where('supplier_id', $request->id)
            ->whereIn('status', ['Pending', 'Partial'])
            ->get(['id', 'tid', 'supplier_id', 'note', 'status', 'grandttl', 'due_date', 'amountpaid']);

        return response()->json($bills);
    }
}
