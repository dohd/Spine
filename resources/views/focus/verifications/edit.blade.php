@extends ('core.layouts.app')

@section('title', 'Edit | Verification Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Verification Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.verifications.partials.verification-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($verification, ['route' => array('biller.verifications.update', $verification), 'method' => 'PATCH']) }}
                        @include('focus.verifications.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
