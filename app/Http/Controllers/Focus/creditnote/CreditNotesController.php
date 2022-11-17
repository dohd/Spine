<?php

namespace App\Http\Controllers\Focus\creditnote;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\creditnote\CreditNote;
use App\Repositories\Focus\creditnote\CreditNoteRepository;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        $is_debit = request('is_debit');
        return new ViewResponse('focus.creditnotes.index', compact('is_debit'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $is_debit = request('is_debit');
        $last_tid = CreditNote::max('tid');
        if ($is_debit == 1) $last_tid = CreditNote::where('is_debit', 1)->max('tid');
            
        return new ViewResponse('focus.creditnotes.create', compact('last_tid', 'is_debit'));
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
        $data = $request->except('_token', 'tax_id', 'amount');

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        $result = $this->repository->create($data);

        $msg = 'Credit Note created successfully';
        $route = route('biller.creditnotes.index');
        if ($result['is_debit']) {
            $msg = 'Debit Note created successfully';
            $route = route('biller.creditnotes.index', 'is_debit=1');
        }

        return new RedirectResponse($route, ['flash_success' => $msg]);
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
    public function edit(CreditNote $creditnote)
    {
        $is_debit = $creditnote->is_debit;
        foreach ($creditnote as $key => $val) {
            if (in_array($key, ['subtotal', 'tax', 'total'], 1)) {
                $creditnote[$key] = numberFormat($val);
            }
        }
        
        return new ViewResponse('focus.creditnotes.edit', compact('creditnote', 'is_debit'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CreditNote $creditnote, Request $request)
    {
        // extract input fields
        $data = $request->except('_token', 'tax_id', 'amount');

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        $this->repository->update($creditnote, $data);

        $msg = 'Credit Note updated successfully';
        $route = route('biller.creditnotes.index');
        if ($creditnote['is_debit']) {
            $msg = 'Debit Note updated successfully';
            $route = route('biller.creditnotes.index', 'is_debit=1');
        }

        return new RedirectResponse($route, ['flash_success' => $msg]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(CreditNote $creditnote)
    {
        $this->repository->delete($creditnote);

        $msg = 'Credit Note updated successfully';
        $route = route('biller.creditnotes.index');
        if ($creditnote['is_debit']) {
            $msg = 'Debit Note updated successfully';
            $route = route('biller.creditnotes.index', 'is_debit=1');
        }

        return new RedirectResponse($route, ['flash_success' => $msg]);
    }

    /**
     * Print Credit Note
     */
    public function print_creditnote(CreditNote $creditnote)
    {
        $html = view('focus.creditnotes.print_creditnote', ['resource' => $creditnote])->render();
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);
        $headers = array(
            "Content-type" => "application/pdf",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        return Response::stream($pdf->Output('creditnote.pdf', 'I'), 200, $headers);
    }    

    /**
     * Customer Invoices
     */
    public function customer_invoice()
    {
        $date = '';
        $last_month_day = (new DateTime($date))->format('Y-m-t');
        $date_6months_prior = date('Y-m-d', strtotime("{$last_month_day} -6 months")); 
        $first_month_day = substr_replace($date_6months_prior, '01', -2, 2);
    }
}
