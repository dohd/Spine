<?php

namespace App\Http\Controllers\Focus\pricelist;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\pricegroup\Pricegroup;
use App\Models\pricelist\PriceList;
use App\Repositories\Focus\pricelist\PriceListRepository;
use Illuminate\Http\Request;

class PriceListsController extends Controller
{
    /**
     * variable to store the repository object
     * @var PriceListRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param PriceListRepository $repository ;
     */
    public function __construct(PriceListRepository $repository)
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
        $pricegroups = Pricegroup::all();

        return new ViewResponse('focus.pricelists.index', compact('pricegroups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pricegroups = Pricegroup::all();

        return new ViewResponse('focus.pricelists.create', compact('pricegroups'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // extract request input
        $data = $request->only('pricegroup_id');
        $data_items = $request->only('product_id', 'name', 'price');

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        $data_items = modify_array($data_items);

        $this->repository->create(compact('data', 'data_items'));

        return new RedirectResponse(route('biller.pricelists.index'), ['flash_success' => 'Price List added successfully']);
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
    public function destroy(PriceList $pricelist)
    {
        $this->repository->delete($pricelist);

        return new RedirectResponse(route('biller.pricelists.index'), ['flash_success' => 'Price list item deleted successfully']);
    }
}
