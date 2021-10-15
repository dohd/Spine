@extends ('core.layouts.app')

@section ('title', 'Lead Management |  Lead Edit')

@section('page-header')
    <h1>
         Lead Management
        <small>Edit Lead</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="mb-0">Edit Lead</h4>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.leads.partials.leads-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-body">
                                    {{ Form::model($lead, ['route' => ['biller.leads.update', $lead], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'edit-lead']) }}
                                        <div class="form-group">
                                            {{-- Including Form blade file --}}
                                            @include("focus.leads.form")
                                            <div class="edit-form-btn">
                                                {{ link_to_route('biller.leads.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                                {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
                                                <div class="clearfix"></div>
                                            </div>
                                        </div>
                                    {{ Form::close() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
