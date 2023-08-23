@extends ('core.layouts.app')

@section('title', 'Create | Refill Customer Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Create Refill Customer </h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.refill_customers.partials.refill-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.refill_customers.store', 'method' => 'POST']) }}
                        @include('focus.refill_customers.form')
                    {{ Form::close() }}
                </div>
            </div
        </div>
    </div>
</div>
@endsection
