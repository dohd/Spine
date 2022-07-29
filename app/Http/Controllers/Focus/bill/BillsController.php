<?php

namespace App\Http\Controllers\Focus\bill;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\bill\Bill;
use App\Models\bill\Paidbill;
use App\Repositories\Focus\bill\BillRepository;
use Illuminate\Http\Request;

class BillsController extends Controller
{
    /**
     * variable to store the repository object
     * @var BillRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param BillRepository $repository ;
     */
    public function __construct(BillRepository $repository)
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
        return new ViewResponse('focus.bills.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $last_tid = Paidbill::max('tid');
        $accounts = Account::whereHas('accountType', function ($q) {
            $q->where('name', 'Bank');
        })->get();

        return new ViewResponse('focus.bills.create', compact('accounts', 'last_tid'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // extract input fields
        $bill = $request->only([
            'supplier_id', 'tid', 'date', 'due_date', 'payment_mode', 'deposit', 'doc_ref_type',
            'doc_ref', 'amount_ttl', 'deposit_ttl', 'account_id'
        ]);
        $bill_items = $request->only(['bill_id', 'paid']);

        $bill['ins'] = auth()->user()->ins;
        $bill['user_id'] = auth()->user()->id;

        // modify and filter paid bill
        $bill_items = modify_array($bill_items);
        $bill_items = array_filter($bill_items, function ($item) { return $item['paid']; });

        $result = $this->repository->create(compact('bill', 'bill_items'));

        return new RedirectResponse(route('biller.bills.index'), ['flash_success' => 'Bill payment successfully received']);
    }


    /**
     * Fetch Supplier bills
     */
    public function supplier_bills(Request $request)
    {
        $bills = Bill::where('supplier_id', $request->id)
            ->whereIn('status', ['Pending', 'Partial'])
            ->get([
                'id', 'tid', 'supplier_id', 'suppliername', 'doc_ref', 'doc_ref_type', 'note', 
                'status', 'grandttl', 'due_date', 'amountpaid'
            ]);

        return response()->json($bills);
    }

    /**
     * Show the form for creating KRA Bill
     */
    public function create_kra()
    {
        $tid = Bill::max('tid');
        return new ViewResponse('focus.bills.create_kra', compact('tid'));
    }

    /**
     * Store KRA bill in storage
     */
    public function store_kra(Request $request)
    {
        $data = $request->except('_token');

        $this->repository->create_kra($data);

        return new RedirectResponse(route('biller.bills.index'), ['flash_success' => 'KRA Bill created successfully']);
    }
}
