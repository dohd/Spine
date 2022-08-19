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
        return view('focus.utility-bills.edit', compact('utility_bill'));
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
     * 
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
     * 
     */
    public function store_kra_bill(Request $request)
    {
        $this->respository->create_kra($request->except('_token'));

        return new RedirectResponse(route('biller.utility-bills.index'), ['flash_success' => 'KRA Bill Created Successfully']);
    }
}
