@extends ('core.layouts.app')

@section ('title', 'Business Tenant Management | Edit')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="mb-0">Edit Tenant</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.tenants.partials.tenants-header-buttons')
                </div>
            </div>
        </div>
    </div>
    <div class="content-body">
        {{ Form::model($tenant, ['route' => ['biller.tenants.update', $tenant], 'method' => 'PATCH', 'files' => true]) }}
        <div class="form-group">                                    
            @include("focus.tenants.form")
            <div class="edit-form-btn">
                {{ link_to_route('biller.tenants.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
            </div>                                   
        </div>                              
        {{ Form::close() }}
    </div>
</div>
@endsection