@extends ('core.layouts.app')

@section('title', 'Create | Purchase Requisition Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Purchase Requisition Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.purchase_requests.partials.purchase-request-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.purchase_requests.store', 'method' => 'POST', 'files' => false]) }}
                        @include('focus.purchase_requests.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
