@extends ('core.layouts.app')
@section ('title', trans('labels.backend.productcategories.edit') . '|' .trans('labels.backend.productcategories.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-2">
        <div class="content-header-left col-md-6 col-12">
            <h4 class="mb-0">{{ trans('labels.backend.productcategories.edit') }}</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.refill_product_categories.partials.productcategories-header-buttons')
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
                            {{ Form::model($productcategories, ['route' => ['biller.refill_product_categories.update', $product_category], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'edit-productcategory']) }}
                                <div class="form-group">
                                    @include("focus.productcategories.form")
                                    <div class="edit-form-btn">
                                        {{ link_to_route('biller.productcategories.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                        {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
                                        <div class="clearfix"></div>
                                    </div><!--edit-form-btn-->
                                </div><!--form-group-->
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection