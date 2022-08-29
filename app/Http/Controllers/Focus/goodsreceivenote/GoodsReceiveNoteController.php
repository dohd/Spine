<?php

namespace App\Http\Controllers\Focus\goodsreceivenote;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Repositories\Focus\goodsreceivenote\GoodsreceivenoteRepository;
use Illuminate\Http\Request;

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
        return view('focus.goodsreceivenotes.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tid = Goodsreceivenote::max('tid');

        return view('focus.goodsreceivenotes.create', compact('tid'));
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

        return new RedirectResponse(route('biller.goodsreceivenote.index'), ['flash_success' => 'Goods Received Note Created Successfully']);
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
        return view('focus.goodsreceivenotes.edit', compact('goodsreceivenote'));
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
        $this->respository->update($goodsreceivenote, $request->except('_token'));

        return new RedirectResponse(route('biller.goodsreceivenote.index'), ['flash_success' => 'Goods Received Noe Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     * @return \Illuminate\Http\Response
     */
    public function destroy(Goodsreceivenote $goodsreceivenote)
    {
        $this->respository->delete($goodsreceivenote);

        return new RedirectResponse(route('biller.goodsreceivenote.index'), ['flash_success' => 'Goods Received Note Deleted Successfully']);
    }
}