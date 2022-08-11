<?php

namespace App\Http\Controllers\Focus\supplierbill;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\supplier\Supplier;
use App\Models\supplierbill\Supplierbill;
use App\Repositories\Focus\supplierbill\SupplierBillRepository;
use Illuminate\Http\Request;

class SupplierBillController extends Controller
{
    /**
     * Store repository object
     * @var \App\Repositories\Focus\supplierbill\SupplierBillRepository
     */
    public $respository;

    public function __construct(SupplierBillRepository $repository)
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
        return view('focus.supplierbills.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $data = $request->only(['supplier_id', 'row_ids']);
        if (empty($data['row_ids'])) return new RedirectResponse(route('biller.supplierbills.goodsreceivenote'), []);

        $grn_ids = explode(',', $data['row_ids']);
        $goodsreceivenotes = Goodsreceivenote::whereIn('id', $grn_ids)->get();
        $supplier = Supplier::find($data['supplier_id']);
        $tid = Supplierbill::max('tid');

        return view('focus.supplierbills.create', compact('tid', 'goodsreceivenotes', 'supplier'));
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

        return new RedirectResponse(route('biller.supplierbills.index'), ['flash_success' => 'Supplier Bill Created Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\supplierbill\Supplierbill $supplierbill
     * @return \Illuminate\Http\Response
     */
    public function show(Supplierbill $supplierbill)
    {
        return view('focus.supplierbills.view', compact('supplierbill'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\supplierbill\Supplierbill $supplierbill
     * @return \Illuminate\Http\Response
     */
    public function edit(Supplierbill $supplierbill)
    {
        return view('focus.supplierbills.edit', compact('supplierbill'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\supplierbill\Supplierbill $supplierbill
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Supplierbill $supplierbill)
    {
        $this->respository->update($supplierbill, $request->except('_token'));

        return new RedirectResponse(route('biller.supplierbills.index'), ['flash_success' => 'Supplier Bill Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\supplierbill\Supplierbill $supplierbill
     * @return \Illuminate\Http\Response
     */
    public function destroy(Supplierbill $supplierbill)
    {
        $this->respository->delete($supplierbill);

        return new RedirectResponse(route('biller.supplierbills.index'), ['flash_success' => 'Supplier Bill Deleted Successfully']);
    }

    /**
     * Display a listing of goods receive notes.
     *
     * @return \Illuminate\Http\Response
     */
    public function goodsreceivenote()
    {
        $suppliers = Supplier::all();

        return view('focus.supplierbills.goodsreceivenote', compact('suppliers'));
    }
}
