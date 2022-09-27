<?php

namespace App\Http\Controllers\Focus\utility_bill;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\supplier\Supplier;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\Focus\utility_bill\UtilityBillRepository;
use Illuminate\Http\Request;

class UtilityBillController extends Controller
{
    /**
     * Store repository object
     * @var \App\Repositories\Focus\utility_bill\UtilityBillRepository
     */
    public $respository;

    public function __construct(UtilityBillRepository $repository)
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
        return view('focus.utility-bills.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $tid = UtilityBill::max('tid');
        $suppliers = Supplier::get(['id', 'name']);

        return view('focus.utility-bills.create', compact('tid', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->respository->create($request->except('_token'));

        return new RedirectResponse(route('biller.utility-bills.index'), ['flash_success' => 'Bill Created Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\utility_bill\UtilityBill $utility_bill
     * @return \Illuminate\Http\Response
     */
    public function show(UtilityBill $utility_bill)
    {
        return view('focus.utility-bills.view', compact('utility_bill'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\utility_bill\UtilityBill $utility_bill
     * @return \Illuminate\Http\Response
     */
    public function edit(UtilityBill $utility_bill)
    {
        $suppliers = Supplier::get(['id', 'name']);

        $doc_type = $utility_bill->document_type;
        if ($doc_type == 'direct_purchase') 
            return response()->redirectTo(route('biller.purchases.edit', $utility_bill->ref_id));
        elseif ($doc_type == 'opening_balance') 
            return response()->redirectTo(route('biller.suppliers.edit', $utility_bill->supplier));
        elseif ($doc_type == 'goods_receive_note' && $utility_bill->ref_id) 
            return response()->redirectTo(route('biller.goodsreceivenote.edit', $utility_bill->ref_id));

        return view('focus.utility-bills.edit', compact('utility_bill', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\utility_bill\UtilityBill $utility_bill
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UtilityBill $utility_bill)
    {
        $this->respository->update($utility_bill, $request->except('_token'));

        return new RedirectResponse(route('biller.utility-bills.index'), ['flash_success' => 'Bill Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\utility_bill\UtilityBill $utility_bill
     * @return \Illuminate\Http\Response
     */
    public function destroy(UtilityBill $utility_bill)
    {
        $this->respository->delete($utility_bill);

        return new RedirectResponse(route('biller.utility-bills.index'), ['flash_success' => 'Bill Deleted Successfully']);
    }

    /**
     * Create KRA Bill
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create_kra_bill(Request $request)
    {
        $tid = UtilityBill::max('tid');
        $suppliers = Supplier::get(['id', 'name']);

        return view('focus.utility-bills.create-kra', compact('tid', 'suppliers'));
    }

    /**
     * Store KRA Bill in storage
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_kra_bill(Request $request)
    {
        $this->respository->create_kra($request->except('_token'));

        return new RedirectResponse(route('biller.utility-bills.index'), ['flash_success' => 'KRA Bill Created Successfully']);
    }

    /**
     * Store KRA Bill in storage
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function goods_receive_note(Request $request)
    {
        $supplier = Supplier::find($request->supplier_id);
        $grn = $supplier->goods_receive_notes()->whereNull('invoice_no')->get();

        return response()->json($grn);
    }
}
