<?php

namespace App\Http\Controllers\Focus\billpayment;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\Access\User\User;
use App\Models\account\Account;
use App\Models\billpayment\Billpayment;
use App\Models\supplier\Supplier;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\Focus\billpayment\BillPaymentRepository;
use DirectoryIterator;
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
        // create purchases (frontfreeze, sahara)
        // foreach (new DirectoryIterator(base_path() . '/main_creditors') as $file) {
        //     if ($file->isDot()) continue;
        //     $expense_data = $this->repository->expense_import_data($file->getFilename());
        //     // dd($expense_data);
        //     foreach ($expense_data as $row) {
        //         // $this->repository->create($row);
        //     }
        // }

        // delete purchases (frontfreeze, sahara)
        // $billpayments = Billpayment::get();
        // foreach ($billpayments as $key => $purchase) {
        //     // $this->repository->delete($purchase);
        // }

        $suppliers = Supplier::get(['id', 'name']);
        return view('focus.billpayments.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $tid = Billpayment::where('ins', auth()->user()->ins)->max('tid');
        $accounts = Account::whereNull('system')
            ->whereHas('accountType', fn($q) =>  $q->where('system', 'bank'))
            ->get(['id', 'holder']);

        $suppliers = Supplier::get(['id', 'name']);
        $employees = User::get();

        $direct_bill = [];
        $params = $request->only(['src_id', 'src_type']);
        if (count($params) == 2) {
            $bill = UtilityBill::where([
                'ref_id' => $params['src_id'],
                'document_type' => $params['src_type'],
                'status' => 'due'
            ])->first();
            
            if ($params['src_type'] == 'direct_purchase') {
                if (!$bill) {
                    return redirect(route('biller.purchases.index'))
                    ->with(['flash_error' => 'Bill not available for direct payment.']);
                }
            }
                
            $direct_bill = [
                'tid' => $bill->tid,
                'supplier_id' => $bill->supplier_id,
                'amount' => $bill->total,
            ];
        }

        return view('focus.billpayments.create', compact('tid', 'accounts', 'suppliers', 'employees', 'direct_bill'));
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
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\billpayment\Billpayment $billpayment
     * @return \Illuminate\Http\Response
     */
    public function edit(Billpayment $billpayment)
    {
        $suppliers = Supplier::get(['id', 'name']);
        $employees = User::get();
        $accounts = Account::whereNull('system')
        ->whereHas('accountType', function ($q) {
            $q->where('system', 'bank');
        })->get(['id', 'holder']);


        return view('focus.billpayments.edit', compact('billpayment', 'accounts', 'suppliers', 'employees'));
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
}
