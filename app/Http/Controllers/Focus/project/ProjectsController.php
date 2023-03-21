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

namespace App\Http\Controllers\Focus\project;

use App\Models\Company\ConfigMeta;
use App\Models\note\Note;
use App\Models\account\Account;
use App\Models\project\ProjectLog;
use App\Models\project\ProjectMileStone;
use App\Models\project\ProjectRelations;
use Illuminate\Http\Request;
use App\Repositories\Focus\invoice\InvoiceRepository;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\project\EditResponse;
use App\Repositories\Focus\project\ProjectRepository;
use App\Http\Requests\Focus\project\ManageProjectRequest;
use App\Http\Requests\Focus\project\CreateProjectRequest;
use App\Http\Requests\Focus\project\UpdateProjectRequest;
use App\Models\Access\User\User;
use App\Models\misc\Misc;
use App\Models\project\Budget;
use App\Models\project\Project;
use App\Models\quote\Quote;

use Yajra\DataTables\Facades\DataTables;

/**
 * ProjectsController
 */
class ProjectsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProjectRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProjectRepository $repository ;
     */
    public function __construct(ProjectRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\project\ManageProjectRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageProjectRequest $request)
    {
        $accounts = Account::where('account_type', 'Income')->get(['id', 'holder', 'number']);
        $last_tid = Project::where('ins', auth()->user()->ins)->max('tid');

        return new ViewResponse('focus.projects.index', compact('accounts', 'last_tid'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProjectRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(CreateProjectRequest $request)
    {
        // extract input fields from request
        $data = $request->only([
            'customer_id', 'branch_id', 'name', 'tid', 'status', 'priority', 'short_desc',
            'note', 'start_date', 'end_date', 'phase', 'worth', 'project_share', 'sales_account',
        ]);
        $data_items = array_merge([$request->main_quote], ...array_values($request->only('other_quote')));

        $data['ins'] = auth()->user()->ins;

        try {
            $this->repository->create(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return new RedirectResponse(route('biller.projects.index'), ['flash_error' => 'Error Creating Project']);
        }

        return new RedirectResponse(route('biller.projects.index'), ['flash_success' => trans('alerts.backend.projects.created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\project\Project $project
     * @param EditProjectRequestNamespace $request
     * @return \App\Http\Responses\Focus\project\EditResponse
     */
    public function edit(Project $project)
    {
        // $valid_project_creator = isset($project->creator) && $project->creator->id == auth()->user()->id;
        return new EditResponse($project);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProjectRequestNamespace $request
     * @param App\Models\project\Project $project
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        // extract input fields from request
        $data = $request->except(['_token', 'main_quote', 'other_quote']);
        $data_items = array_merge([$request->main_quote], ...array_values($request->only('other_quote')));

        try {
            $this->repository->update($project, compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return new RedirectResponse(route('biller.projects.index'), ['flash_error' => 'Error Updating Project']);
        }

        return new RedirectResponse(route('biller.projects.index'), ['flash_success' => trans('alerts.backend.projects.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProjectRequestNamespace $request
     * @param \App\Models\project\Project $project
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Project $project)
    {
        try {
            $this->repository->delete($project);
        } catch (\Throwable $th) {
            return new RedirectResponse(route('biller.projects.index'), ['flash_error' => 'Error Deleting Project']);
        }

        return new RedirectResponse(route('biller.projects.index'), ['flash_success' => trans('alerts.backend.projects.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProjectRequestNamespace $request
     * @param App\Models\project\Project $project
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Project $project, ManageProjectRequest $request)
    {
        $accounts = Account::where('account_type', 'Income')->get(['id', 'holder', 'number']);
        $last_tid = Project::where('ins', auth()->user()->ins)->max('tid');

        // temp properties
        $project->customer = $project->customer_project;
        $project->creator = auth()->user();

        $mics = Misc::all();
        $employees = User::all();

        $params = ['mics', 'employees'];

        return new ViewResponse('focus.projects.view', compact('project', 'accounts', 'last_tid', ...$params));
    }

    /**
     * show form to create resource
     * 
     * @param App\Models\quote\Quote quote
     */
    public function create_project_budget(Quote $quote)
    {
        $budget = Budget::where('quote_id', $quote->id)->first();
        if ($budget) return redirect(route('biller.projects.edit_project_budget', [$quote, $budget]));
        
        return view('focus.projects.create_project_budget', compact('quote'));
    }

    /**
     * show form to edit resource
     * 
     * @param App\Models\quote\Quote quote
     */
    public function edit_project_budget($quote_id, $budget_id)
    {
        $quote = Quote::find($quote_id);
        $budget = Budget::find($budget_id);
        $budget_items = $budget->items()->orderBy('row_index')->get();

        return view('focus.projects.edit_project_budget', compact('quote', 'budget', 'budget_items'));
    }

    /**
     * store a newly created resource
     * 
     * @param Request request
     */
    public function store_project_budget(Request $request)
    {
        // extract request input
        $data = $request->only('labour_total', 'budget_total', 'quote_id', 'quote_total', 'note');
        $data_items = $request->only(
            'numbering', 'row_index', 'a_type', 'product_id', 'product_name', 'product_qty', 'unit', 
            'new_qty', 'price'
        );
        $data_skillset = $request->only('skillitem_id', 'skill', 'charge', 'hours', 'no_technician');

        $data_items = modify_array($data_items);
        $data_skillset = modify_array($data_skillset);

        try {
            $this->repository->create_budget(compact('data', 'data_items', 'data_skillset'));
        } catch (\Throwable $th) {
            return new RedirectResponse(route('biller.projects.index'), ['flash_error' => 'Error Creating Budget']);
        }

        return new RedirectResponse(route('biller.projects.index'), ['flash_success' => 'Budget created successfully']);
    }

    /**
     * Update Project Budget resource in storage
     * 
     * @param Request request
     */
    public function update_project_budget(Request $request, Budget $budget)
    {
        // extract request input
        $data = $request->only('labour_total', 'budget_total', 'quote_id', 'quote_total', 'note');
        $data_items = $request->only(
            'item_id', 'numbering', 'row_index', 'a_type', 'product_id', 'product_name', 'product_qty', 'unit', 
            'new_qty', 'price'
        );
        $data_skillset = $request->only('skillitem_id', 'skill', 'charge', 'hours', 'no_technician');

        $data_items = modify_array($data_items);
        $data_skillset = modify_array($data_skillset);

        try {
            $this->repository->update_budget($budget, compact('data', 'data_items', 'data_skillset'));
        } catch (\Throwable $th) {
            return new RedirectResponse(route('biller.projects.index'), ['flash_error' => 'Error Updating Project Budget']);
        }

        return new RedirectResponse(route('biller.projects.index'), ['flash_success' => 'Project Budget updated successfully']);
    }

    /**
     * Update issuance tools and requisition
     */
    public function update_budget_tool(Request $request, Budget $budget)
    {
        $budget->update(['tool' => $request->tool, 'tool_reqxn' => $request->tool_reqxn]);

        return redirect()->back();
    }

    /**
     * Invoices Datatable
     */
    public function invoices(InvoiceRepository $invoice)
    {
        $core = $invoice->getForDataTable();

        return Datatables::of($core)
            ->addIndexColumn()
            ->addColumn('tid', function ($invoice) {
                return '<a class="font-weight-bold" href="' . route('biller.invoices.show', [$invoice->id]) . '">' . $invoice->tid . '</a>';
            })
            ->addColumn('customer', function ($invoice) {
                return $invoice->customer->name . ' <a class="font-weight-bold" href="' . route('biller.customers.show', [$invoice->customer->id]) . '"><i class="ft-eye"></i></a>';
            })
            ->addColumn('invoicedate', function ($invoice) {
                return dateFormat($invoice->invoicedate);
            })
            ->addColumn('total', function ($invoice) {
                return amountFormat($invoice->total);
            })
            ->addColumn('status', function ($invoice) {
                return '<span class="st-' . $invoice->status . '">' . trans('payments.' . $invoice->status) . '</span>';
            })
            ->addColumn('invoiceduedate', function ($invoice) {
                return dateFormat($invoice->invoiceduedate);
            })
            ->addColumn('actions', function ($invoice) {
                return $invoice->action_buttons;
            })->rawColumns(['tid', 'customer', 'actions', 'status', 'total'])
            ->make(true);
    }


    /**
     * Project autocomplete search
     */
    public function project_search(Request $request)
    {        
        if (!access()->allow('product_search')) return false;
        
        $k = $request->post('keyword');

        $projects = Project::whereHas('quote', function ($q) use($k) {
            $q->where('tid', $k);
        })->orWhereHas('branch', function ($q) use ($k) {
            $q->where('name', 'LIKE', '%'.$k.'%');
        })->orWhereHas('customer_project', function ($q) use ($k) {
            $q->where('company', 'LIKE', '%'.$k.'%');
        })->orwhere('name', 'LIKE', '%'.$k.'%')
        ->orWhere('tid', $k)         
        ->limit(6)->get();
        
        // response format
        $output = array();
        foreach ($projects as $project) {
            // if ($project->status == 'closed') continue;
            
            $quote_tids = array();
            foreach ($project->quotes as $quote) {
                if ($quote->bank_id) $quote_tids[] = gen4tid('PI-', $quote->tid);
                else $quote_tids[] = gen4tid('QT-', $quote->tid);
            }
            $quote_tids = implode(', ', $quote_tids);
            $quote_tids = "[{$quote_tids}]";

            $customer = $project->customer_project->company;
            $branch = $project->branch->name;
            $project_tid = gen4tid('Prj-', $project->tid);
            $output[] = [
                'id' => $project->id, 
                'name' => implode(' - ', [$quote_tids, $customer, $branch, $project_tid, $project->name]),
                'client_id' => $project->customer_project->id, 
                'branch_id' => $project->branch->id
            ];
        }

        return response()->json($output);
    }

    public function search(Request $request)
    {
        $q = $request->post('keyword');

        $projects = Project::where('tid', 'LIKE', '%' . $q . '%')
            ->orWhereHas('customer', function ($query) use ($q) {
                $query->where('company', 'LIKE', '%' . $q . '%');
                return $query;
            })->orWhereHas('branch', function ($query) use ($q) {
                $query->where('name', 'LIKE', '%' . $q . '%');
                return $query;
            })->limit(6)->get();


        if (count($projects) > 0) return view('focus.projects.partials.search')->with(compact('projects'));
    }

    /**
     * Projects select dropdown options
     */
    public function project_load_select(Request $request)
    {
        $q = $request->post('q');
        $projects = Project::where('name', 'LIKE', '%'.$q.'%')->limit(6)->get();

        return response()->json($projects);
    }
}
