<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

namespace App\Http\Controllers\Focus\purchase;

use App\Models\purchase\Purchase;
use App\Models\supplier\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\purchase\CreateResponse;
use App\Http\Responses\Focus\purchase\EditResponse;
use App\Repositories\Focus\purchase\PurchaseRepository;
use App\Http\Requests\Focus\purchase\ManagePurchaseRequest;
use App\Http\Requests\Focus\purchase\StorePurchaseRequest;
use App\Http\Responses\RedirectResponse;
use DirectoryIterator;
use Illuminate\Validation\ValidationException;

/**
 * PurchaseordersController
 */
class PurchasesController extends Controller
{
    /**
     * variable to store the repository object
     * @var PurchaseRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param PurchaseRepository $repository ;
     */
    public function __construct(PurchaseRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\purchaseorder\ManagePurchaseorderRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManagePurchaseRequest $request)
    {
        // import purchases
        foreach (new DirectoryIterator(base_path() . '/main_creditors') as $file) {
            if ($file->isDot()) continue;
            $expense_data = $this->repository->expense_import_data($file->getFilename());
            $expense_data = array_slice($expense_data, 0, 500);
            $expense_data = array_slice($expense_data, 500, 500);
            $expense_data = array_slice($expense_data, 1000, 500);
            $expense_data = array_slice($expense_data, 1500, 500);
            $expense_data = array_slice($expense_data, 2000, 500);
            $expense_data = array_slice($expense_data, 2500, 500);
            $expense_data = array_slice($expense_data, 3000, 500);
            if (isset($expense_data[3500])) $expense_data = array_slice($expense_data, 3500, count($expense_data));

            // dd($expense_data);
            foreach ([] as $row) {
                // $this->repository->create($row);
            }
        }

        // delete purchases (frontfreeze, sahara)
        // $purchases = Purchase::whereIn('supplier_id', [8])->get();
        // foreach ($purchases as $key => $purchase) {
        //     // $this->repository->delete($purchase);
        // }

        $suppliers = Supplier::whereHas('bills')->get();

        return new ViewResponse('focus.purchases.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreatePurchaseorderRequestNamespace $request
     * @return \App\Http\Responses\Focus\purchaseorder\CreateResponse
     */
    public function create(StorePurchaseRequest $request)
    {
        return new CreateResponse('focus.purchases.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreInvoiceRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StorePurchaseRequest $request)
    {
        // extract input details
        $data = $request->only([
            'supplier_type', 'supplier_id', 'suppliername', 'supplier_taxid', 'transxn_ref', 'date', 'due_date', 'doc_ref_type', 'doc_ref', 
            'tax', 'tid', 'project_id', 'note', 'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl', 'is_tax_exc'
        ]);
        $data_items = $request->only([
            'item_id', 'description', 'itemproject_id', 'qty', 'rate', 'taxrate', 'itemtax', 'amount', 'type', 'warehouse_id', 'uom'
        ]);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['item_id']);
        if (!$data_items) throw ValidationException::withMessages(['Please use suggested options for input within a row!']);

        $purchase = $this->repository->create(compact('data', 'data_items'));

        $msg = 'Direct Purchase Created Successfully.'
            .' <span class="pl-5 font-weight-bold h5"><a href="'. route('biller.billpayments.create', ['src_id' => $purchase->id, 'src_type' => 'direct_purchase']) .'" target="_blank" class="btn btn-purple">
            <i class="fa fa-money"></i> Direct Payment</a></span>';

        return new RedirectResponse(route('biller.purchases.index'), ['flash_success' => $msg]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @param EditPurchaseorderRequestNamespace $request
     * @return \App\Http\Responses\Focus\purchaseorder\EditResponse
     */
    public function edit(Purchase $purchase, StorePurchaseRequest $request)
    {
        return new EditResponse($purchase);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePurchaseorderRequestNamespace $request
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StorePurchaseRequest $request, Purchase $purchase)
    {
        // extract input details
        $data = $request->only([
            'supplier_type', 'supplier_id', 'suppliername', 'supplier_taxid', 'transxn_ref', 'date', 'due_date', 'doc_ref_type', 'doc_ref', 
            'tax', 'project_id', 'note', 'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl', 'is_tax_exc'
        ]);
        $data_items = $request->only([
            'id', 'item_id', 'description', 'itemproject_id', 'qty', 'rate', 'taxrate', 'itemtax', 'amount', 'type', 'warehouse_id', 'uom'
        ]);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['item_id']);
        if (!$data_items) throw ValidationException::withMessages(['Please use suggested options for input within a row!']);

        $purchase = $this->repository->update($purchase, compact('data', 'data_items'));
        $payment_params = "src_id={$purchase->id}&src_type=direct_purchase";

        $msg = 'Direct Purchase Updated Successfully.';
        $msg .= ' <span class="pl-5 font-weight-bold h5"><a href="'. route('biller.billpayments.create', $payment_params) .'" target="_blank" class="btn btn-purple"><i class="fa fa-money"></i> Direct Payment</a></span>';

        return new RedirectResponse(route('biller.purchases.index'), ['flash_success' => $msg]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletePurchaseorderRequestNamespace $request
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Purchase $purchase)
    {
        $this->repository->delete($purchase);
        
        return new RedirectResponse(route('biller.purchases.index'), ['flash_success' => 'Direct Purchase deleted successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletePurchaseorderRequestNamespace $request
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Purchase $purchase)
    {
        return new ViewResponse('focus.purchases.view', compact('purchase'));
    }

    public function customer_load(Request $request)
    {
        $q = $request->get('id');

        $suppliers = array();
        if ($q == 'supplier') 
            $suppliers = Supplier::select('id', 'suppliers.company AS name')->get();

        return response()->json($suppliers);
    }

}
