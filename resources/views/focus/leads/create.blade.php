@extends ('core.layouts.app')

@section ('title', 'Leads Management | Create Lead')

@section('content')
<div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12">
                <h4 class="content-header-title">Leads Management</h4>
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
                                {{ Form::open(['route' => 'biller.leads.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'id' => 'create-productcategory']) }}
                                    <div class="form-group">
                                        @include("focus.leads.form")
                                        <div class="edit-form-btn">
                                            {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-lg pull-right']) }}
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
