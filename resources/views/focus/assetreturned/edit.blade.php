@extends('core.layouts.app')

@section('title', 'Asset Returns | Edit')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Asset returned Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.assetreturned.partials.assetreturned-header-buttons')
                </div>
            </div>
        </div>
    </div>    

    <div class="content-body"> 
        <div class="card">
            <div class="card-body">
                {{ Form::model($assetreturned, ['route' => ['biller.assetreturned.update', $assetreturned], 'method' => 'PATCH']) }}
                
                    @include('focus.assetreturned.form')
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
@include('focus.assetreturned.edit-form-js')
<script>
    // reference and tax
    // $('#ref_type').val("{{ $assetreturned->doc_ref_type }}");
    // $('#tax').val("{{ $assetreturned->tax }}");

    // date
    $('#issue_date').datepicker('setDate', new Date("{{ $assetreturned->issue_date }}"));
    $('#return_date').datepicker('setDate', new Date("{{ $assetreturned->return_date }}"));

    // employee
    const employeeText = "{{ $assetreturned->employee_name? $assetreturned->employee_name : $assetreturned->name }} : ";
    const employeeVal = "{{ $assetreturned->employee_id }}";
    $('#employeebox').append(new Option(employeeText, employeeVal, true, true)).change();

</script>
@endsection
