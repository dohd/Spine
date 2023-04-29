@extends ('core.layouts.app',['page'=>'class="horizontal-layout horizontal-menu content-detached-right-sidebar" data-open="click" data-menu="horizontal-menu" data-col="content-detached-right-sidebar" '])

@section ('title', 'View | ' . trans('labels.backend.projects.management') )

@section('content')
    <!-- BEGIN: Content-->
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h3 class="content-header-title mb-0">{{trans('projects.project_summary')}}</h3>
                <div class="row breadcrumbs-top">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a
                                        href="{{route('biller.dashboard')}}">{{trans('core.home')}}</a>
                            </li>
                            <li class="breadcrumb-item"><a
                                        href="{{route('biller.projects.index')}}">{{trans('projects.projects')}}</a>
                            </li>
                            <li class="breadcrumb-item active">{{trans('projects.project_summary')}}
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="content-header-right col-md-6 col-12">
                <div class="media width-250 float-right">
                    <div class="media-left media-middle"></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ gen4tid("#", $project->tid) }}: {{ $project['name'] }}</h4>
            </div>
            <div class="card-content">
                <div class="card-body" id="pro_tabs">
                    {{-- navigation links --}}
                    <ul class="nav nav-tabs nav-top-border no-hover-bg" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab1" data-toggle="tab" href="#tab_data1"
                                aria-controls="tab_data1" role="tab" aria-selected="true"><i
                                        class="fa fa-lightbulb-o"></i> {{trans('projects.project_summary')}}</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" id="tab2" data-toggle="tab" href="#tab_data2"
                                aria-controls="tab_data2" role="tab" aria-selected="true"><i
                                        class="fa fa-flag-checkered"></i> {{trans('projects.milestones')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab3" data-toggle="tab" href="#tab_data3"
                                aria-controls="tab_data3" role="tab" aria-selected="true"><i
                                        class="icon-directions"></i> {{trans('tasks.tasks')}}</a>
                        </li>
                        @if($project->creator->id==auth()->user()->id)
                            <li class="nav-item">
                                <a class="nav-link" id="tab4" data-toggle="tab" href="#tab_data4"
                                    aria-controls="tab_data4" role="tab" aria-selected="true"><i
                                            class="fa fa-list-ol"></i> {{trans('projects.activity')}}</a>
                            </li> @endif
                        <li class="nav-item">
                            <a class="nav-link" id="tab5" data-toggle="tab" href="#tab_data5"
                                aria-controls="tab_data5" role="tab" aria-selected="true"><i
                                        class="fa fa-paperclip"></i> {{trans('general.files')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab6" data-toggle="tab" href="#tab_data6"
                                aria-controls="tab_data6" role="tab" aria-selected="true"><i
                                        class="icon-note"></i> {{trans('general.notes')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab9" data-toggle="tab" href="#tab_data9"
                                aria-controls="tab_data9" role="tab" aria-selected="true"><i
                                        class="ft-file-text"></i> Quotes & Proforma Invoices </a>
                        </li> 
                        <li class="nav-item">
                            <a class="nav-link" id="tab10" data-toggle="tab" href="#tab_data10"
                                aria-controls="tab_data10" role="tab" aria-selected="true"><i
                                        class="ft-file-text"></i> Budget </a>
                        </li> 

                        <li class="nav-item">
                            <a class="nav-link" id="tab11" data-toggle="tab" href="#tab_data11"
                                aria-controls="tab_data11" role="tab" aria-selected="true"><i
                                        class="ft-file-text"></i> Expenses </a>
                        </li> 

                        {{-- @if($project->creator->id == auth()->user()->id) --}}
                            <li class="nav-item">
                                <a class="nav-link" id="tab7" data-toggle="tab" href="#tab_data7"
                                    aria-controls="tab_data7" role="tab" aria-selected="true"><i
                                            class="ft-file-text"></i> {{trans('invoices.invoices')}}</a>
                            </li> 
                        {{-- @endif --}}
                        <li class="nav-item">
                            <a class="nav-link" id="tab8" data-toggle="tab" href="#tab_data8"
                                aria-controls="tab_data8" role="tab" aria-selected="true"><i
                                        class="ft-users"></i> {{trans('projects.users')}}</a>
                        </li>
                    </ul>

                    {{-- tab content --}}
                    <div class="tab-content px-1 pt-1">
                        <div class="tab-pane  active in" id="tab_data1" aria-labelledby="tab1" role="tabpanel">
                            <div class="card">
                                <div class="card-head">
                                    <div class="card-header">
                                        <h4 class="card-title">{{$project['name']}}</h4>
                                        <a class="heading-elements-toggle"><i
                                                    class="fa fa-ellipsis-v font-medium-3"></i></a>
                                    </div>

                                    <div class="px-1"><p>{{$project['short_desc']}}</p>
                                        <div class="heading-elements">
                                            @foreach ($project->tags as $row)
                                                <span class="badge"
                                                        style="background-color:{{$row['color']}}">{{$row['name']}}</span>
                                            @endforeach
                                        </div>
                                        <ul class="list-inline list-inline-pipe text-center p-1 border-bottom-grey border-bottom-lighten-3">
                                            <li>{{trans('projects.owner')}}: <span
                                                        class="text-muted text-bold-600 blue">{{$project->creator->first_name.' '.$project->creator->last_name}}</span>
                                            </li>
                                            <li>
                                                {{trans('customers.customer')}}: 
                                                <span class="text-bold-600 primary">
                                                    <a href="{{route('biller.customers.show',[$project->customer->id])}}">
                                                        {{ @$project->customer->company }} - {{ @$project->branch->name }}
                                                    </a>
                                                </span>
                                            </li>
                                            @if($project->worth>0.00)  <li>{{trans('projects.worth')}}: <span
                                                        class="text-bold-600 primary">{{amountFormat($project->worth)}}</span>
                                            </li>
                                            @endif
                                                </ul>  <ul class="list-inline list-inline-pipe text-center p-1 border-bottom-grey border-bottom-lighten-3">
                                            <li>{{trans('projects.start_date')}}: <span
                                                        class=" text-bold-600 purple">{{dateTimeFormat($project['start_date'])}}</span>
                                            </li>
                                            <li>{{trans('projects.end_date')}}: <span
                                                        class="text-bold-600 danger">{{dateTimeFormat($project['end_date'])}}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                {{-- project-info --}}
                                <div id="project-info" class="card-body row">
                                    <div class="project-info-count col-lg-3 col-md-12">
                                        <div class="project-info-icon">
                                            <h2>{{@count($project->users)}}</h2>
                                            <div class="project-info-sub-icon">
                                                <span class="fa fa-user-o"></span>
                                            </div>
                                        </div>
                                        <div class="project-info-text pt-1">
                                            <h5>{{trans('projects.users')}}</h5>
                                        </div>
                                    </div>
                                    <div class="project-info-count col-lg-3 col-md-12">
                                        <div class="project-info-icon">
                                            <h2 id="prog">{{@numberFormat($project->progress)}}%</h2>
                                            <div class="project-info-sub-icon">
                                                <span class="fa fa-rocket"></span>
                                            </div>
                                        </div>
                                        <div class="project-info-text pt-1">
                                            <h5>{{trans('projects.progress')}}</h5>
                                            <input type="range" min="0" max="100"
                                                    value="{{$project['progress']}}" class="slider"
                                                    id="progress">
                                        </div>
                                    </div>
                                    <div class="project-info-count col-lg-3 col-md-12">
                                        <div class="project-info-icon">
                                            <h2>{{@count($project->tasks)}}</h2>
                                            <div class="project-info-sub-icon">
                                                <span class="fa fa-calendar-check-o"></span>
                                            </div>
                                        </div>
                                        <div class="project-info-text pt-1">
                                            <h5>{{trans('tasks.tasks')}}</h5>
                                        </div>
                                    </div>
                                    <div class="project-info-count col-lg-3 col-md-12">
                                        <div class="project-info-icon">
                                            <h2>{{@count($project->milestones)}}</h2>
                                            <div class="project-info-sub-icon">
                                                <span class="fa fa-flag-checkered"></span>
                                            </div>
                                        </div>
                                        <div class="project-info-text pt-1">
                                            <h5>{{trans('projects.milestones')}}</h5>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <div class="card-subtitle line-on-side text-muted text-center font-small-3 mx-2 my-1">
                                        <span>{{trans('projects.eagle_view')}}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- project-overview --}}
                            <section class="row">
                                <div class="col-xl-12 col-lg-12 col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title">{{trans('general.description')}}</h4>
                                            <a class="heading-elements-toggle"><i
                                                        class="fa fa-ellipsis-v font-medium-3"></i></a>
                                            <div class="heading-elements">
                                                <ul class="list-inline mb-0">
                                                    <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                                                    <li><a data-action="close"><i class="ft-x"></i></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="card-content collapse show">
                                            <div class="card-body">
                                                {!! $project['note']!!}
                                            </div>
                                        </div>
                                    </div>
                                    <!--/ Project Overview -->
                                </div>
                                <!--/ Task Progress -->
                            </section>
                        </div>

                        {{-- milestone tab --}}
                        <div class="tab-pane" id="tab_data2" aria-labelledby="tab2" role="tabpanel">
                            {{-- @if(project_access($project->id)) --}}
                                <button type="button" class="btn btn-info" id="addt" data-toggle="modal"
                                        data-target="#AddMileStoneModal">
                                        <i class="fa fa-plus-circle"></i> Milestone
                                </button>
                            {{-- @endif --}}
                            <ul class="timeline">
                                @php
                                    $flag = true;
                                    $total = count($project->milestones);
                                @endphp
                                @foreach ($project->milestones as $row)
                                    <li class="{!! (!$flag)? 'timeline-inverted' : '' !!}" id="m_{{$row['id']}}">
                                        <div class="timeline-badge"
                                                style="background-color:@if ($row['color']) {{$row['color']}} @else #0b97f4  @endif;">{{$total}}</div>
                                        <div class="timeline-panel">
                                            <div class="timeline-heading">
                                                <h4 class="timeline-title">{{$row['name']}}</h4>
                                                <p>
                                                    <small class="text-muted">
                                                        [{{trans('general.due_date')}} {{dateTimeFormat($row['due_date'])}}]
                                                    </small>
                                                </p>
                                            </div>
                                            {{-- @if (project_access($project->id)) --}}
                                                <div class="timeline-body mb-1">
                                                    <p>{{$row['note']}}</p>
                                                    <p>Extimated Milestone Amount: <b>{{amountFormat($row['extimated_milestone_amount'])}}</b></p>
                                                    <a href="#" class=" delete-object" data-object-type="2" data-object-id="{{$row['id']}}">
                                                        <i class="danger fa fa-trash"></i>
                                                    </a>
                                                </div>
                                            {{-- @endif --}}
                                            <small class="text-muted"><i class="fa fa-user"></i>
                                                <strong>{{$row->creator->first_name}}  {{$row->creator->last_name}}</strong>
                                                <i class="fa fa-clock-o"></i> {{trans('general.created')}} {{dateTimeFormat($row['created_at'])}}
                                            </small>
                                        </div>
                                    </li>
                                    @php
                                        $flag = !$flag;
                                        $total--;
                                    @endphp
                                @endforeach
                            </ul>
                        </div>

                        {{-- tasks tab --}}
                        <div class="tab-pane" id="tab_data3" aria-labelledby="tab3" role="tabpanel">
                            {{-- @if(access()->allow('task-create') AND project_access($project->id)) --}}
                                <button type="button" class="btn btn-info float-right mr-2" data-toggle="modal"
                                        data-target="#AddTaskModal">
                                        <i class="fa fa-plus-circle"></i> Task
                                </button>
                            {{-- @endif --}}
                            <div class="card-body">
                                <table id="tasks-table"
                                        class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                        width="100%">
                                    <thead>
                                    <tr>
                                        <th>{{ trans('tasks.task') }}</th>
                                        <th>{{ trans('tasks.start') }}</th>
                                        <th>{{ trans('tasks.duedate') }}</th>
                                        <th>{{ trans('tasks.status') }}</th>
                                        <th>{{ trans('labels.general.actions') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                        </div>

                        {{-- activity tab --}}
                        <div class="tab-pane" id="tab_data4" aria-labelledby="tab4" role="tabpanel">
                            {{-- @if(project_access($project->id)) --}}
                                <button type="button" class="btn btn-info float-right mr-2" data-toggle="modal"
                                        data-target="#AddLogModal">
                                        <i class="fa fa-plus-circle"></i> Activity
                                </button>
                            {{-- @endif --}}
                            <div class="card-body">
                                <table id="log-table"
                                        class="table table-striped table-bordered zero-configuration"
                                        cellspacing="0"
                                        width="100%">

                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ trans('general.date') }}</th>
                                        <th>{{ trans('projects.users') }}</th>
                                        <th>{{ trans('general.description') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- files tab --}}
                        <div class="tab-pane" id="tab_data5" aria-labelledby="tab5" role="tabpanel">
                            {{-- @if(project_access($project->id)) --}}
                                <div class="card-body">
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <p class="lead">{{trans('general.attachment')}}</p>
                                            <pre>{{trans('general.allowed')}}:   {{@$features['value1']}} </pre>
                                            <!-- The fileinput-button span is used to style the file input field as button -->
                                            <div class="btn btn-success fileinput-button display-block col-4">
                                                <i class="glyphicon glyphicon-plus"></i>
                                                <span>Select files...</span>
                                                <!-- The file input field used as target for the file upload widget -->
                                                <input id="fileupload" type="file" name="files">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {{-- @endif --}}
                            <table id="files" class="files table table-striped mt-2">
                                @foreach($project->attachment as $row)
                                    <tr>
                                        <td>
                                            <a data-url="{{route('biller.project_attachment')}}?op=delete&id={{$row['id']}}"
                                                class="aj_delete red"><i class="btn-sm fa fa-trash"></i></a> <a
                                                    href="{{ Storage::disk('public')->url('app/public/files/' . $row['value']) }}"
                                                    class="purple"><i
                                                        class="btn-sm fa fa-eye"></i> {{$row['value']}}</a></td>
                                    </tr>
                                @endforeach
                            </table>

                        </div>

                        {{-- notes tab --}}
                        <div class="tab-pane" id="tab_data6" aria-labelledby="tab6" role="tabpanel">
                            <button type="button" class="btn btn-info float-right mr-2" data-toggle="modal"
                                    data-target="#AddNoteModal">
                                    <i class="fa fa-plus-circle"></i> Note
                            </button>

                            <div class="card-body">
                                <table id="notes-table"
                                        class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                        width="100%">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ trans('general.title') }}</th>
                                        <th>{{ trans('general.date') }}</th>
                                        <th>{{ trans('projects.users') }}</th>
                                        <th>{{ trans('general.action') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                        </div>

                        {{-- quotes tab --}}
                        <div class="tab-pane" id="tab_data9" aria-labelledby="tab9" role="tabpanel">
                            <div class="card-body">
                                <button type="button" class="btn btn-info float-right mr-2" id="addquote" data-toggle="modal"
                                        data-target="#AddQuoteModal">
                                        <i class="fa fa-plus-circle"></i> Add
                                </button>
                                <table id="quotesTbl"
                                        class="table table-striped table-bordered zero-configuration"
                                        cellspacing="0"
                                        width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Tr No.</th>
                                            <th>Customer - Branch</th>
                                            <th>Title</th>
                                            <th>Amount</th>
                                            <th>Ticket No.</th>
                                            <th>Invoice No.</th>
                                            <th>Budget Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- budget tab --}}
                        <div class="tab-pane" id="tab_data10" aria-labelledby="tab10" role="tabpanel">
                            <div class="card-body">
                                <button type="button" class="btn btn-info float-right mr-2" id="addbudget" data-toggle="modal"
                                        data-target="#AddBudgetModal"><i class="fa fa-plus-circle"></i> Add
                                </button>
                                <table id="budgetsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Tr No.</th>
                                            <th>Customer - Branch</th>
                                            <th>Quoted Amount</th>
                                            <th>Budgeted Amount</th>
                                            
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        
                        {{-- expenses --}}
                        <div class="tab-pane" id="tab_data11" aria-labelledby="tab11" role="tabpanel">
                            <div class="card-body">
                                <h4>Inventory Service Labour</h4>
                                <table id="serviceTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Quote/PI No.</th>
                                            <th>Description</th>
                                            <th>UoM</th>
                                            <th>Qty</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <h4 class="mt-3">Skillset Labour</h4>
                                <table id="labourTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Quote/PI No.</th>
                                            <th>Skill</th>
                                            <th>Charge</th>
                                            <th>Working Hrs.</th>
                                            <th>No. Technicians</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <h4 class="mt-3">Issued Stock</h4>
                                <table id="stockTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Quote/PI No.</th>
                                            <th>Description</th>
                                            <th>UoM</th>
                                            <th>Qty</th>
                                            <th>Warehouse</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <h4 class="mt-3">Purchased Stock To Project</h4>
                                <table id="purchaseTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Bill Number</th>
                                            <th>Type</th>
                                            <th>Description</th>
                                            <th>UOM</th>
                                            <th>Qty</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <h4 class="mt-3">Direct Purchase Expenses</h4>
                                <table id="expenseTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Bill Number</th>
                                            <th>Type</th>
                                            <th>Description</th>
                                            <th>UOM</th>
                                            <th>Qty</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- invoices tab --}}
                        <div class="tab-pane" id="tab_data7" aria-labelledby="tab7" role="tabpanel">
                            @if($project->creator->id == auth()->user()->id)
                               
                                <div class="card-body">
                                    <table id="invoices-table_p"
                                            class="table table-striped table-bordered zero-configuration"
                                            cellspacing="0"
                                            width="100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Invoice No.</th>
                                                <th>{{ trans('customers.customer') }}</th>
                                                <th>{{ trans('invoices.invoice_date') }}</th>
                                                <th>{{ trans('general.amount') }}</th>
                                                <th>{{ trans('general.status') }}</th>
                                                <th>{{ trans('invoices.invoice_due_date') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        {{-- users tab --}}
                        <div class="tab-pane" id="tab_data8" aria-labelledby="tab8" role="tabpanel">
                            <div class="card">
                                <div class="card-header mb-0">
                                    <h4 class="card-title">{{trans('projects.users')}}</h4>
                                    <a class="heading-elements-toggle"><i
                                                class="fa fa-ellipsis-v font-medium-3"></i></a>
                                    <div class="heading-elements">
                                        <ul class="list-inline mb-0">
                                            <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                                            <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                                            <li><a data-action="close"><i class="ft-x"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-content collapse show">
                                    <div class="card-content">
                                        <div class="card-body  py-0 px-0">
                                            <div class="list-group">
                                                @foreach ($project->users as $row)
                                                    <a href="javascript:void(0)" class="list-group-item">
                                                        <div class="media">
                                                            <div class="media-left pr-1"><span
                                                                        class="avatar avatar-sm"><img
                                                                            src="{{ Storage::disk('public')->url('app/public/img/users/' . @$row->picture) }}"><i></i></span>
                                                            </div>
                                                            <div class="media-body w-100">
                                                                <h6 class="media-heading mb-0">{{$row['first_name']}} {{$row['last_name']}}</h6>

                                                            </div>
                                                        </div>
                                                    </a>@endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- END: Content-->
    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>
    <input type="hidden" id="loader_url" value="{{route('biller.tasks.load')}}">

    @include('focus.projects.modal.task_view')
    @include('focus.projects.modal.milestone_new')
    @include('focus.projects.modal.log_new')
    @include('focus.projects.modal.quote_new')
    @include('focus.projects.modal.budget_new')
    @include('focus.projects.modal.note_new')
    @include('focus.projects.modal.delete_2')

    @if(access()->allow('create-task')) 
        @include('focus.projects.modal.task_new', ['project' => $project]) 
    @endif
@endsection

@section('after-styles')
    {{ Html::style('core/app-assets/css-'.visual().'/pages/project.css') }}
    {!! Html::style('focus/css/bootstrap-colorpicker.min.css') !!}
@endsection

@section('after-scripts')
@include('focus.projects.view-main_js')
@endsection