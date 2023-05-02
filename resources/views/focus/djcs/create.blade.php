@extends ('core.layouts.app')

@section ('title', 'Create | Diagnosis Job Card Report')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Djc Report Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.djcs.partials.djcs-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.djcs.store', 'method' => 'POST', 'files' => true ]) }}
                        @include('focus.djcs.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
@include('focus.djcs.form_js')
@endsection
