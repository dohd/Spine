<?php

namespace App\Http\Controllers\Focus\journal;

use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\manualjournal\Journal;
use Illuminate\Http\Request;

class JournalsController extends Controller
{
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
        dd($request->all());

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
     * Fetch ledgers for select
     */
    public function journal_ledgers()
    {
        $accounts = Account::where('is_manual_journal', 1)->with(['accountType' => function ($q) {
            $q->select('id', 'category')->get();
        }])->get();

        return response()->json($accounts);
    }
}
