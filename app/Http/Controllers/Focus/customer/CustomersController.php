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
namespace App\Http\Controllers\Focus\customer;

use App\Models\customer\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\customer\CreateResponse;
use App\Http\Responses\Focus\customer\EditResponse;
use App\Repositories\Focus\customer\CustomerRepository;
use App\Http\Requests\Focus\customer\ManageCustomerRequest;
use App\Http\Requests\Focus\customer\CreateCustomerRequest;
use App\Http\Requests\Focus\customer\EditCustomerRequest;
use App\Models\Company\Company;
use DateTime;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

/**
 * CustomersController
 */
class CustomersController extends Controller
{
    /**
     * variable to store the repository object
     * @var CustomerRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param CustomerRepository $repository ;
     */
    public function __construct(CustomerRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\customer\ManageCustomerRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.customers.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateCustomerRequestNamespace $request
     * @return \App\Http\Responses\Focus\customer\CreateResponse
     */
    public function create(CreateCustomerRequest $request)
    {

        return new CreateResponse('focus.customers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCustomerRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(CreateCustomerRequest $request)
    {
        $request->validate([
            'company' => 'required',
        ]);

        // extract input fields
        $input = $request->except(['_token', 'ins', 'balance']);

        $input['ins'] = auth()->user()->ins;
        if (!$request->password || strlen($request->password) < 6) 
            $input['password'] = rand(111111, 999999);

        try {
            $result = $this->repository->create($input);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Creating Customers', $th);
        }

        if (!$result) return redirect()->back();
        // case ajax request
        $result['random_password'] = $input['password'];
        if ($request->ajax()) return response()->json($result);
            
        return new RedirectResponse(route('biller.customers.index'), ['flash_success' => trans('alerts.backend.customers.created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\customer\Customer $customer
     * @param EditCustomerRequestNamespace $request
     * @return \App\Http\Responses\Focus\customer\EditResponse
     */
    public function edit(Customer $customer, EditCustomerRequest $request)
    {
        return new EditResponse($customer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCustomerRequestNamespace $request
     * @param App\Models\customer\Customer $customer
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(EditCustomerRequest $request, Customer $customer)
    {
        $request->validate([
            'company' => 'required',
        ]);
        // extract input fields
        $input = $request->except(['_token', 'ins', 'balance']);
        
        try {
            $this->repository->update($customer, $input);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Updating Customers', $th);
        }

        return new RedirectResponse(route('biller.customers.show', $customer), ['flash_success' => trans('alerts.backend.customers.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteCustomerRequestNamespace $request
     * @param App\Models\customer\Customer $customer
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Customer $customer)
    {
        try {
            $this->repository->delete($customer);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Customers', $th);
        }

        return new RedirectResponse(route('biller.customers.index'), ['flash_success' => trans('alerts.backend.customers.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteCustomerRequestNamespace $request
     * @param App\Models\customer\Customer $customer
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Customer $customer, ManageCustomerRequest $request)
    {
        // extract invoice from customer statement
        $invoices = $this->statement_invoices($customer);

        // aging balance from extracted invoices
        $aging_cluster = $this->aging_cluster($customer, $invoices);

        // customer debt balance
        $account_balance = collect($aging_cluster)->sum() - $customer->on_account;

        return new ViewResponse('focus.customers.view', compact('customer', 'aging_cluster', 'account_balance'));
    }

    /**
     * Customer Statement Invoices
     */
    public function statement_invoices($customer)
    {
        $invoices = collect();
        $statement = $this->repository->getStatementForDataTable($customer->id);
        foreach ($statement as $row) {
            if ($row->type == 'invoice') $invoices->add($row);
            else {
                $last_invoice = $invoices->last();
                if ($last_invoice->invoice_id == $row->invoice_id) {
                    $last_invoice->credit += $row->credit;
                }
            }
        }

        return $invoices;
    }


    /**
     * Aging report from customer statement invoices
     */
    public function aging_cluster($customer, $invoices)
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

        // aging balance from extracted invoices
        $aging_cluster = array_fill(0, 5, 0);
        foreach ($invoices as $invoice) {
            $due_date = new DateTime($invoice->date);
            $debt_amount = $invoice->debit - $invoice->credit;
            // over payment
            if ($debt_amount < 0) {
                // $customer->on_account += $debt_amount * -1;
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

        return $aging_cluster;
    }

    /**
     * Customer search options
     */
    public function search(Request $request)
    {
        if (!access()->allow('crm')) return false;
        
        $k = $request->post('keyword');
        $user = Customer::with('primary_group')->where('active', 1)->where(function ($q) use($k) {
            $q->where('name', 'LIKE', '%' . $k . '%')
            ->orWhere('email', 'LIKE', '%' . $k . '')
            ->orWhere('company', 'LIKE', '%' . $k . '');
        })->limit(6)->get(['id', 'name', 'phone', 'address', 'city', 'email','company']);
            
        return view('focus.customers.partials.search')->with(compact('user'));
    }

    /**
     * Fetch cutomers for dropdown select options
     */
    public function select(Request $request)
    {
        if (!access()->allow('crm')) 
            return response()->json(['message' => 'Insufficient privileges'], 403);

        $q = $request->search;
        $customers = Customer::where('name', 'LIKE', '%'.$q.'%')
            ->orWhere('company', 'LIKE', '%'.$q.'%')
            ->limit(6)->get();

        return response()->json($customers);
    }

    /**
     * Print customer statements
     */
    public function print_statement(Request $request, $customer_id)
    {   
        // dd($customer_id);
        $page = '';
        $params = [];
        if ($request->type == 1) {
            // statement on account
            $page = 'focus.customers.statements.print_statement_on_account';

            $transactions = $this->repository->getTransactionsForDataTable($customer_id)->sortBy('tr_date');
            $start_date = request('start_date', date('Y-m-d'));
            $company = Company::find(auth()->user()->ins);
            $customer = Customer::find($customer_id);

            $statement_invoices = $this->statement_invoices($customer);
            $aging_cluster = $this->aging_cluster($customer, $statement_invoices);
            
            $params = compact('transactions', 'start_date', 'company', 'customer', 'aging_cluster');
        } elseif ($request->type == 2) {
            // statement on invoice
            $page = 'focus.customers.statements.print_statement_on_invoice';

            $inv_statements = $this->repository->getStatementForDataTable($customer_id);
            $start_date = request('start_date', date('Y-m-d'));
            $company = Company::find(auth()->user()->ins);
            $customer = Customer::find($customer_id);

            $statement_invoices = $this->statement_invoices($customer);
            $aging_cluster = $this->aging_cluster($customer, $statement_invoices);

            $params = compact('inv_statements', 'start_date', 'company', 'customer', 'aging_cluster');
        }
        
        $html = view($page, $params)->render();
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);
        $headers = array(
            "Content-type" => "application/pdf",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );
        return Response::stream($pdf->Output('statement_on_account' . '.pdf', 'I'), 200, $headers);
    }

}
