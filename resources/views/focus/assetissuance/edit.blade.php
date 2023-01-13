@extends('core.layouts.app')

@section('title', 'Assetissuance | Edit')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Asset issuance Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.assetissuance.partials.assetissuance-header-buttons')
                </div>
            </div>
        </div>
    </div>    

    <div class="content-body"> 
        <div class="card">
            <div class="card-body">
                {{ Form::model($assetissuance, ['route' => ['biller.assetissuance.update', $assetissuance], 'method' => 'PATCH']) }}
                    <div class="form-group">
                        @include('focus.assetissuance.form')
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
@include('focus.assetissuance.form-js')
<script>

    // date
    // $('#issue_date').datepicker('setDate', new Date("{{ $assetissuance->issue_date }}"));
    // $('#return_date').datepicker('setDate', new Date("{{ $assetissuance->return_date }}"));

    // employee
    const employeeText = "{{ $assetissuance->employee_name }} ";
    const employeeVal = "{{ $assetissuance->employee_id }}";
    $('#employeebox').append(new Option(employeeText, true)).change();

</script>
@endsection
