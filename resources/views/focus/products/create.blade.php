@extends ('core.layouts.app')

@section ('title', trans('labels.backend.products.management') . ' | ' . trans('labels.backend.products.create'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="mb-0">{{ trans('labels.backend.products.create') }}</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.products.partials.products-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.products.store', 'class' => 'form-horizontal', 'method' => 'post', 'files' => true, 'id' => 'create-product']) }}
                        <div class="form-group">
                            @include("focus.products.form")
                            <span class="mt-5 display-block"></span>
                            @if (strlen($fields) > 1)
                                <hr>
                                <a class="card-title purple">
                                    <i class="fa fa-plus-circle"></i>
                                    {{trans('customfields.customfields')}}                                                            
                                </a>
                                {!! $fields !!}
                            @endif
                            <div class="edit-form-btn mt-2">
                                {{ link_to_route('biller.products.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
                            </div><!--edit-form-btn-->
                        </div><!-- form-group -->
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection