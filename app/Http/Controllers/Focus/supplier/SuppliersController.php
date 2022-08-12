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
namespace App\Http\Controllers\Focus\supplier;

use App\Http\Controllers\Controller;
use App\Http\Requests\Focus\purchaseorder\CreatePurchaseorderRequest;
use App\Http\Requests\Focus\supplier\ManageSupplierRequest;
use App\Http\Requests\Focus\supplier\StoreSupplierRequest;
use App\Http\Responses\Focus\supplier\CreateResponse;
use App\Http\Responses\Focus\supplier\EditResponse;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\supplier\Supplier;
use App\Repositories\Focus\supplier\SupplierRepository;
use DateTime;
use Request;

/**
 * SuppliersController
 */
class SuppliersController extends Controller
{
    /**
     * variable to store the repository object
     * @var SupplierRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param SupplierRepository $repository ;
     */
    public function __construct(SupplierRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\supplier\ManageSupplierRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageSupplierRequest $request)
    {
        return new ViewResponse('focus.suppliers.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateSupplierRequestNamespace $request
     * @return \App\Http\Responses\Focus\supplier\CreateResponse
     */
    public function create(StoreSupplierRequest $request)
    {
        return new CreateResponse('focus.suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreSupplierRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StoreSupplierRequest $request)
    {
        // extract request input
        $data = $request->only([
            'name', 'phone', 'email', 'address', 'city', 'region', 'country', 'postbox', 'email', 'picture',
            'company', 'taxid', 'docid', 'custom1', 'employee_id', 'active', 'password', 'role_id', 'remember_token',
            'contact_person_info'
        ]);
        $account_data = $request->only([
            'account_name', 'account_no', 'open_balance', 'open_balance_date', 'open_balance_note', 
            'expense_account_id'
        ]);
        $payment_data = $request->only(['bank', 'bank_code', 'payment_terms', 'credit_limit', 'mpesa_payment']);

        $data['ins'] = auth()->user()->ins;

        $result = $this->repository->create(compact('data', 'account_data', 'payment_data'));

        if ($request->ajax()) {
            $result['random_password'] = null;
            return response()->json($result);
        } 

        return new RedirectResponse(route('biller.suppliers.index'), ['flash_success' => trans('alerts.backend.suppliers.created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\supplier\Supplier $supplier
     * @param EditSupplierRequestNamespace $request
     * @return \App\Http\Responses\Focus\supplier\EditResponse
     */
    public function edit(Supplier $supplier, StoreSupplierRequest $request)
    {
        return new EditResponse($supplier);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateSupplierRequestNamespace $request
     * @param App\Models\supplier\Supplier $supplier
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StoreSupplierRequest $request, Supplier $supplier)
    {
        // extract request input
        $data = $request->only([
            'name', 'phone', 'email', 'address', 'city', 'region', 'country', 'postbox', 'email', 'picture',
            'company', 'taxid', 'docid', 'custom1', 'employee_id', 'active', 'password', 'role_id', 'remember_token',
            'contact_person_info'
        ]);
        $account_data = $request->only([
            'account_name', 'account_no', 'open_balance', 'open_balance_date', 'open_balance_note', 
            'expense_account_id'
        ]);
        $payment_data = $request->only(['bank', 'bank_code', 'payment_terms', 'credit_limit', 'mpesa_payment']);

        $result = $this->repository->update($supplier, compact('data', 'account_data', 'payment_data'));        
       
        return new RedirectResponse(route('biller.suppliers.index'), ['flash_success' => trans('alerts.backend.suppliers.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteSupplierRequestNamespace $request
     * @param App\Models\supplier\Supplier $supplier
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Supplier $supplier)
    {
        $this->repository->delete($supplier);

        return new RedirectResponse(route('biller.suppliers.index'), ['flash_success' => trans('alerts.backend.suppliers.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteSupplierRequestNamespace $request
     * @param App\Models\supplier\Supplier $supplier
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Supplier $supplier, ManageSupplierRequest $request)
    {
        // 4 date intervals of between 0 - 120 days earlier 
        $intervals = array();
        for ($i = 0; $i < 4; $i++) {
            $from = date('Y-m-d');
            $to = date('Y-m-d', strtotime($from . ' - 30 days'));
            if ($i > 0) {
                $prev = $intervals[$i-1][1];
                $from = date('Y-m-d', strtotime($prev . ' - 1 day'));
                $to = date('Y-m-d', strtotime($from . ' - 28 days'));
            }
            $intervals[] = [$from, $to];
        }

        // total debt and aging balance
        $account_balance = 0;
        $aging_cluster = array_fill(0, 4, 0);
        $bills = $this->repository->getBillsForDataTable($supplier->id);
        foreach ($bills as $bill) {
            $debt_amount = $bill->grandttl - $bill->amountpaid;
            $due_date = new DateTime($bill->date);
            // due_date between 0 - 120 days
            foreach ($intervals as $i => $dates) {
                $start  = new DateTime($dates[0]);
                $end = new DateTime($dates[1]);
                if ($start >= $due_date && $end <= $due_date) {
                    $aging_cluster[$i] += $debt_amount;
                    break;
                }
            }
            // due_date in 120 days plus
            if ($due_date < new DateTime($intervals[3][1])) {
                $aging_cluster[3] += $debt_amount;
            }
            $account_balance += $debt_amount;
        }

        return new ViewResponse('focus.suppliers.view', compact('supplier', 'account_balance', 'aging_cluster'));
    }

    public function search(CreatePurchaseorderRequest $request)
    {
        $q = $request->post('keyword');
        $user = Supplier::where('name', 'LIKE', '%' . $q . '%')
            ->where('active', 1)
            ->orWhere('email', 'LIKE', '%' . $q . '')
            ->limit(6)->get(['id', 'name', 'phone', 'address', 'city', 'email']);

        return view('focus.suppliers.partials.search')->with(compact('user'));
    }

    /**
     * Supllier select dropdown
     */
    public function select(ManageSupplierRequest $request)
    {
        $q = $request->keyword;
        $suppliers = Supplier::where('name', 'LIKE', '%'.$q.'%')
            ->where('active', 1)->orWhere('email', 'LIKE', '%'.$q.'')
            ->limit(6)->get(['id', 'name', 'phone', 'address', 'city', 'email', 'taxid']);

        return response()->json($suppliers);
    }

    public function active(ManageSupplierRequest $request)
    {

        $cid = $request->post('cid');
        $active = $request->post('active');
        $active = !(bool)$active;
        Supplier::where('id', '=', $cid)->update(array('active' => $active));
    }

    /**
     * Get Purchase Orders
     */
    public function purchaseorders()
    {
        $supplier = Supplier::find(request('supplier_id'));

        return response()->json($supplier->purchase_orders);
    }

    /**
     * Get Goods receive note
     */
    public function goodsreceivenote()
    {
        $supplier = Supplier::find(request('supplier_id'));

        return response()->json($supplier->goodsreceivenotes);
    }

    /**
     * Get due bills
     */
    public function due_bills()
    {
        $supplier = Supplier::find(request('supplier_id'));

        return response()->json($supplier->due_bills);
    }
}
