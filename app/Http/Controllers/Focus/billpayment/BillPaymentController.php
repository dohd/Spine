<?php

namespace App\Http\Controllers\Focus\billpayment;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\account\Account;
use App\Models\billpayment\Billpayment;
use App\Repositories\Focus\billpayment\BillPaymentRepository;
use Illuminate\Http\Request;

class BillPaymentController extends Controller
{
    /**
     * Store repository object
     * @var \App\Repositories\Focus\billpayment\BillPaymentRepository
     */
    public $respository;

    public function __construct(BillPaymentRepository $repository)
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
        return view('focus.billpayments.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $tid = Billpayment::max('tid');
        $accounts = Account::whereHas('accountType', function ($q) {
            $q->where('system', 'bank');
        })->get(['id', 'holder']);

        return view('focus.billpayments.create', compact('tid', 'accounts'));
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

        return new RedirectResponse(route('biller.billpayments.index'), ['flash_success' => 'Bill Payment Created Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\billpayment\Billpayment $billpayment
     * @return \Illuminate\Http\Response
     */
    public function show(Billpayment $billpayment)
    {
        return view('focus.billpayments.view', compact('billpayment'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\billpayment\Billpayment $billpayment
     * @return \Illuminate\Http\Response
     */
    public function edit(Billpayment $billpayment)
    {
        $accounts = Account::whereHas('accountType', function ($q) {
            $q->where('system', 'bank');
        })->get(['id', 'holder']);

        return view('focus.billpayments.edit', compact('billpayment', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\billpayment\Billpayment $billpayment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Billpayment $billpayment)
    {
        $this->respository->update($billpayment, $request->except('_token'));

        return new RedirectResponse(route('biller.billpayments.index'), ['flash_success' => 'Bill Payment Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\billpayment\Billpayment $billpayment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Billpayment $billpayment)
    {
        $this->respository->delete($billpayment);

        return new RedirectResponse(route('biller.billpayments.index'), ['flash_success' => 'Bill Payment Deleted Successfully']);
    }
}
