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
use App\Models\hrm\Hrm;
use App\Models\items\PurchaseItem;
use App\Models\misc\Misc;
use App\Models\project\Budget;
use App\Models\project\Project;
use App\Models\project\ProjectQuote;
use App\Models\quote\Quote;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
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

        $mics = Misc::all();
        $statuses = Misc::where('section', 2)->get();
        $tags = Misc::where('section', 1)->get();

        $employees = Hrm::all();
        $project = new Project;

        // return new ViewResponse('focus.projects.index', compact('accounts', 'last_tid'));

        return new ViewResponse('focus.projects.index-main', compact('accounts', 'last_tid', 'project', 'mics', 'employees', 'statuses', 'tags'));
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
            return errorHandler('Error Creating Project', $th); 
        }

        return response()->json(['status' => 'Success', 'message' => trans('alerts.backend.projects.created'), 'data' => $project, 'meta' => $project->actions]);
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
        $last_tid = Project::where('ins', auth()->user()->ins)->max('tid');

        // temp properties
        $project->customer = $project->customer_project;
        $project->creator = auth()->user();

        $mics = Misc::all();
        $employees = User::all();

        $params = ['mics', 'employees'];

        // return new ViewResponse('focus.projects.view', compact('project', 'accounts', 'last_tid', ...$params));

        return new ViewResponse('focus.projects.view-main', compact('project', 'accounts', 'last_tid', ...$params));
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
        $data_items = $request->only('numbering', 'row_index', 'a_type', 'product_id', 'product_name',            
            'product_qty', 'unit', 'new_qty',  'price'
        );
        $data_skillset = $request->only('skillitem_id', 'skill', 'charge', 'hours', 'no_technician');

        $data_items = modify_array($data_items);
        $data_skillset = modify_array($data_skillset);

        try {
            $this->repository->create_budget(compact('data', 'data_items', 'data_skillset'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Project Budget', $th);
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
            'item_id', 'numbering',  'row_index',  'a_type', 'product_id', 'product_name',            
            'product_qty', 'unit', 'new_qty',  'price'
        );
        $data_skillset = $request->only('skillitem_id', 'skill', 'charge', 'hours', 'no_technician');

        $data_items = modify_array($data_items);
        $data_skillset = modify_array($data_skillset);

        try {
            $this->repository->update_budget($budget, compact('data', 'data_items', 'data_skillset'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Project Budget', $th);
        }

        return new RedirectResponse(route('biller.projects.index'), ['flash_success' => 'Project Budget updated successfully']);
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
        $projects = Project::where('name', 'LIKE', '%' . $q . '%')->limit(6)->get();

        return response()->json($projects);
    }

    /**
     * Project Quotes Select
     */
    public function quotes_select()
    {   
        $quotes = Quote::where(['customer_id' => request('customer_id'), 'status' => 'approved'])
            ->whereDoesntHave('project')
            ->get()->map(fn($v) => [
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
        // if (!project_access($input['project_id'])) exit;
        $input = $request->except(['_token', 'ins']);
        $response = ['status' => 'Error', 'message' => 'Something Went Wrong'];

        DB::beginTransaction();

        switch ($input['obj_type']) {
            case 2: // milestone
                $data = Arr::only($input, ['project_id', 'name', 'description', 'color', 'duedate', 'time_to']);
                $data['due_date'] = date_for_database("{$data['duedate']} {$data['time_to']}:00");
                $data['note'] = $data['description'];
                unset($data['duedate'], $data['time_to'], $data['description']);
                $milestone = ProjectMileStone::create($data);

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
                                <a href="#" class=" delete-object" data-object-type="2" data-object-id="'. $milestone->id .'">
                                    <i class="danger fa fa-trash"></i>
                                </a>
                            </div>
                            <small class="text-muted">
                                <i class="fa fa-user"></i><strong>'. $milestone->creator->first_name .' '. $milestone->creator->last_name . '</strong>
                                <i class="fa fa-clock-o"></i> '. trans('general.created') . '  ' . dateTimeFormat($milestone->created_at) . '
                            </small>
                        </div>
                    </li>
                ';

                $data = [
                    'project_id' => $milestone->project_id, 
                    'value' => '[' . trans('projects.milestone') . '] ' . '[' . trans('general.new') . '] ' . $input['name'],
                ];
                ProjectLog::create($data);

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
                $data = ['title' => $input['title'], 'content' => $input['content'], 'section' => 1];
                $note = Note::create($data);

                $data = ['project_id' => $request->project_id, 'related' => 6, 'rid' => $note->id];
                ProjectRelations::create($data);

                $data = ['project_id' => $request->project_id, 'value' => '[' . trans('projects.milestone') . '] ' . $request->title];
                ProjectLog::create($data);

                $log_text = '<tr><td>*</td><td>'. $note->title .'</td><td>'. dateTimeFormat($note->created_at) .'</td><td>' 
                    . auth()->user()->first_name . '</td><td><a href="'. route('biller.notes.show', [$note->id]) .'" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="View"><i class="fa fa-eye"></i></a>
                        <a href="'. route('biller.notes.edit', [$note->id]) .'" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fa fa-pencil "></i> </a> 
                        <a class="btn btn-danger round" table-method="delete" data-trans-button-cancel="Cancel" data-trans-button-confirm="Delete" data-trans-title="Are you sure you want to do this?" data-toggle="tooltip" data-placement="top" title="Delete" style="cursor:pointer;" onclick="$(this).find(&quot;form&quot;).submit();">
                        <i class="fa fa-trash"></i> <form action="' . route('biller.notes.show', [$note->id]) . '" method="POST" name="delete_table_item" style="display:none"></form></a></td></tr>';
                
                $response = array_replace($response, ['status' => 'Success', 't_type' => 6, 'meta' => $log_text]);
                break;
            
            case 7: // project quote
                $project = Project::find($input['project_id']);
                if (!$project->main_quote_id) $project->update(['main_quote_id' => current($input['quote_ids'])]);

                foreach($input['quote_ids'] as $val) {
                    $item = ProjectQuote::firstOrCreate(['project_id' => $project->id, 'quote_id' => $val]);
                    Quote::find($val)->update(['project_quote_id' => $item->id]); 
                }

                $response = array_replace($response, ['status' => 'Success', 't_type' => 7, 'meta' => '', 'refresh' => 1]);
                break;
        }

        if ($response['status'] == 'Success') {
            DB::commit();
            $response['message'] = 'Resource Updated Successfully';
            return response()->json($response);
        } 

        return response()->json($response);
    }

    /**
     * Remove Project Quote
     */
    public function detach_quote(Request $request)
    {
        $input = $request->except('_token');

        DB::beginTransaction();
        
        $project = Project::find($input['project_id']);
        // expense
        $purchase_items = $project->purchase_items;
        $expense_amount = $purchase_items->sum('amount');
        // issuance
        $issuance_amount = 0;
        foreach ($project->quotes as $quote) {
            $issuance_amount += $quote->projectstock->sum('total');
        }
        $expense_total = $expense_amount + $issuance_amount;
        $project_budget = $project->quotes->sum('total');

        $detach = false;
        $quote = Quote::find($input['quote_id']);
        if ($expense_total < $project_budget - $quote->total) {
            if ($quote->invoiced == 'Yes') {
                $type = $quote->bank_id? 'Proforma Invoice' : 'Quote';
                return response()->json(['status' => 'Error', 'message' => "Not allowed! {$type} has been invoiced."], 500);
            } else {
                ProjectQuote::where(['project_id' => $input['project_id'], 'quote_id' => $input['quote_id']])->delete();
                if ($project->main_quote_id == $input['quote_id']) {
                    $other_project_quote = ProjectQuote::where(['project_id' => $input['project_id']])->first();
                    if ($other_project_quote) $project->update(['main_quote_id' => $other_project_quote->quote_id]);
                    else $project->update(['main_quote_id' => null]);
                }
                $detach = true;
            }
        } else return response()->json(['status' => 'Error', 'message' => "Project has expense."], 500);

        if ($detach) {
            DB::commit();
            return response()->json(['status' => 'Success', 'message' => 'Resource Detached Successfully', 't_type' => 7]);
        }
    }

    /**
     * Remove Project Budget
     */
    public function detach_budget(Request $request)
    {
        $input = $request->except('_token');

        DB::beginTransaction();
        
        $project = Project::find($input['project_id']);
        // expense
        $purchase_items = $project->purchase_items;
        $expense_amount = $purchase_items->sum('amount');
        // issuance
        $issuance_amount = 0;
        foreach ($project->quotes as $quote) {
            $issuance_amount += $quote->projectstock->sum('total');
        }
        $expense_total = $expense_amount + $issuance_amount;
        $project_budget = $project->quotes->sum('total');

        $detach = false;
        $quote = Quote::find($input['quote_id']);
        if ($expense_total < $project_budget - $quote->total) {
            if ($quote->invoiced == 'Yes') {
                $type = $quote->bank_id? 'Proforma Invoice' : 'Quote';
                return response()->json(['status' => 'Error', 'message' => "Not allowed! {$type} has been invoiced."], 500);
            } else {
                ProjectQuote::where(['project_id' => $input['project_id'], 'quote_id' => $input['quote_id']])->delete();
                if ($project->main_quote_id == $input['quote_id']) {
                    $other_project_quote = ProjectQuote::where(['project_id' => $input['project_id']])->first();
                    if ($other_project_quote) $project->update(['main_quote_id' => $other_project_quote->quote_id]);
                    else $project->update(['main_quote_id' => null]);
                }
                $detach = true;
            }
        } else return response()->json(['status' => 'Error', 'message' => "Project has expense."], 500);

        if ($detach) {
            DB::commit();
            return response()->json(['status' => 'Success', 'message' => 'Resource Detached Successfully', 't_type' => 7]);
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
}
