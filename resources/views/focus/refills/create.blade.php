@extends ('core.layouts.app')

@section('title', 'Create | Product Refill Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Create Product Refill</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.refills.partials.refill-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.refills.store', 'method' => 'POST']) }}
                        @include('focus.refills.form')
                    {{ Form::close() }}
                </div>
            </div
        </div>
    </div>
</div>
@endsection
