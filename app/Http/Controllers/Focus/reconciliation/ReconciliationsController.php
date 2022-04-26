<?php

namespace App\Http\Controllers\Focus\reconciliation;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\reconciliation\Reconciliation;
use App\Models\transaction\Transaction;
use App\Repositories\Focus\reconciliation\ReconciliationRepository;
use Illuminate\Http\Request;

class ReconciliationsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ReconciliationRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ReconciliationRepository $repository ;
     */
    public function __construct(ReconciliationRepository $repository)
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
        return new ViewResponse('focus.reconciliations.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // banks
        $accounts = Account::where(['account_type_id' => 6])
        ->whereIn('id', function ($q) {
            $q->select('account_id')->distinct()->from('transactions')->where('reconciliation_id', 0);
        })
        ->get(['id', 'holder']);

        $reconciliation = Reconciliation::orderBy('id', 'DESC')->first();
        // $reconciliation = null;

        return new ViewResponse('focus.reconciliations.create', compact('accounts', 'reconciliation'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //ectract input fields
        $data = $request->only(['account_id', 'tid', 'start_date', 'end_date', 'system_amount', 'open_amount', 'close_amount',]);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        $this->repository->create($data);

        return new RedirectResponse(route('biller.reconciliations.index'), ['flash_success' => 'Bank reconcilliaton successfully completed']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Reconciliation $reconciliation)
    {
        return new ViewResponse('focus.reconciliations.view', compact('reconciliation'));
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
    public function destroy($id)
    {
        //
    }

    /**
     * Ledger Transactions
     */
    public function ledger_transactions()
    {
        $tranxs = Transaction::where('account_id', request('id'))->get();

        return response()->json($tranxs);
    }
}
