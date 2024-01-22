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
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\project\EditResponse;
use App\Repositories\Focus\project\ProjectRepository;
use App\Http\Requests\Focus\project\ManageProjectRequest;
use App\Http\Requests\Focus\project\CreateProjectRequest;
use App\Http\Requests\Focus\project\UpdateProjectRequest;
use App\Models\Access\User\User;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\misc\Misc;
use App\Models\project\Budget;
use App\Models\project\Project;
use App\Models\project\ProjectQuote;
use App\Models\project\ProjectRelations;
use App\Models\quote\Quote;
use App\Models\supplier\Supplier;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Log;
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
        $customer_id = auth()->user()->customer_id;
        $customers = Customer::when($customer_id, fn($q) => $q->where('id', $customer_id))
            ->whereHas('quotes')->get(['id', 'company']);
        $accounts = Account::where('account_type', 'Income')->get(['id', 'holder', 'number']);
        $last_tid = Project::where('ins', auth()->user()->ins)->max('tid');

        $mics = Misc::all();
        $statuses = Misc::where('section', 2)->get();
        $tags = Misc::where('section', 1)->get();

        $employees = Hrm::all();
        $project = new Project;

        return new ViewResponse('focus.projects.index', compact('customers', 'accounts', 'last_tid', 'project', 'mics', 'employees', 'statuses', 'tags'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProjectRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(CreateProjectRequest $request)
    {
        try {
            $this->repository->create($request->except('_token'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Creating Project', $th); 
        }

        return new RedirectResponse(route('biller.projects.index'), ['flash_success' => 'Project Successfully Created']);
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
        try {
            $this->repository->update($project, $request->except('_token'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Updating Project', $th);
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
            return errorHandler('Error Deleting Project', $th);
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
        $exp_accounts = Account::where('account_type', 'Expense')->get(['id', 'holder', 'number']);
        $suppliers = Supplier::get(['id', 'name']);
        $last_tid = Project::where('ins', auth()->user()->ins)->max('tid');

        // temp properties
        $project->customer = $project->customer_project;
        $project->creator = auth()->user();

        $mics = Misc::all();
        $employees = User::all();

        return new ViewResponse('focus.projects.view', compact('project', 'accounts', 'exp_accounts', 'suppliers', 'last_tid', 'mics', 'employees'));
    }

    /**
     * Update issuance tools and requisition
     */
    public function update_budget_tool(Request $request, Budget $budget)
    {
        try {
            $budget->update(['tool' => $request->tool, 'tool_reqxn' => $request->tool_reqxn]);
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Project Budget Tool', $th);
        }

        return redirect()->back();
    }

    /**
     * Project autocomplete search
     */
    public function project_search(Request $request)
    {
        if (!access()->allow('product_search')) return false;

        $k = $request->post('keyword');

        $projects = Project::whereHas('quote', function ($q) use ($k) {
            $q->where('tid', $k);
        })->orWhereHas('branch', function ($q) use ($k) {
            $q->where('name', 'LIKE', '%' . $k . '%');
        })->orWhereHas('customer_project', function ($q) use ($k) {
            $q->where('company', 'LIKE', '%' . $k . '%');
        })->orwhere('name', 'LIKE', '%' . $k . '%')
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

            $customer = @$project->customer_project->company;
            $branch = @$project->branch->name;
            $project_tid = gen4tid('Prj-', $project->tid);
            $output[] = [
                'id' => $project->id,
                'name' => implode(' - ', [$quote_tids, $customer, $branch, $project_tid, $project->name]),
                'client_id' => @$project->customer_project->id,
                'branch_id' => @$project->branch->id
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
        $projects = Project::where('name', 'LIKE', '%' . $q . '%')->limit(6)->get();

        return response()->json($projects);
    }

    /**
     * Project Quotes select
     */
    public function quotes_select()
    {   
        $quotes = Quote::where(['customer_id' => request('customer_id'), 'status' => 'approved'])
            ->doesntHave('project')
            ->doesntHave('invoice')
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'name' => gen4tid($v->bank_id? 'PI-' : 'QT-', $v->tid) . ' - ' . $v->notes,
            ]);

        return response()->json($quotes);
    }


    /**
     * Update Project Status
     */
    public function update_status(ManageProjectRequest $request)
    {
        $response = [];
        switch ($request->r_type) {
            case 1:
                $project = Project::find($request->project_id);
                $project->progress = $request->progress;
                if ($request->progress == 100) {
                    $status_code = ConfigMeta::where('feature_id', '=', 16)->first();
                    $project->status = $status_code->feature_value;
                }
                $project->save();
                $response = ['status' => $project->progress];
                break;
            case 2:
                $project = Project::find($request->project_id);
                $project->status = $request->sid;
                $project->save();
                $task_back = task_status($project->status);
                $status = '<span class="badge" style="background-color:' . $task_back['color'] . '">' . $task_back['name'] . '</span> ';
                $response = compact('status');
                break;
        }

        return response()->json($response);
    }

    /**
     * Project Meta Data
     */
    public function store_meta(ManageProjectRequest $request)
    {
        $input = $request->except(['_token', 'ins']);
        $response = ['status' => 'Error', 'message' => 'Something Went Wrong. Try again later'];

        DB::beginTransaction();

        try {
            switch ($input['obj_type']) {
                case 2: // milestone
                    $data = Arr::only($input, ['project_id','amount', 'name', 'description', 'color', 'duedate', 'time_to']);
                    $data = array_replace($data, [
                        'due_date' => date_for_database("{$data['duedate']} {$data['time_to']}:00"),
                        'note' => $data['description'],
                        'amount' => numberClean($data['amount']),
                    ]);
                    unset($data['duedate'], $data['time_to'], $data['description']);
                    $milestone = ProjectMileStone::create($data);
                    ProjectRelations::create(['project_id' => $milestone->project_id, 'milestone_id' => $milestone->id]);

                    // log
                    $data = ['project_id' => $milestone->project_id, 'value' => '['. trans('projects.milestone') .']' .'['. trans('general.new') .'] '. $input['name'], 'user_id' => auth()->user()->id];
                    ProjectLog::create($data);                    
    
                    $result = '
                        <li id="m_'. $milestone->id .'">
                            <div class="timeline-badge" style="background-color:'. $milestone->color .';">*</div>
                            <div class="timeline-panel">
                                <div class="timeline-heading">
                                    <h4 class="timeline-title">'. $milestone->name .'</h4>
                                    <p><small class="text-muted">['. trans('general.due_date') .' '. dateTimeFormat($milestone->due_date) .']</small></p>
                                </div>
                                <div class="timeline-body mb-1">
                                    <p> '. $milestone->note .'</p>
                                    <p> Milestone Amount: '. numberFormat($milestone->amount) .'</p>
                                </div>
                                <small class="text-muted">
                                    <i class="fa fa-user"></i><strong>'. @$milestone->creator->fullname . '</strong>
                                    <i class="fa fa-clock-o"></i> '. trans('general.created') . '  ' . dateTimeFormat($milestone->created_at) . '
                                </small>
                                <div class="btn-group">
                                    <button class="btn btn-link milestone-edit" obj-type="2" data-id="'. $milestone->id .'" data-url="'. route('biller.projects.edit_meta') .'">
                                        <i class="ft ft-edit" style="font-size: 1.2em"></i>
                                    </button>
                                    <button class="btn btn-link milestone-del" obj-type="2" data-id="'. $milestone->id .'" data-url="'. route('biller.projects.delete_meta') .'">
                                        <i class="fa fa-trash fa-lg danger"></i>
                                    </button>
                                </div>                             
                            </div>
                        </li>
                    ';
                    $response = array_replace($response, ['status' => 'Success', 't_type' => 2, 'meta' => $result]);
                    break;
                case 5: // project activity log 
                    $data = ['project_id' => $request->project_id, 'value' => $request->name];
                    $project_log = ProjectLog::create($data);
    
                    $log_text = '<tr><td>*</td><td>'. dateTimeFormat($project_log->created_at) .'</td><td>' 
                        .auth()->user()->first_name .'</td><td>'. $project_log->value .'</td></tr>';
    
                    $response = array_replace($response, ['status' => 'Success', 't_type' => 5, 'meta' => $log_text]);
                    break;
                case 6: // project note
                    $data = Arr::only($input, ['title', 'content']);
                    $data['section'] = 1;
                    $note = Note::create($data);

                    ProjectLog::create(['project_id' => $input['project_id'], 'value' => '[Project Note][New]' . $note->title]);

                    ProjectRelations::create(['project_id' => $input['project_id'], 'note_id' => $note->id]);
    
                    $log_text = '<tr>
                        <td>*</td>
                        <td>'. $note->title .'</td>
                        <td>'. $note->content .'</td>
                        <td>'. auth()->user()->first_name . '</td>
                        <td>'. dateTimeFormat($note->created_at) .'</td>
                        <td>
                            <a href="'. route('biller.notes.edit', [$note->id]) .'" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fa fa-pencil "></i> </a> 
                            <a class="btn btn-danger round" table-method="delete" data-trans-button-cancel="Cancel" data-trans-button-confirm="Delete" data-trans-title="Are you sure you want to do this?" data-toggle="tooltip" data-placement="top" title="Delete" style="cursor:pointer;" onclick="$(this).find(&quot;form&quot;).submit();">
                            <i class="fa fa-trash"></i> <form action="' . route('biller.notes.show', [$note->id]) . '" method="POST" name="delete_table_item" style="display:none"></form></a>
                        </td>
                    </tr>';
                    
                    $response = array_replace($response, ['status' => 'Success', 't_type' => 6, 'meta' => $log_text]);
                    break;
                case 7: // attach project quote
                    $project = Project::find($input['project_id']);
                    if (!$project->main_quote_id) 
                        $project->update(['main_quote_id' => @$input['quote_ids'][0]]);
    
                    foreach($input['quote_ids'] as $val) {
                        $item = ProjectQuote::firstOrCreate(
                            ['project_id' => $project->id, 'quote_id' => $val],
                            ['project_id' => $project->id, 'quote_id' => $val]
                        );
                        $item->quote->update(['project_quote_id' => $item->id]);
                    }
    
                    $response = array_replace($response, ['status' => 'Success', 't_type' => 7, 'meta' => '', 'refresh' => 1]);
                    break;
            }
        } catch (\Throwable $th) {
            \Log::error($th->getMessage() . ' ' . $th->getFile() . ' : ' . $th->getLine());
        }

        if ($response['status'] == 'Success') {
            DB::commit();
            $response['message'] = 'Resource Updated Successfully';
            return response()->json($response);
        } 

        return response()->json($response);
    }

    /**
     * Edit Meta Data
     */
    public function edit_meta(ManageProjectRequest $request)
    {
        $input = $request->except(['_token', 'ins']);

        switch ($input['obj_type']) {
            case 2 :
                $milestone = ProjectMileStone::find($input['object_id']);
                $project = $milestone->project;
                return view('focus.projects.modal.milestone_new', compact('milestone', 'project'));
        }
        
        return response()->json();
    }    

    /**
     * Delete meta
     */
    public function delete_meta(ManageProjectRequest $request)
    {
        $input = $request->except(['_token', 'ins']);

        DB::beginTransaction();

        try {
            switch ($input['obj_type']) {
                case 2: //milestone
                    $milestone = ProjectMileStone::find($input['object_id']);
                    
                    $data = ['project_id' => $milestone->project_id, 'value' => '['. trans('projects.milestone') .']' .'['. trans('general.deleted') .'] '. $milestone['name'], 'user_id' => auth()->user()->id];
                    ProjectLog::create($data); 

                    $milestone->delete();
                    $data = ['status' => 'Success', 'message' => trans('general.delete'), 't_type' => 1, 'meta' => $input['object_id']];
                    break;
            }
            DB::commit();
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            $data = ['status' => 'Error', 'message' => 'Internal server error!'];
        }

        return response()->json($data);
    }

    /**
     * Update Meta
     */
    public function update_meta(ManageProjectRequest $request)
    {
        $input = $request->except(['_token', 'ins']); 

        DB::beginTransaction();

        try {
            switch ($input['obj_type']) {
                case 2 :
                    $data = Arr::only($input, ['project_id','amount', 'name', 'description', 'color', 'duedate', 'time_to']);
                    $data = array_replace($data, [
                        'due_date' => date_for_database("{$data['duedate']} {$data['time_to']}:00"),
                        'note' => $data['description'],
                        'amount' => numberClean($data['amount']),
                    ]);
                    unset($data['duedate'], $data['time_to'], $data['description']);
                    $milestone = ProjectMileStone::find($input['object_id']);
                    $milestone->update($data);

                    // log
                    $data = ['project_id' => $milestone->project_id, 'value' => '['. trans('projects.milestone') .']' .'['. trans('general.update') .'] '. $input['name'], 'user_id' => auth()->user()->id];
                    ProjectLog::create($data);  

                    $data = ['status' => 'Success', 'message' => trans('general.update'), 't_type' => 1, 'meta' => $input['object_id'], 'refresh' => 1];
                    break;
            }
            DB::commit();
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            $data = ['status' => 'Error', 'message' => 'Internal server error!'];
        }

        return response()->json($data);
    }

    /**
     * Remove Project Quote
     */
    public function detach_quote(Request $request)
    {
        $input = $request->except('_token');
        $error_data = [];

        DB::beginTransaction();
    
        try {
            $project = Project::find($input['project_id']);
            $quote = Quote::find($input['quote_id']);

            $expense_amount = $project->purchase_items->sum('amount');
            $issuance_amount = 0;
            foreach ($project->quotes as $quote) {
                $issuance_amount += $quote->projectstock->sum('total');
            }
            $expense_total = $expense_amount + $issuance_amount;
            $project_budget = $project->quotes->sum('total');

            if ($expense_total >= $project_budget - $quote->total) {
                $error_data = ['status' => 'Error', 'message' => "Not allowed! Project has been expensed."];
                trigger_error($error_data['message']);
            } elseif ($quote->invoice) {
                $doc = $quote->bank_id? 'Proforma Invoice' : 'Quote';
                $inv_tid = @$quote->invoice->tid ?: '';
                $error_data = ['status' => 'Error', 'message' => "Not allowed! {$doc} is attached to Invoice no. {$inv_tid}"];
                trigger_error($error_data['message']);
            }

            ProjectQuote::where(['project_id' => $input['project_id'], 'quote_id' => $input['quote_id']])->delete();
            if ($project->main_quote_id == $input['quote_id']) {
                $other_project_quote = ProjectQuote::where(['project_id' => $input['project_id']])->first();
                if ($other_project_quote) $project->update(['main_quote_id' => $other_project_quote->quote_id]);
                else $project->update(['main_quote_id' => null]);
            }

            DB::commit();
            return response()->json(['status' => 'Success', 'message' => 'Resource Detached Successfully', 't_type' => 7]);
        } catch (\Throwable $th) {
            \Log::error($th->getMessage());
            if (!$error_data) $error_data = ['status' => 'Error', 'message' => 'Something went wrong!'];
            return response()->json($error_data, 500);
        }
    }

    /**
     * DataTable Project Activity Log
     */
    public function log_history(ManageProjectRequest $request)
    {
        $input = $request->except(['_token', 'ins']);

        $core = collect();
        $project = Project::find($input['project_id']);
        if ($project) $core = $project->history;

        return DataTables::of($core)
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
     * Milestone budget limit
     */
    public function budget_limit(Project $project)
    {
        $project_budget = 0;
        foreach ($project->quotes as $quote) {
            if ($quote->budget) $project_budget += $quote->budget->budget_total;
        }
        if ($project_budget == 0 && $project->quotes->count()) 
            $project_budget = $project->quotes->sum('total');
        elseif ($project_budget == 0) $project_budget = $project->worth;

        $milestone_budget = $project_budget;
        foreach ($project->milestones as $milestone) {
            $milestone_budget -= $milestone->amount;
        }

        return response()->json(['status' => 'Success', 'data' => compact('project_budget', 'milestone_budget')]);
    }
}
