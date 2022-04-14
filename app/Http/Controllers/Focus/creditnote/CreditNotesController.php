<?php

namespace App\Http\Controllers\Focus\creditnote;

use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Models\creditnote\CreditNote;
use App\Repositories\Focus\creditnote\CreditNoteRepository;
use Illuminate\Http\Request;
use PayPal\Api\Credit;

class CreditNotesController extends Controller
{
    /**
     * variable to store the repository object
     * @var CreditNoteRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param CreditNoteRepository $repository ;
     */
    public function __construct(CreditNoteRepository $repository)
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
        return new ViewResponse('focus.creditnotes.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $is_debit = request('is_debit');

        $last_cn = CreditNote::where('customer_id', '>', 0)
            ->orderBy('id', 'DESC')->first(['tid']);

        if ($is_debit == 1) {
            $last_cn = CreditNote::where('supplier_id', '>', 0)
                ->orderBy('id', 'DESC')->first(['tid']);
        }

        return new ViewResponse('focus.creditnotes.create', compact('last_cn', 'is_debit'));
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
        $data = $request->except('_token', 'tax_id');

        $data = $data + [
            'ins' => auth()->user()->ins,
            'user_id' => auth()->user()->id,
        ];

        $result = $this->repository->create($data);

        return new ViewResponse('focus.creditnotes.index', ['flash_success' => 'Credit Note created successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
}
