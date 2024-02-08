<?php

namespace App\Http\Controllers\Focus\goodsreceivenote;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\additional\Additional;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\supplier\Supplier;
use App\Repositories\Focus\goodsreceivenote\GoodsreceivenoteRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GoodsReceiveNoteController extends Controller
{
    /**
     * Store repository object
     * @var \App\Repositories\Focus\goodsreceivenote\GoodsreceivenoteRepository
     */
    public $respository;

    public function __construct(GoodsreceivenoteRepository $repository)
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
        $supplier_id = auth()->user()->supplier_id;
        $suppliers = Supplier::when($supplier_id, fn($q) => $q->where('id', $supplier_id))
        ->whereHas('goods_receive_notes')
        ->get(['id', 'name']);

        return view('focus.goodsreceivenotes.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tid = Goodsreceivenote::where('ins', auth()->user()->ins)->max('tid');
        $suppliers = Supplier::get(['id', 'name']);
        $additionals = Additional::get();

        return view('focus.goodsreceivenotes.create', compact('tid', 'suppliers', 'additionals'));
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
            $grn = $this->respository->create($request->except('_token'));
            $msg = 'Goods Received Note Created Successfully With DNote';
            if ($grn->invoice_no) $msg = 'Goods Received Note Created Successfully With Invoice';
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Creating Goods Received Note', $th);
        }

        return new RedirectResponse(route('biller.goodsreceivenote.index'), ['flash_success' => $msg]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     * @return \Illuminate\Http\Response
     */
    public function show(Goodsreceivenote $goodsreceivenote)
    {
        return view('focus.goodsreceivenotes.view', compact('goodsreceivenote'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     * @return \Illuminate\Http\Response
     */
    public function edit(Goodsreceivenote $goodsreceivenote)
    {
        $suppliers = Supplier::get(['id', 'name']);
        $additionals = Additional::get();

        return view('focus.goodsreceivenotes.edit', compact('goodsreceivenote', 'suppliers', 'additionals'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Goodsreceivenote $goodsreceivenote)
    {
        try {
            $this->respository->update($goodsreceivenote, $request->except('_token'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Updating Goods Received Note', $th);
        }

        return new RedirectResponse(route('biller.goodsreceivenote.index'), ['flash_success' => 'Goods Received Note Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     * @return \Illuminate\Http\Response
     */
    public function destroy(Goodsreceivenote $goodsreceivenote)
    {
        try {
            $this->respository->delete($goodsreceivenote);
        } catch (\Throwable $th) { 
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Deleting Goods Received Note', $th);
        }
        return new RedirectResponse(route('biller.goodsreceivenote.index'), ['flash_success' => 'Goods Received Note Deleted Successfully']);
    }
}
