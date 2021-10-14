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

namespace App\Http\Controllers\Focus\djc;

use App\Models\djc\Djc;
use App\Models\Company\ConfigMeta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\djc\CreateResponse;
use App\Http\Responses\Focus\djc\EditResponse;
use App\Repositories\Focus\djc\DjcRepository;
use App\Http\Requests\Focus\djc\ManageDjcRequest;
use App\Models\branch\Branch;
use App\Models\customer\Customer;
use App\Models\items\DjcItem;
use App\Models\lead\Lead;
use Illuminate\Support\Facades\Response;
use mPDF;

/**
 * DjcsController
 */
class DjcsController extends Controller
{
    /**
     * variable to store the repository object
     * @var AccountRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param AccountRepository $repository ;
     */
    public function __construct(DjcRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\account\ManageAccountRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageDjcRequest $request)
    {

        return new ViewResponse('focus.djcs.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateAccountRequestNamespace $request
     * @return \App\Http\Responses\Focus\account\CreateResponse
     */
    public function create(ManageDjcRequest $request)
    {
        return new CreateResponse('focus.djcs.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreAccountRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(ManageDjcRequest $request)
    {
        $request->validate([
            'technician' => 'required',
            'title' => 'required'
        ]);

        $data = $request->only(['tid', 'lead_id', 'client_id', 'branch_id', 'reference', 'technician', 'action_taken', 'root_cause', 'recommendations', 'subject', 'prepared_by', 'attention', 'region', 'report_date', 'image_one', 'image_two', 'image_three', 'image_four', 'caption_one', 'caption_two', 'caption_three', 'caption_four']);
        $data_item = $request->only(['tag_number', 'joc_card', 'equipment_type', 'make', 'capacity', 'location', 'last_service_date', 'next_service_date']);
        $data['ins'] = auth()->user()->ins;

        //Create the model using repository create method
        $id = $this->repository->create(compact('data', 'data_item'));

        return new RedirectResponse(route('biller.djcs.index'), ['flash_success' => trans('alerts.djc.report.created')]);

        //return with successfull message
        //return new RedirectResponse(route('biller.djcs.show', [$id]), ['flash_success' => 'Djc Report Created' . ' <a href="' . route('biller.djcs.show', [$id]) . '" class="ml-5 btn btn-outline-light round btn-min-width bg-blue"><span class="fa fa-eye" aria-hidden="true"></span> ' . trans('general.view') . '  </a> &nbsp; &nbsp;' . ' <a href="' . route('biller.djcs.create') . '" class="btn btn-outline-light round btn-min-width bg-purple"><span class="fa fa-plus-circle" aria-hidden="true"></span> ' . trans('general.create') . '  </a>&nbsp; &nbsp;' . ' <a href="' . route('biller.djcs.index') . '" class="btn btn-outline-blue round btn-min-width bg-amber"><span class="fa fa-list blue" aria-hidden="true"></span> <span class="blue">' . trans('general.list') . '</span> </a>']);
        // echo json_encode(array('status' => 'Success', 'message' => trans('alerts.backend.quotes.created') . ' <a href="' . route('biller.djcs.show', [$result->id]) . '" class="btn btn-primary btn-md"><span class="fa fa-eye" aria-hidden="true"></span> ' . trans('general.view') . '  </a> &nbsp; &nbsp;'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\account\Account $account
     * @param EditAccountRequestNamespace $request
     * @return \App\Http\Responses\Focus\account\EditResponse
     */
    public function edit(Djc $djc, ManageDjcRequest $request)
    {
        return new EditResponse('focus.djcs.edit', compact('djc', 'lead'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateAccountRequestNamespace $request
     * @param App\Models\account\Account $account
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(ManageDjcRequest $request, Djc $djc)
    {
        $request->validate([
            'number' => 'required',
            'holder' => 'required'
        ]);
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($djc, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.djcs.index'), ['flash_success' => trans('alerts.backend.accounts.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteAccountRequestNamespace $request
     * @param App\Models\account\Account $account
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Djc $djc, ManageDjcRequest $request)
    {
        //Calling the delete method on repository
        $this->repository->delete($djc);
        //returning with successfull message
        return new RedirectResponse(route('biller.djcs.index'), ['flash_success' => trans('alerts.backend.accounts.deleted')]);
    }

    /**
     * View the specified resource from storage
     * 
     * @param App\Models\djc\Djc $djc
     * @return \App\Http\Responses\ViewResponse
     */
    public function show(Djc $djc)
    {
        $lead = Lead::find($djc->lead_id, ['id', 'client_name', 'note']);
        $branch = Branch::find($djc->branch_id, ['id', 'name']);
        $customer = Customer::find($djc->client_id, ['id', 'name']);
        $djc_items = DjcItem::where('djc_id', '=', $djc->id)->get();

        return new ViewResponse('focus.djcs.view', compact('djc', 'lead', 'branch', 'customer', 'djc_items'));
    }

    // account search
    public function account_search(Request $request, $bill_type)
    {
        if (!access()->allow('product_search')) return false;

        $q = $request->post('keyword');
        $w = $request->post('wid');
        $s = $request->post('serial_mode');
        if ($bill_type == 'label') $q = @$q['term'];
        $wq = compact('q', 'w');

        $account = Account::where('holder', 'LIKE', '%' . $q . '%')
            ->where('account_type', 'Expenses')
            ->orWhere('number', 'LIKE', '%' . $q . '%')->limit(6)->get();
        $output = array();

        foreach ($account as $row) {
            if ($row->id > 0) {
                $output[] = array('name' => $row->holder . ' - ' . $row->number, 'id' => $row['id']);
            }
        }
        if (count($output) > 0)
            return view('focus.products.partials.search')->withDetails($output);
    }

    // balance sheet
    public function balance_sheet(Request $request)
    {
        $bg_styles = array('bg-gradient-x-info', 'bg-gradient-x-purple', 'bg-gradient-x-grey-blue', 'bg-gradient-x-danger', 'bg-gradient-x-success', 'bg-gradient-x-warning');
        $account = Account::all();
        $account_types = ConfigMeta::withoutGlobalScopes()->where('feature_id', '=', 17)->first('value1');
        $account_types = json_decode($account_types->value1, true);
        if ($request->type == 'v') {
            return new ViewResponse('focus.accounts.balance_sheet', compact('account', 'bg_styles', 'account_types'));
        } else {
            $html = view('focus.accounts.print_balance_sheet', compact('account', 'account_types'))->render();
            $pdf = new \Mpdf\Mpdf(config('pdf'));
            $pdf->WriteHTML($html);
            $headers = array(
                "Content-type" => "application/pdf",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            );
            return Response::stream($pdf->Output('balance_sheet.pdf', 'I'), 200, $headers);
        }
    }

    // trial balance
    public function trial_balance(Request $request)
    {
        $bg_styles = array('bg-gradient-x-info', 'bg-gradient-x-purple', 'bg-gradient-x-grey-blue', 'bg-gradient-x-danger', 'bg-gradient-x-success', 'bg-gradient-x-warning');
        $account = Account::orderBy('number', 'asc')->get();
        $account_types = ConfigMeta::withoutGlobalScopes()->where('feature_id', '=', 17)->first('value1');
        $account_types = json_decode($account_types->value1, true);
        if ($request->type == 'v') {
            return new ViewResponse('focus.accounts.trial_balance', compact('account', 'bg_styles', 'account_types'));
        } else {

            $html = view('focus.accounts.print_balance_sheet', compact('account', 'account_types'))->render();
            $pdf = new \Mpdf\Mpdf(config('pdf'));
            $pdf->WriteHTML($html);
            $headers = array(
                "Content-type" => "application/pdf",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            );
            return Response::stream($pdf->Output('balance_sheet.pdf', 'I'), 200, $headers);
        }
    }
}
