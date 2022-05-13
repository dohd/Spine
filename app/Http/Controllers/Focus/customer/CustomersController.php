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

use App\Http\Requests\Focus\general\CommunicationRequest;
use App\Models\account\Account;
use App\Models\customer\Customer;
use App\Models\transaction\TransactionHistory;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
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
use App\Models\invoice\Invoice;
use App\Models\transaction\Transaction;
use Redirect;

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
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required',
        ]);

        // extract input fields
        $input = $request->except(['_token', 'ins', 'balance']);

        $input['ins'] = auth()->user()->ins;
        if (!$request->password || strlen($request->password) < 6) 
            $input['password'] = rand(111111, 999999);

        $result = $this->repository->create($input);

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
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required',
        ]);
        // extract input fields
        $input = $request->except(['_token', 'ins', 'balance']);
        
        $result = $this->repository->update($customer, $input);

        $link = route('biller.customers.show', $customer);
        $msg = [
            'flash_success' => trans('alerts.backend.customers.updated') 
                . ' <a href="' . route('biller.customers.show', [$customer->id]) . '" class="ml-5 btn btn-outline-light round btn-min-width bg-blue"><span class="fa fa-eye" aria-hidden="true"></span> ' 
                . trans('general.view') . '  </a> &nbsp; &nbsp;' . ' <a href="' . route('biller.customers.create') . '" class="btn btn-outline-light round btn-min-width bg-purple"><span class="fa fa-plus-circle" aria-hidden="true"></span> ' 
                . trans('general.create') . '  </a>&nbsp; &nbsp;' . ' <a href="' . route('biller.customers.index') . '" class="btn btn-outline-blue round btn-min-width bg-amber"><span class="fa fa-list blue" aria-hidden="true"></span> <span class="blue">' 
                . trans('general.list') . '</span> </a>'
        ];
        if (!$result) return new RedirectResponse($link, '');

        return new RedirectResponse($link, $msg);
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
        $res = $this->repository->delete($customer);
        $link = route('biller.customers.index');

        if (!$res) return new RedirectResponse($link, ['flash_error' => 'Customer attached to Ticket']);

        return new RedirectResponse($link, ['flash_success' => trans('alerts.backend.customers.deleted')]);
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
        $invoices = Invoice::where('customer_id', $customer->id)->get();
        $transactions = Transaction::whereHas('account', function ($q) { 
            $q->where('system', 'receivable');  
        })->where(function ($q) use($customer) {
            $q->whereHas('invoice', function ($q) use($customer) { 
                $q->where('customer_id', $customer->id); 
            })->orwhereHas('paidinvoice', function ($q) use($customer) {
                $q->where('customer_id', $customer->id);
            })->orwhereHas('withholding', function ($q) use($customer) {
                $q->where('customer_id', $customer->id);
            })->orwhereHas('creditnote', function ($q) use($customer) {
                $q->where('customer_id', $customer->id);
            })->orwhereHas('debitnote', function ($q) use($customer) {
                $q->where('customer_id', $customer->id);
            });
        })->whereIn('tr_type', ['rcpt', 'pmt', 'withholding', 'cnote', 'dnote'])->get();
        
        return new ViewResponse('focus.customers.view', compact('customer', 'transactions', 'invoices'));
    }

    public function send_bill(CommunicationRequest $request)
    {

        $input = $request->only('text', 'subject', 'mail_to');
        $mailer = new RosemailerRepository;
        return $mailer->send($input['text'], $input);

    }

    public function selected_action(ManageCustomerRequest $request)
    {

        if (isset($request->cust)) {


            switch ($request->r_type) {
                case 'mail':
                    if (access()->allow('communication')) {
                        $customers_mails = Customer::whereIn('id', $request->cust)->get('email');
                        $input = array();
                        foreach ($customers_mails as $customer_mail) {
                            $input['email'][] = $customer_mail->email;
                        }
                        $input['subject'] = $request->subject;
                        $mailer = new RosemailerRepository;
                        return $mailer->send_group($request->text, $input);
                    }
                    break;
                case 'sms':
                    if (access()->allow('communication')) {
                        $customers_mails = Customer::whereIn('id', $request->cust)->get('phone');
                        foreach ($customers_mails as $customer_mail) {
                            $mailer = new RosesmsRepository;
                            $mailer->send_sms($customer_mail->phone, $request->text, false);
                        }
                        return array('status' => 'Success', 'message' => '');
                    }
                    break;
                case 'delete':
                    if (access()->allow('delete-customer')) {
                        $customers_mails = Customer::whereIn('id', $request->cust)->delete();
                        return array('status' => 'Success', 'message' => trans('alerts.backend.customers.deleted'));
                    }
                    break;
            }

        }


    }

    public function wallet(ManageCustomerRequest $request)
    {


        if ($request->post('amount') and $request->post('wid')) {
            $amount = numberClean($request->post('amount'));
            if ($amount > 0) {
                $customer = Customer::find($request->wid);
                $customer->balance = $customer->balance + $amount;
                $customer->save();
                $note = trans('transactions.wallet_recharge') . ' ' . amountFormat($amount);
                TransactionHistory::create(array('party_id' => $request->wid, 'user_id' => auth()->user()->id, 'note' => $note, 'relation_id' => 11, 'ins' => auth()->user()->ins));
                return new RedirectResponse(route('biller.customers.wallet') . '?rel_id=' . $request->wid, ['flash_success' => trans('transactions.wallet_updated')]);
            }
            return new RedirectResponse(route('biller.customers.wallet') . '?rel_id=' . $request->wid, ['flash_error' => trans('transactions.zero_amount')]);

        }

        if ($request->rel_id) {
            $customer = Customer::find($request->rel_id);
            $accounts = Account::all();

            return new ViewResponse('focus.customers.wallet', compact('customer', 'accounts'));
        }

    }

    public function wallet_transactions(ManageCustomerRequest $request)
    {
        $wallet_transactions = TransactionHistory::where(['relation_id' => 11, 'party_id' => $request->rel_id])->get();
        return new ViewResponse('focus.customers.wallet_history', compact('wallet_transactions'));
    }

    public function search(Request $request)
    {
        if (!access()->allow('crm')) return false;
        $q = $request->post('keyword');
        $user = \App\Models\customer\Customer::with('primary_group')->where('name', 'LIKE', '%' . $q . '%')->where('active', '=', 1)->orWhere('email', 'LIKE', '%' . $q . '')->orWhere('company', 'LIKE', '%' . $q . '')->limit(6)->get(array('id', 'name', 'phone', 'address', 'city', 'email','company'));
        if (count($user) > 0) return view('focus.customers.partials.search')->with(compact('user'));
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

    public function active(ManageCustomerRequest $request)
    {

        $cid = $request->post('cid');
        $active = $request->post('active');
        $active = !(bool)$active;
        Customer::where('id', '=', $cid)->update(array('active' => $active));
    }

}
