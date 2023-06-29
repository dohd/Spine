<?php

namespace App\Http\Controllers\Focus\utility_bill;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\items\GoodsreceivenoteItem;
use App\Models\items\UtilityBillItem;
use App\Models\supplier\Supplier;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\Focus\utility_bill\UtilityBillRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
        $suppliers = Supplier::whereHas('bills')->get(['id', 'name']);

        return view('focus.utility-bills.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $tid = UtilityBill::where('ins', auth()->user()->ins)->max('tid');
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
        try {
            $this->respository->create($request->except('_token'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Creating Bill', $th);
        }

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
        elseif ($doc_type == 'advance_payment') 
            return response()->redirectTo(route('biller.advance_payments.edit', $utility_bill->ref_id));

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
        try {
            $this->respository->update($utility_bill, $request->except('_token'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Updating Bill', $th);
        }

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
        try {
            $this->respository->delete($utility_bill);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Deleting Bill', $th);
        }

        return new RedirectResponse(route('biller.utility-bills.index'), ['flash_success' => 'Bill Deleted Successfully']);
    }

    /**
     * Create KRA Bill
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create_kra_bill(Request $request)
    {
        $tid = UtilityBill::where('ins', auth()->user()->ins)->max('tid');
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
        try {
            $this->respository->create_kra($request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating KRA Bill', $th);
        }

        return new RedirectResponse(route('biller.utility-bills.index'), ['flash_success' => 'KRA Bill Created Successfully']);
    }

    /**
     * Goods Receive Note Items
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function goods_receive_note(Request $request)
    {
        $grn_items = GoodsreceivenoteItem::whereHas('goodsreceivenote', function ($q) {
            $q->whereNull('invoice_no')->whereHas('purchaseorder', fn($q) =>$q->where('supplier_id', request('supplier_id')));
        })->with([
            'purchaseorder_item' => fn($q) => $q->select(['id', 'description', 'uom']),
            'goodsreceivenote' => fn($q) => $q->select(['id', 'dnote', 'date']),
        ])->get()
        ->map(function($v) {
            $dnote = @$v->goodsreceivenote->dnote;
            $descr = @$v->purchaseorder_item->description;
            $uom = @$v->purchaseorder_item->uom;
            return [
                'id' => $v->id,
                'date' => $v->goodsreceivenote->date,
                'note' => "DNote:{$dnote} - {$descr} {$uom}",
                'qty' => $v->qty,
                'rate' => $v->rate,
                'tax' => $v->tax_rate,
                'total' => $v->tax_rate == 0? ($v->qty * $v->rate) : ($v->qty * $v->rate) * (1 + $v->tax_rate),
                'goodsreceivenote_id' => $v->goodsreceivenote->id,
            ];
        })->toArray();

        // decrement grn items qty by billed qty        
        $bill_items = UtilityBillItem::whereHas('bill', fn($q) => $q->where('supplier_id', request('supplier_id')))
            ->get()->toArray();
        foreach ($bill_items as $bill_item) {
            foreach ($grn_items as $i => $grn_item) {
                if ($grn_item['id'] == $bill_item['ref_id']) {
                    $grn_items[$i]['qty'] -= $bill_item['qty'];
                }
            }
        } 
        $grn_items = array_values(array_filter($grn_items, fn($v) => $v['qty'] > 0));
        
        return response()->json($grn_items);
    }

    /**
     * Employee bills
     */
    public function employee_bills()
    {
        $bills = UtilityBill::where('document_type', 'advance_payment')
            ->whereIn('ref_id', function ($q) {
                $q->select('employee_id')->from('advance_payments')->where('employee_id', request('employee_id'));
            })->get();

        return response()->json($bills);
    }
}
