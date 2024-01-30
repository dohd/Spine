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
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\supplier\Supplier;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\Focus\supplier\SupplierRepository;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
        $request->validate([
            'password' => request('password') ? 'required_with:user_email | min:7' : '',
            'password_confirmation' => 'required_with:password | same:password'
        ]);
        if (request('password')) {
            if (!preg_match("/[a-z][A-Z]|[A-Z][a-z]/i", $request->password)) 
                throw ValidationException::withMessages(['password' => 'Password Must Contain Upper and Lowercase letters']);
            if (!preg_match("/[0-9]/i", $request->password)) 
                throw ValidationException::withMessages(['password' => 'Password Must Contain At Least One Number']);
        }

        $data = $request->only([
            'name', 'phone', 'email', 'address', 'city', 'region', 'country', 'postbox', 'email', 'picture',
            'company', 'taxid', 'docid', 'custom1', 'employee_id', 'active', 'password', 'role_id', 'remember_token',
            'contact_person_info'
        ]);
        $account_data = $request->only([
            'account_name', 'account_no', 'open_balance', 'open_balance_date', 'open_balance_note', 
        ]);
        $payment_data = $request->only(['bank', 'bank_code', 'payment_terms', 'credit_limit', 'mpesa_payment']);
        $user_data = $request->only('first_name', 'last_name', 'email', 'password', 'picture');
        $user_data['email'] = $request->user_email;

        try {
            $result = $this->repository->create(compact('data', 'account_data', 'payment_data', 'user_data'));

            if ($request->ajax()) {
                $result['random_password'] = null;
                return response()->json($result);
            } 
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Creating Supplier', $th);
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
        $accounts = Account::where('account_type', 'Expense')->get(['id', 'holder']);

        return view('focus.suppliers.edit', compact('supplier', 'accounts'));
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
        $request->validate([
            'password' => request('password') ? 'required_with:user_email | min:7' : '',
            'password_confirmation' => 'required_with:password | same:password'
        ]);
        if (request('password')) {
            if (!preg_match("/[a-z][A-Z]|[A-Z][a-z]/i", $request->password)) 
                throw ValidationException::withMessages(['password' => 'Password Must Contain Upper and Lowercase letters']);
            if (!preg_match("/[0-9]/i", $request->password)) 
                throw ValidationException::withMessages(['password' => 'Password Must Contain At Least One Number']);
        }

        $data = $request->only([
            'name', 'phone', 'email', 'address', 'city', 'region', 'country', 'postbox', 'email', 'picture',
            'company', 'taxid', 'docid', 'custom1', 'employee_id', 'active', 'password', 'role_id', 'remember_token',
            'contact_person_info'
        ]);
        $account_data = $request->only([
            'account_name', 'account_no', 'open_balance', 'open_balance_date', 'open_balance_note', 
        ]);
        $payment_data = $request->only(['bank', 'bank_code', 'payment_terms', 'credit_limit', 'mpesa_payment']);
        $user_data = $request->only('first_name', 'last_name', 'password', 'picture');
        $user_data['email'] = $request->user_email;

        try {
            $result = $this->repository->update($supplier, compact('data', 'account_data', 'payment_data', 'user_data'));
        } catch (\Throwable $th) { dd($th);
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Updating Supplier', $th);
        }        
       
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
        try {
            $this->repository->delete($supplier);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Supplier', $th);
        }

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
        // 5 date intervals of between 0 - 120+ days prior 
        $intervals = array();
        for ($i = 0; $i < 5; $i++) {
            $from = date('Y-m-d');
            $to = date('Y-m-d', strtotime($from . ' - 30 days'));
            if ($i > 0) {
                $prev = $intervals[$i-1][1];
                $from = date('Y-m-d', strtotime($prev . ' - 1 day'));
                $to = date('Y-m-d', strtotime($from . ' - 28 days'));
            }
            $intervals[] = [$from, $to];
        }

        // statement on bills 
        $bills = collect();
        $bills_statement = $this->repository->getStatementForDataTable($supplier->id);
        foreach ($bills_statement as $row) {
            if ($row->type == 'bill') $bills->add($row);
            else {
                $last_bill = $bills->last();
                if ($last_bill->bill_id == $row->bill_id) {
                    $last_bill->debit += $row->debit;
                }
            }
        }

        // aging balance from extracted invoices
        $aging_cluster = array_fill(0, 5, 0);
        foreach ($bills as $bill) {
            $due_date = new DateTime($bill->date);
            $debt_amount = $bill->credit - $bill->debit;
            // over payment
            if ($debt_amount < 0) {
                // $supplier->on_account += $debt_amount * -1;
                $debt_amount = 0;
            }
            // due_date between 0 - 120 days
            foreach ($intervals as $i => $dates) {
                $start  = new DateTime($dates[0]);
                $end = new DateTime($dates[1]);
                if ($start >= $due_date && $end <= $due_date) {
                    $aging_cluster[$i] += $debt_amount;
                    break;
                }
            }
            // due_date in 120+ days
            if ($due_date < new DateTime($intervals[4][1])) {
                $aging_cluster[4] += $debt_amount;
            }
        }

        // supplier debt balance
        $account_balance = collect($aging_cluster)->sum() - $supplier->on_account;

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
    public function select(Request $request)
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
        $purchase_orders = [];
        $supplier = Supplier::find(request('supplier_id'));
        if ($supplier) {
            // fetch grn purchase orders
            if (request('type') == 'grn') {
                $purchase_orders =  $supplier->purchase_orders()
                    ->whereIn('status', ['Pending', 'Partial'])
                    ->where('closure_status', 0)
                    ->get();
            } 
            else $purchase_orders =  $supplier->purchase_orders;
        }

        return response()->json($purchase_orders);
    }

    /**
     * Get Goods receive note
     */
    public function goods_receive_note()
    {
        $supplier = Supplier::find(request('supplier_id'));
        $grns = $supplier? $supplier->goods_receive_notes : [];

        return response()->json($grns);
    }

    /**
     * Get Supplier Bills
     */
    public function bills()
    {
        $bills = UtilityBill::where('supplier_id', request('supplier_id'))
            ->whereColumn('amount_paid', '<', 'total')
            ->with([
                'supplier' => fn($q) => $q->select('id', 'name'),
                'purchase' => fn($q) => $q->select('id', 'suppliername', 'note'),
                'grn' => fn($q) => $q->select('id', 'note'),
            ])
            ->orderBy('due_date', 'asc')->get()
            ->map(function ($v) {
                if ($v->document_type == 'direct_purchase') {
                    $v->suppliername = $v->purchase->suppliername;
                    if ($v->grn) unset($v->grn);
                } elseif ($v->document_type == 'goods_receive_note') {
                   if ($v->purchase) unset($v->purchase);
                }
                
                return $v;
            }); 
        
        return response()->json($bills);
    }
}
