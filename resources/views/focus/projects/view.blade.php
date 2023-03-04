@extends ('core.layouts.app', ['page' => 'class="horizontal-layout horizontal-menu content-detached-right-sidebar"
data-open="click" data-menu="horizontal-menu" data-col="content-detached-right-sidebar"'])

@section('title', trans('labels.backend.projects.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title mb-0">{{ trans('projects.project_summary') }}</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('biller.dashboard') }}">{{ trans('core.home') }}</a> </li>
                        <li class="breadcrumb-item"><a href="{{ route('biller.projects.index') }}">{{ trans('projects.projects') }}</a> </li>
                        <li class="breadcrumb-item active">{{ trans('projects.project_summary') }} </li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-left media-middle">
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ $project->name }}</h4>
            <div class="btn-group mt-1">
                <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#statusModal">
                    <i class="fa fa-pencil" aria-hidden="true"></i> Status
                </a>
                @include('focus.projects.modal.status_modal')
            </div>
        </div>
        <div class="card-content">
            <div class="card-body" id="pro_tabs">
                <ul class="nav nav-tabs nav-top-border no-hover-bg" role="tablist">
                    {{-- project summary --}}
                    <li class="nav-item">
                        <a class="nav-link active" id="tab1" data-toggle="tab" href="#tab_data1" aria-controls="tab_data1" role="tab" aria-selected="true">                                
                            <i class="fa fa-lightbulb-o"></i> {{ trans('projects.project_summary') }}
                        </a>
                    </li>
                    
                    {{-- project income --}}
                    <li class="nav-item">
                        <a class="nav-link" id="tab8" data-toggle="tab" href="#tab_data8" aria-controls="tab_data8" role="tab" aria-selected="true">
                            <i class="fa fa-money"></i>Income
                        </a>                           
                    </li>
                    {{-- project expense --}}
                    <li class="nav-item">
                        <a class="nav-link" id="tab9" data-toggle="tab" href="#tab_data9" aria-controls="tab_data9" role="tab" aria-selected="true">
                            <i class="fa fa-money"></i>Expense
                        </a>                           
                    </li>
                    {{-- project percentage profit --}}
                    <li class="nav-item">
                        <a class="nav-link" id="tab10" data-toggle="tab" href="#tab_data10" aria-controls="tab_data10" role="tab" aria-selected="true">
                            <i class="font-weight-bold">%</i>Percentage Profit
                        </a>                           
                    </li>
                </ul>
                <div class="tab-content px-1 pt-1">
                    @include('focus.projects.tabs.summary')
                    {{-- 
                    @include('focus.projects.tabs.milestone')
                    @include('focus.projects.tabs.task')
                    @include('focus.projects.tabs.activity')
                    @include('focus.projects.tabs.file')
                    @include('focus.projects.tabs.note')
                    @include('focus.projects.tabs.user')
                     --}}
                    @include('focus.projects.tabs.income')
                    @include('focus.projects.tabs.expense')
                    @include('focus.projects.tabs.percentage_profit')
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sidenav-overlay"></div>
<div class="drag-target"></div>
<input type="hidden" id="loader_url" value="{{ route('biller.tasks.load') }}">
@include('focus.projects.modal.task_view')
@include('focus.projects.modal.milestone_new')
@include('focus.projects.modal.log_new')
@include('focus.projects.modal.note_new')
@if (access()->allow('create-task'))
    @include('focus.projects.modal.task_new')
@endif
@include('focus.projects.modal.delete_2')
@endsection

@section('after-styles')
{{ Html::style('core/app-assets/css-' . visual() . '/pages/project.css') }}
{!! Html::style('focus/css/bootstrap-colorpicker.min.css') !!}
@endsection

@section('after-scripts')
{{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
{!! Html::style('focus/jq_file_upload/css/jquery.fileupload.css') !!}
{{ Html::script('focus/jq_file_upload/js/jquery.fileupload.js') }}
@include('focus.projects.view-js')
@endsection
