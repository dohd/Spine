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
use App\Models\project\Budget;
use App\Models\project\BudgetItem;
use App\Models\project\BudgetSkillset;
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
        $last_tid = Project::max('tid');

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

        $this->repository->create(compact('data', 'data_items'));

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

        $this->repository->update($project, compact('data', 'data_items'));

        return new RedirectResponse(route('biller.projects.index'), ['flash_success' => trans('alerts.backend.projects.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProjectRequestNamespace $request
     * @param App\Models\project\Project $project
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Project $project)
    {
        $this->repository->delete($project);

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
        $last_tid = Project::max('tid');

        return new ViewResponse('focus.projects.view', compact('project', 'accounts', 'last_tid'));
    }

    /**
     * show form to create resource
     * 
     * @param App\Models\quote\Quote quote
     */
    public function create_project_budget(Quote $quote)
    {
        $budget = Budget::where('quote_id', $quote->id)->first();
        if (isset($budget))
            return redirect(route('biller.projects.edit_project_budget', [$quote, $budget]));

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
        $data = $request->only('labour_total', 'budget_total', 'quote_id', 'quote_total', 'tool');
        $data_items = $request->only(
            'numbering', 'row_index', 'a_type', 'product_id', 'product_name', 'product_qty', 'unit', 
            'new_qty', 'price'
        );
        $data_skillset = $request->only('skillitem_id', 'skill', 'charge', 'hours', 'no_technician');

        $data_items = modify_array($data_items);
        $data_skillset = modify_array($data_skillset);

        $this->repository->create_budget(compact('data', 'data_items', 'data_skillset'));

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
        $data = $request->only('labour_total', 'budget_total', 'quote_id', 'quote_total', 'tool');
        $data_items = $request->only(
            'item_id', 'numbering', 'row_index', 'a_type', 'product_id', 'product_name', 'product_qty', 'unit', 
            'new_qty', 'price'
        );
        $data_skillset = $request->only('skillitem_id', 'skill', 'charge', 'hours', 'no_technician');

        $data_items = modify_array($data_items);
        $data_skillset = modify_array($data_skillset);

        $this->repository->update_budget($budget, compact('data', 'data_items', 'data_skillset'));

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
     * Delete Project Quote Budget item in storage
     * 
     * @param int $id
     */
    public function delete_budget_item($id)
    {
        BudgetItem::find($id)->delete();

        return response()->noContent();
    }

    /**
     * Delete Project Quote Budget skillset in storage
     * 
     * @param int $id
     */
    public function delete_budget_skillset($id)
    {
        BudgetSkillset::find($id)->delete();

        return response()->noContent();
    }

    public function store_meta(ManageProjectRequest $request)
    {

        $input = $request->except(['_token', 'ins']);
        if (!project_access($input['project_id'])) exit;
        switch ($input['obj_type']) {
            case 2:
                $milestone = ProjectMileStone::create(array('project_id' => $input['project_id'], 'name' => $input['name'], 'note' => $input['description'], 'color' => $input['color'], 'due_date' => date_for_database($input['duedate']) . ' ' . $input['time_to'] . ':00', 'user_id' => auth()->user()->id));
                $result = '<li class=" " id="m_' . $milestone->id . '">
                                    <div class="timeline-badge" style="background-color: ' . $milestone->color . ' ;">*</div>
                                    <div class="timeline-panel">
                                        <div class="timeline-heading">
                                            <h4 class="timeline-title">' . $milestone->name . '</h4>
                                            <p>
                                                <small class="text-muted"> [ ' . trans('general.due_date') . ' ' . dateTimeFormat($milestone->due_date) . ']
                                                </small>

                                            </p>
                                        </div>';

                $result .= '<div class="timeline-body mb-1">
                                            <p> ' . $milestone->note . '</p><a href="#" class=" delete-object" data-object-type="2" data-object-id="' . $milestone->id . '"><i class="danger fa fa-trash"></i></a>
                                        </div>';

                $result .= '<small class="text-muted"><i class="fa fa-user"></i> <strong>' . $milestone->creator->first_name . ' ' . $milestone->creator->last_name . '</strong>  <i class="fa fa-clock-o"></i>  ' . trans('general.created') . '  ' . dateTimeFormat($milestone->created_at) . '
                                                </small>
                                    </div>
                                </li>';
                ProjectLog::create(array('project_id' => $milestone->project_id, 'value' => '[' . trans('projects.milestone') . '] ' . '[' . trans('general.new') . '] ' . $input['name'], 'user_id' => auth()->user()->id));
                return json_encode(array('status' => 'Success', 'message' => trans('general.success'), 't_type' => 2, 'meta' => $result));
                break;
            case 5:

                $p_log = ProjectLog::create(array('project_id' => $request->project_id, 'value' => $request->name, 'user_id' => auth()->user()->id));

                $log_text = '<tr><td>*</td><td>' . dateTimeFormat($p_log->created_at) . '</td><td>' . auth()->user()->first_name . '</td><td>' . $p_log->value . '</td></tr>';

                return json_encode(array('status' => 'Success', 'message' => trans('general.success'), 't_type' => 5, 'meta' => $log_text));
                break;

            case 6:

                $note = Note::create(array('title' => $input['title'], 'content' => $input['content'], 'user_id' => auth()->user()->id, 'section' => 1, 'ins' => auth()->user()->ins));
                $p_group = array('project_id' => $request->project_id, 'related' => 6, 'rid' => $note->id);
                ProjectRelations::create($p_group);
                ProjectLog::create(array('project_id' => $request->project_id, 'value' => '[' . trans('projects.milestone') . '] ' . $request->title, 'user_id' => auth()->user()->id));
                $log_text = '<tr><td>*</td><td>' . $note->title . '</td><td>' . dateTimeFormat($note->created_at) . '</td><td>' . auth()->user()->first_name . '</td><td><a href="' . route('biller.notes.show', [$note->id]) . '" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="View"><i class="fa fa-eye"></i></a><a href="' . route('biller.notes.edit', [$note->id]) . '" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fa fa-pencil "></i> </a> <a class="btn btn-danger round" table-method="delete" data-trans-button-cancel="Cancel" data-trans-button-confirm="Delete" data-trans-title="Are you sure you want to do this?" data-toggle="tooltip" data-placement="top" title="Delete" style="cursor:pointer;" onclick="$(this).find(&quot;form&quot;).submit();"><i class="fa fa-trash"></i> <form action="' . route('biller.notes.show', [$note->id]) . '" method="POST" name="delete_table_item" style="display:none"></form></a></td></tr>';
                return json_encode(array('status' => 'Success', 'message' => trans('general.success'), 't_type' => 6, 'meta' => $log_text));

                break;
        }
    }

    public function delete_meta(ManageProjectRequest $request)
    {
        $input = $request->except(['_token', 'ins']);
        switch ($input['obj_type']) {
            case 2:
                $milestone = ProjectMileStone::find($input['object_id']);
                ProjectLog::create(array('project_id' => $milestone->project_id, 'value' => '[' . trans('projects.milestone') . '] ' . '[' . trans('general.delete') . '] ' . $milestone->name, 'user_id' => auth()->user()->id));
                $milestone->delete();
                return json_encode(array('status' => 'Success', 'message' => trans('general.delete'), 't_type' => 1, 'meta' => $input['object_id']));
                break;
        }
    }

    public function log_history(ManageProjectRequest $request)
    {
        $input = $request->except(['_token', 'ins']);

        $project_select = Project::where('id', '=', $input['project_id'])->with('history')->first();
        $h = $project_select->history;
        return DataTables::of($h)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('created_at', function ($project) {
                return dateTimeFormat($project->created_at);
            })
            ->addColumn('user', function ($project) {
                return user_data($project->user_id)['first_name'];
            })
            ->make(true);
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

        $projects = Project::orwhereHas('quote', function ($q) use($k) {
            $q->where('tid', $k);
        })->orWhereHas('branch', function ($q) use ($k) {
            $q->where('name', 'LIKE', '%'.$k.'%');
        })->orWhereHas('customer_project', function ($q) use ($k) {
            $q->where('company', 'LIKE', '%'.$k.'%');
        })->orwhere('name', 'LIKE', '%'.$k.'%')->orWhere('tid', $k)         
        ->limit(6)->get();
        
        // response format
        $output = array();
        foreach ($projects as $project) {
            $customer = $project->customer_project->company;
            $branch = $project->branch->name;
            $project_tid = gen4tid('Prj-', $project->tid);

            $quote_tids = array();
            foreach ($project->quotes as $quote) {
                if ($quote->bank_id) $quote_tids[] = gen4tid('PI-', $quote->tid);
                else $quote_tids[] = gen4tid('QT-', $quote->tid);
            }
            $quote_tids = '[' . implode(', ', $quote_tids) . ']';

            $name = implode(' - ', [$quote_tids, $customer, $branch, $project_tid, $project->name]);
            $output[] = [
                'id' => $project->id, 
                'name' => $name,
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

    public function update_status(ManageProjectRequest $request)
    {
        switch ($request->r_type) {
            case 1:
                $project = Project::find($request->project_id);
                $project->progress = $request->progress;
                if ($request->progress == 100) {
                    $status_code = ConfigMeta::where('feature_id', '=', 16)->first();
                    $project->status = $status_code->feature_value;
                }
                $project->save();
                return json_encode(array('status' => $project->progress));
                break;
            case 2:
                $project = Project::find($request->project_id);
                $project->status = $request->sid;
                $project->save();
                $task_back = task_status($project->status);
                $status = '<span class="badge" style="background-color:' . $task_back['color'] . '">' . $task_back['name'] . '</span> ';
                return json_encode(array('status' => $status));

                break;
        }
    }

    public function project_load(Request $request)
    {
        $q = $request->get('id');
        if ($q == 1) {
            $result = Equipment::all()->where('rel_id', '=', $q);
            return json_encode($result);
        }

        $result = "";
        return json_encode($result);
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
