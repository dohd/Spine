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
use App\Models\hrm\Hrm;
use App\Models\misc\Misc;
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
        $mics = Misc::all();
        $employees = Hrm::all();
        $accounts = Account::where('account_type', 'Income')->get();
        $ref = Project::orderBy('project_number', 'desc')->first('project_number');
        $tid = isset($ref) ? $ref->project_number+1 : 1;

        return new ViewResponse('focus.projects.index', compact('mics', 'employees', 'accounts', 'tid'));
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
        $project = $request->only([
            'customer_id', 'branch_id', 'name', 'project_number', 'status', 'priority', 'short_desc', 
            'note', 'start_date', 'end_date', 'phase', 'worth', 'project_share', 'sales_account'
        ]);
        $project_quotes = $request->only(['main_quote', 'other_quote']);
        // $rest = $request->only(['tags', 'time_from',  'time_to', 'color',  'employees']);

        $result = $this->repository->create(compact('project', 'project_quotes'));

        return json_encode(['status' => 'Success', 'message' => trans('alerts.backend.projects.created'), 'refresh' => 1]);
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
        if (true) return new EditResponse($project);        
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
        $data = $request->only([
            'customer_id', 'branch_id', 'name', 'project_number', 'status', 'priority', 'short_desc', 
            'note', 'start_date', 'end_date', 'phase', 'worth', 'project_share', 'sales_account'
        ]);
        $quotes = $request->only(['main_quote', 'other_quote']);

        $data['id'] = $project->id;
        
        // $valid_project_creator = isset($project->creator) && $project->creator->id == auth()->user()->id;
        if (true) $this->repository->update($project, compact('data', 'quotes'));
        
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
        // $auth_view = project_view($project->id);
        if (true) {
            $employees = Hrm::all();
            $mics = Misc::all();
            $features = ConfigMeta::where('feature_id', 9)->first();

            $user = auth()->user();
            $project_select = Project::whereHas('users', function ($q) use ($user) {
                return $q->where('rid', $user->id);
            })->get();

            return new ViewResponse('focus.projects.view', compact('project', 'employees', 'mics', 'project_select', 'features'));
        }
    }

    // Project Quote Budget
    public function quote_budget(Quote $quote)
    {
        $products = $quote->products()->orderBy('row_index')->get();

        return view('focus.projects.quote_budget')->with(compact('quote', 'products'));
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

    public function load(ManageProjectRequest $request)
    {
        $project = Project::find($request->project_id);
        $project->start_date = dateTimeFormat($project['start_date']);
        $project->view = route('biller.projects.show', [$project->id]);

        $task_back = task_status($project->status);
        $project->status = '<span class="badge" style="background-color:' . $task_back['color'] . '">' . $task_back['name'] . '</span> ';

        $s = '';
        foreach (status_list() as $row) {
            if ($row['id'] == $task_back->id) $s .= '<option value="' . $row['id'] . '" selected>' . $row['name'] . '</option>';
            else $s .= '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
        }
        $project['status_list'] = $s;

        return response()->json($project);
    }

    public function project_search(Request $request, $bill_type)
    {

        if (!access()->allow('product_search')) return false;

        $q = $request->post('keyword');
        $w = $request->post('wid');
        $s = $request->post('serial_mode');
        if ($bill_type == 'label') $q = @$q['term'];
        $wq = compact('q', 'w');


        $project = Project::where('name', 'LIKE', '%' . $q . '%')
            ->orWhereHas('customer', function ($query) use ($wq) {
                $query->where('company', 'LIKE', '%' . $wq['q'] . '%');
                return $query;
            })->orWhereHas('branch', function ($query) use ($wq) {
                $query->where('name', 'LIKE', '%' . $wq['q'] . '%');
                return $query;
            })->limit(6)->get();
        $output = array();

        foreach ($project as $row) {

            if ($row->id > 0) {
                $output[] = array('name' => $row->customer_project->company . ' ' . $row->branch->name . '  - ' . $row->name . ' - ' . $row->project_number, 'id' => $row['id'], 'client_id' => $row->customer_project->id, 'branch_id' => $row->branch->id);
            }
        }

        if (count($output) > 0)

            return view('focus.products.partials.search')->withDetails($output);
    }

    public function search(Request $request)
    {
        $q = $request->post('keyword');

        $projects = Project::where('project_number', 'LIKE', '%' . $q . '%')
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
        print_log('+++ Update status called +++',$request->all());
        //Update the model using repository update method
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

    public function project_load_select(Request $request)
    {
        $q = $request->get('id');
        $result = Project::all()->where('customer_id', '=', $q)->where('status', '=', 1);
        
        return json_encode($result);
    }
}
