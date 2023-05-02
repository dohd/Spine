@extends ('core.layouts.app')

@section('title', 'Bill Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Bill Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.utility-bills.partials.utility-bills-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.utility-bills.store', 'method' => 'POST']) }}
                        @include('focus.utility-bills.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
