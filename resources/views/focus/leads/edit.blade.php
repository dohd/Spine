@extends ('core.layouts.app')

@section ('title', 'Tickets Management | Edit')

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title">Tickets Management</h4>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right mr-3">
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
                                            @include("focus.leads.form")
                                            {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-lg pull-right mb-2']) }}
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
