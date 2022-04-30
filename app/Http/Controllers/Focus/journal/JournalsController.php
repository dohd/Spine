<?php

namespace App\Http\Controllers\Focus\journal;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\manualjournal\Journal;
use App\Repositories\Focus\journal\JournalRepository;
use Illuminate\Http\Request;

class JournalsController extends Controller
{
    /**
     * variable to store the repository object
     * @var JournalRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param JournalRepository $repository ;
     */
    public function __construct(JournalRepository $repository)
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
        return new ViewResponse('focus.journals.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        $last_journal = Journal::orderBy('id', 'DESC')->first('tid');

        return new ViewResponse('focus.journals.create', compact('last_journal'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->only(['tid', 'date', 'note', 'debit_ttl', 'credit_ttl']);
        $data_items = $request->only(['account_id', 'debit', 'credit']);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        $data_items = modify_array($data_items);

        $this->repository->create(compact('data', 'data_items'));

        return new RedirectResponse(route('biller.journals.index'), ['flash_success' => 'Manual Journal created successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Journal $journal)
    {
        return new ViewResponse('focus.journals.view', compact('journal'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Journal $journal)
    {
        $this->repository->delete($journal);

        return new RedirectResponse(route('biller.journals.index'), ['flash_success' => 'Manual Journal deleted successfully']);
    }

    /**
     * Fetch journal accounts
     */
    public function journal_accounts()
    {
        $accounts = Account::where('is_manual_journal', 1)
            ->where('holder', 'LIKE', '%'. request('keyword') .'%')
            ->with(['accountType' => function ($q) {
                $q->select('id', 'category')->get();
            }])->get();

        return response()->json($accounts);
    }
}
