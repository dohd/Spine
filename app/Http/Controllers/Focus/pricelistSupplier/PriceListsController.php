<?php

namespace App\Http\Controllers\Focus\pricelistSupplier;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\supplier_product\SupplierProduct;
use App\Models\supplier\Supplier;
use App\Repositories\Focus\pricelistSupplier\PriceListRepository;
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
        $supplier_id = auth()->user()->supplier_id;
        $suppliers = Supplier::when($supplier_id, fn ($q) => $q->where('id', $supplier_id))
        ->whereHas('supplier_products')->get(['id', 'company']);
        $contracts = SupplierProduct::get(['contract', 'supplier_id'])->unique('contract');
        $contracts = [...$contracts];

        return new ViewResponse('focus.pricelistsSupplier.index', compact('suppliers', 'contracts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $suppliers = Supplier::get(['id', 'company']);

        return new ViewResponse('focus.pricelistsSupplier.create', compact('suppliers'));
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
            $this->repository->create($request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler($th, 'Error creating Supplier Pricelist');
        }

        return new RedirectResponse(route('biller.pricelistsSupplier.index'), ['flash_success' => 'Pricelist Item Created Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $supplier_product = SupplierProduct::find($id);
        return view('focus.pricelistsSupplier.view', compact('supplier_product'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $supplier_product = SupplierProduct::find($id);
        $suppliers = Supplier::get(['id', 'company']);

        return view('focus.pricelistsSupplier.edit', compact('supplier_product', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        try {
            $supplier_product = SupplierProduct::find($id);
            $this->repository->update($supplier_product, $request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler($th, 'Error Updating Supplier Pricelist');
        }

        return new RedirectResponse(route('biller.pricelistsSupplier.index'), ['flash_success' => 'Pricelist Item Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        try {
            if ($id == 0) {
                $request->validate(['supplier_id' => 'required']);
                $this->repository->mass_delete($request->except('_token'));
            } else {
                $supplier_product = SupplierProduct::find($id);
                $this->repository->delete($supplier_product);    
            }
        } catch (\Throwable $th) {
            return errorHandler($th, 'Error Deleting Supplier Pricelist');
        }
            
        return new RedirectResponse(route('biller.pricelistsSupplier.index'), ['flash_success' => 'Pricelist Item Deleted Successfully']);
    }
}
