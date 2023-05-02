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
use App\Repositories\Focus\budget\BudgetRepository;
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
use App\Models\project\BudgetSkillset;
use App\Models\project\Project;
use App\Models\projectstock\Projectstock;
use App\Models\project\ProjectQuote;
use App\Models\quote\Quote;
use App\Models\items\QuoteItem;
use DB;
use Illuminate\Support\Arr;
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
        // extract input fields from request
        // $data = $request->only([
        //     'customer_id', 'branch_id', 'name', 'tid', 'status', 'priority', 'short_desc',
        //     'note', 'start_date', 'end_date', 'phase', 'worth', 'project_share', 'sales_account',
        // ]);
        // $data_items = array_merge([$request->main_quote], ...array_values($request->only('other_quote')));

        // $data['ins'] = auth()->user()->ins;

        // $this->repository->create(compact('data', 'data_items'));
        // return new RedirectResponse(route('biller.projects.index'), ['flash_success' => trans('alerts.backend.projects.created')]);

        $project = $this->repository->create($request->except('_token'));

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
        // extract input fields from request
        // $data = $request->except(['_token', 'main_quote', 'other_quote']);
        // $data_items = array_merge([$request->main_quote], ...array_values($request->only('other_quote')));

        // $this->repository->update($project, compact('data', 'data_items'));

        $this->repository->update($project, $request->except('_token'));

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
        $project = $quote->project()->first();
        $milestone_exists = $project->milestones()->first();
        if($milestone_exists) return redirect(route('biller.projects.show', $project->id))->with('flash_error', 'Milestone Already Created !! Cannot Budget this Quote');
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
        $project_id = ProjectQuote::where('quote_id', $data['quote_id'])->first()->project_id;

        $this->repository->create_budget(compact('data', 'data_items', 'data_skillset'));

        return new RedirectResponse(route('biller.projects.show',[$project_id]), ['flash_success' => 'Budget created successfully']);
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
        $project_id = ProjectQuote::where('quote_id', $data['quote_id'])->first()->project_id;

        $this->repository->update_budget($budget, compact('data', 'data_items', 'data_skillset'));

        return new RedirectResponse(route('biller.projects.show', [$project_id]), ['flash_success' => 'Project Budget updated successfully']);
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
    public function invoices(Request $request)
    {
        $quote_ids = explode(',', $request->quote_ids);
        $quotes = [];
        foreach ($quote_ids as $quote) {
            $quotes = Quote::where('id', $quote)->get();
        }
        $invoices = [];
        if($quotes){
           $pro = [];
            foreach($quotes as $invoice_items){
                $invoices = $invoice_items->invoice_product()->get();
            }
            
        }
        $invoice = [];
        if($invoices){
            foreach ($invoices as $inv) {
                $invoice = $inv->invoice()->get();
            }
        }

        return Datatables::of($invoice)
            ->addIndexColumn()
            ->addColumn('tid', function ($invoice) {
                $tid = gen4tid('INV-', $invoice->tid);
                return '<a class="font-weight-bold" href="' . route('biller.invoices.show', [$invoice->id]) . '">' . $tid . '</a>';
            })
            ->addColumn('customer', function ($invoice) {
                if($invoice->customer)
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
        $milestone_response = ['status' => 'Error', 'message' => 'Milestone Already Attached, Quote CANNOT be Attached'];

        DB::beginTransaction();

        switch ($input['obj_type']) {
            case 2: // milestone
                $data = Arr::only($input, ['project_id','extimated_milestone_amount', 'name', 'description', 'color', 'duedate', 'time_to']);
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
                                <p> '. $milestone->extimated_milestone_amount .'</p>
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
                $milestones = $project->milestones()->first();
                if($milestones) return response()->json($milestone_response);
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
    
    public function delete_meta(ManageProjectRequest $request)
    {
        $input = $request->except(['_token', 'ins']);
        switch ($input['obj_type']) {
            case 2 :
                $milestone = ProjectMileStone::find($input['object_id']);
                ProjectLog::create(array('project_id' => $milestone->project_id, 'value' => '[' . trans('projects.milestone') . '] ' . '[' . trans('general.delete') . '] ' . $milestone->name, 'user_id' => auth()->user()->id));
                $milestone->delete();
                return json_encode(array('status' => 'Success', 'message' => trans('general.delete'), 't_type' => 1, 'meta' => $input['object_id']));
                break;

        }

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
        
        $budget = Budget::find($request->budget_id);
        if($budget){
            $budget->items()->delete();
            $budget->skillsets()->delete();
            $budget->delete();
            DB::commit();
            return response()->json(['status'=> 'Detached Successfully!!']);
        
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

    public function view_budget(Request $request)
    {
        $budget = Budget::where('quote_id', $request->id)->first();
        if($budget){
            $quote = Quote::find($request->id);
            
            $customer = $quote->customer ? $quote->customer->company : '';
            $branch = $quote->branch? $quote->branch->name : '';
           // $budget = Budget::find($budget_id);
            $budget_items = $budget->items()->orderBy('row_index')->get();
            $skillset = $budget->skillsets()->get();
            return response()->json([
                'quote'=>$quote,
                 'budget'=>$budget,
                 'budget_items'=> $budget_items,
                 'skillset'=> $skillset,
                 'customer' => $customer,
                 'branch'=> $branch
                ]);
        }
        return response()->json($request);
    }

    public function get_extimated_milestone(Request $request)
    {
        $project = Project::find($request->project_id);
        $quote_total = $project->quotes()->where('status', 'approved')->sum('total');
        $budget = $project->quotes()->where('status', 'approved')->get();
        $total_budget = '';
        foreach ($budget as $budget_total) {
            $total_budget = $budget_total->budgets()->sum('budget_total');
        }
        $total_milestone = ProjectMileStone::where('project_id', $request->project_id)->get()->sum('extimated_milestone_amount');
        $milestone = -1;
        if ($total_budget > 0) {
            $milestone = $total_budget - $total_milestone;
            return response()->json($milestone);
        }else{
            if($quote_total > 0){
                $milestone = $quote_total - $total_milestone;
                return response()->json($milestone);
            }
        }
        return response()->json($milestone);
    }

    public function project_budget(Request $request){
        //$core = $budget->getForDataTable();
        $pro = $request->project_id;
        $project = Project::find($request->project_id);
        $quotes = $project->quotes()->get();
        $budgets = [];
        foreach($quotes as $quote){
            $budgets = $quote->budgets()->get();
        }
        return Datatables::of($budgets)
            ->addIndexColumn()
            ->addColumn('tid', function ($budget) {
                return $budget->quote ? 'QT-'.$budget->quote->tid : '';
            })
            ->addColumn('customer', function ($budget) {
                return $budget->quote->customer ? $budget->quote->customer->name : '';
            })
            ->addColumn('quote_total', function ($budget) {
                return amountFormat($budget->quote_total);
            })
            ->addColumn('budget_total', function ($budget) {
                return amountFormat($budget->budget_total);
            })
            ->addColumn('actions', function ($budget) use ($pro) {
                 $editUrl = "{{ route('biller.projects.create_project_budget', $budget->quote_id)}}";
                return '
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Action
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item edit" href="' . route('biller.projects.create_project_budget', [$budget->quote_id]) . '">Edit</a>
                        <a class="dropdown-item view" data-id="'.$budget->quote_id.'"  href="javascript:void(0);">View</a>
                        <a class="dropdown-item text-danger budget_delete" data-id="'.$budget->id.'" data-pro="'.$pro.'" href="#">Remove</a>
                    </div>
                </div> ';
        
            })->rawColumns(['tid', 'customer', 'actions', 'quote_total', 'budget_total'])
            ->make(true);

    }

    public function bill_stock_items(Request $request)
    {
        $project = Project::find($request->project_id);
        $bill_items = $project->purchase_items()->where('type', 'Stock')->get();

        return Datatables::of($bill_items)
            ->addIndexColumn()
            ->addColumn('bill_id', function ($bill_item) {
                return $bill_item->bill_id;
            })
            ->addColumn('type', function ($bill_item) {
                return $bill_item->type;
            })
            ->addColumn('description', function ($bill_item) {
                return $bill_item->description;
            })
            ->addColumn('uom', function ($bill_item) {
                return $bill_item->uom;
            })
            ->addColumn('qty', function ($bill_item) {
                return numberFormat($bill_item->qty);
            })
            ->addColumn('amount', function ($bill_item) {
                return amountFormat($bill_item->amount);
        
            })->rawColumns(['bill_id', 'type', 'description','uom', 'qty','amount'])
            ->make(true);
    }

    public function project_expense(Request $request)
    {
        $project = Project::find($request->project_id);
        $expenses = $project->purchase_items()->where('type', 'Expense')->get();

        return Datatables::of($expenses)
            ->addIndexColumn()
            ->addColumn('bill_id', function ($expense) {
                return $expense->bill_id;
            })
            ->addColumn('type', function ($expense) {
                return $expense->type;
            })
            ->addColumn('description', function ($expense) {
                return $expense->description;
            })
            ->addColumn('uom', function ($expense) {
                return $expense->uom;
            })
            ->addColumn('qty', function ($expense) {
                return numberFormat($expense->qty);
            })
            ->addColumn('amount', function ($expense) {
                return amountFormat($expense->amount);
        
            })->rawColumns(['bill_id', 'type', 'description','uom', 'qty','amount'])
            ->make(true);
    }
    public function issued_items(Request $request)
    {
        $quote_ids = explode(',', $request->quote_ids);
        $project_stocks = [];
        foreach ($quote_ids as $quote) {
            $project_stocks = Projectstock::where('quote_id', $quote)->get();
        }
        $project_stocks_items = [];
        if($project_stocks){
           $pro = [];
            foreach($project_stocks as $project_stock){
                $project_stocks_items = $project_stock->items()->get();
                $pro = $project_stock;
            }
        }
        

        return Datatables::of($project_stocks_items)
            ->addIndexColumn()
            ->addColumn('tid', function ($project_stock_item) use ($pro) {
                if($pro)return $pro->quote ? 'QT-'.$pro->quote->tid : '';
                return '';
            })
            ->addColumn('uom', function ($project_stock_item)  {
                return $project_stock_item->unit ? $project_stock_item->unit : '';
            })
            ->addColumn('description', function ($project_stock_item) {
                return $project_stock_item->productvariation ? $project_stock_item->productvariation->name : '';
            })
            ->addColumn('qty', function ($project_stock_item) {
                return $project_stock_item->qty ? numberFormat($project_stock_item->qty) : '';
            })
            ->addColumn('warehouse', function ($project_stock_item) {
                return $project_stock_item->warehouse ? $project_stock_item->warehouse->title : '';
            })
            ->addColumn('amount', function ($project_stock_item) {
                return $project_stock_item->productvariation ? amountFormat($project_stock_item->productvariation->price) : '';
        
            })->rawColumns(['tid', 'description','uom', 'qty','warehouse','amount'])
            ->make(true);
    }
    public function labour_skillsets(Request $request)
    {
       $quote_ids = explode(',', $request->quote_ids);
        $budget_skillsets = [];
       if($request->quote_ids != null){
        foreach ($quote_ids as $quote) {
            $budget_skillsets = BudgetSkillset::where('quote_id', $quote)->get();
        }
       }
       
        

        return Datatables::of($budget_skillsets)
            ->addIndexColumn()
            ->addColumn('skill', function ($budget_skillset) {
                return $budget_skillset->skill;
            })
            ->addColumn('charge', function ($budget_skillset)  {
                return $budget_skillset->charge;
            })
            ->addColumn('hours', function ($budget_skillset) {
                return $budget_skillset->hours;
            })
            ->addColumn('no_technician', function ($budget_skillset) {
                return $budget_skillset->no_technician;
            })
            ->addColumn('tid', function ($budget_skillset) {
                $tid = Quote::find($budget_skillset->quote_id);
                if($tid)
                return '<b>'.'QT-'.$tid->tid.'</b>';
            })
            ->addColumn('amount', function ($budget_skillset) {
                $total = $budget_skillset->charge * $budget_skillset->hours * $budget_skillset->no_technician;
                return amountFormat($total);
            })->rawColumns(['tid', 'skill','charge', 'hours','no_technician','amount'])
            ->make(true);
    }

    //Quote service items
    public function quotes_service_items(Request $request)
    {
        $quote_ids = explode(',', $request->quote_ids);
        $quote_item = [];
        $quote_items = QuoteItem::whereIn('quote_id', $quote_ids)->get();
        foreach ($quote_items as $quote) {
            $quote_item = $quote;
        }
        $variations = [];
        foreach($quote_items as $qq){
            $variations = $qq->variation()->get();
        }
        //dd($variations);
       //$variations = $quote_items->product()->first();
       
       $items = [];
       foreach ($variations as $variation) {
            $items = $variation->quote_service_items()->first();
       }
       if($items){
        return Datatables::of($variations)
            ->addIndexColumn()
            ->addColumn('tid', function ($variation) use ($quote_item){
                return '<b>'.'QT-'.$quote_item->quote->tid.'</b>';
            })
            ->addColumn('description', function ($variation)  {
                return $variation->name;
            })
            ->addColumn('uom', function ($variation) use ($quote_item) {
                return $quote_item->unit;
            })
            ->addColumn('qty', function ($variation) use ($quote_item) {
                return $quote_item->product_qty;
            })
            ->addColumn('amount', function ($variation) use ($quote_item) {
                $total = $quote_item->product_price;
                return amountFormat($total);
            })->rawColumns(['tid', 'description','uom','qty','amount'])
            ->make(true);
       }
       else{
        return Datatables::of($variations)
            ->addIndexColumn()
            ->addColumn('tid', function ($variation) use ($quote_item){
                return '';
            })
            ->addColumn('description', function ($variation)  {
                return '';
            })
            ->addColumn('uom', function ($variation) use ($quote_item) {
                return '';
            })
            ->addColumn('qty', function ($variation) use ($quote_item) {
                return '';
            })
            ->addColumn('amount', function ($variation) use ($quote_item) {
                $total = $quote_item->product_price;
                return '';
            })->rawColumns(['tid', 'description','uom','qty','amount'])
            ->make(true);
       }
        
    }
    
     public function edit_project_milestone($id)
    {
        $milestone = ProjectMilestone::findOrFail($id);
        return view('focus.projects.milestone.edit', compact('milestone'));
    }

    public function update_project_milestone(Request $request, $id)
    {
        $data = $request->only(['name','description','duedate','time_to','color','extimated_milestone_amount']);
        $data['due_date'] = date_for_database("{$data['duedate']} {$data['time_to']}:00");
        $data['note'] = $data['description'];
        unset($data['duedate'], $data['time_to'], $data['description']);
        $project_id = ProjectMilestone::find($id)->update($data);
        return redirect(url('projects', $request->project_id))->with('flash_status','Project MileStone Updated Successfully!!');

    }
}
