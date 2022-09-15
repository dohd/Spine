@extends ('core.layouts.app')

@section('title', 'Edit | Product Opening Stock')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Product Opening Stock</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.opening_stock.partials.opening-stock-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($opening_stock, ['route' => array('biller.opening_stock.update', $opening_stock), 'method' => 'PATCH']) }}
                        @include('focus.opening_stock.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
