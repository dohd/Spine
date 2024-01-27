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
        $last_tid = Reconciliation::max('tid');
        // banks
        $accounts = Account::where('account_type_id', 6)
        ->whereHas('transactions', fn ($q) => $q->where('reconciliation_id', 0))
        ->get();
        
        return new ViewResponse('focus.reconciliations.create', compact('accounts', 'last_tid'));
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
        $data = $request->only(['account_id', 'tid', 'start_date', 'end_date', 'system_amount', 'open_amount', 'close_amount']);
        $data_items = $request->only('id', 'is_reconciled');

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        $data_items = modify_array($data_items);

        try {
            $this->repository->create(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Bank reconcilliaton', $th);
        }

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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reconciliation $reconciliation)
    {
        try {
            $this->repository->delete($reconciliation);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Reconciliation', $th);
        }

        return new RedirectResponse(route('biller.reconciliations.index'), ['flash_sucess' => 'Reconciliation deleted successfully']);
    }

    /**
     * Ledger account transactions
     */
    public function ledger_transactions()
    {
        // all transaction types except deposit (opening balance) 
        $transactions = Transaction::where('account_id', request('id'))
            ->where('reconciliation_id', 0)->get();
            
        return response()->json($transactions);
    }

    /**
     * Last Account reconciliatiom
     */
    public function last_reconciliation()
    {
        $reconciliation =  Reconciliation::where('account_id', request('id'))->orderBy('id', 'Desc')->first();

        return response()->json($reconciliation);
    }
}
