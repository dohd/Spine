@extends ('core.layouts.app')

@section ('title', 'Asset & Equipments Management | Asset & Equipments Create')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="mb-0">Create Asset & Equipments</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.assetequipments.partials.assetequipments-header-buttons')
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
                            {{ Form::open(['route' => 'biller.assetequipments.store', 'method' => 'POST', 'id' => 'create-productcategory']) }}
                            <div class="form-group">
                                {{-- Including Form blade file --}}
                                @include("focus.assetequipments.form")
                                <div class="edit-form-btn">
                                    {{ link_to_route('biller.assetequipments.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                    {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
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
@endsection