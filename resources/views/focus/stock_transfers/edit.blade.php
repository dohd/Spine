@extends ('core.layouts.app')

@section('title', 'Edit | Stock Transfer Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Stock Transfer Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.stock_transfers.partials.stock-transfer-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($stock_transfer, ['route' => array('biller.stock_transfers.update', $stock_transfer), 'method' => 'PATCH']) }}
                        @include('focus.stock_transfers.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
