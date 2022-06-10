@extends('core.layouts.app')

@section('title', 'View | Contract Service Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Contract Service Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.contractservices.partials.contractservices-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="serviceTbl" class="table table-bordered table-sm mb-2">
                                @php
                                    $details = [
                                        'Contract' => $contractservice->contract->title,
                                        'Task Schedule' => $contractservice->task_schedule->title,
                                        'Service Name' => $contractservice->name,
                                        'Service Rate' => numberFormat($contractservice->amount),
                                    ];
                                @endphp
                                @foreach ($details as $key => $val)
                                    <tr>
                                        <th>{{ $key }}</th>
                                        <td>{{ $val }}</td>
                                    </tr>
                                @endforeach
                            </table>
                            {{ Form::open(['route' => ['biller.contractservices.update', $contractservice], 'method' => 'PATCH']) }}
                                @include('focus.contractservices.form')
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
<script>  
    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    .datepicker('setDate', new Date());
    
    // on change row checkbox
    $('#equipmentTbl').on('change', '.select', function() {
        const select = $(this).is(':checked');
        const charged = $(this).parents('tr').find('.charged');
        const rate = $(this).parents('tr').find('.rate');
        if (select) {
            charged.val(1);
            rate.attr('disabled', false);
        } else {
            charged.val(0);
            rate.attr('disabled', true);
        }  
        calcTotal();
    })

    // on change action checkbox
    $('#selectAll').change(function() {
        const selectAll = $(this).is(':checked');
        $('#equipmentTbl tbody tr').each(function() {
            if (selectAll) $(this).find('.select').prop('checked', true).change();
            else $(this).find('.select').prop('checked', false).change();
        });
    });
    
    // compute total rate
    function calcTotal() {
        let totalRate = 0;
        $('#equipmentTbl tbody tr').each(function() {
            const rate = $(this).find('.rate:not(:disabled)');
            if (rate.val()) totalRate += parseFloat(rate.val());
        });
        $('#totalRate').val(parseFloat(totalRate.toFixed(2)).toLocaleString());
    }    

    // update jobcard date and checked rows
    const serviceItems = @json($contractservice->items);
    serviceItems.forEach((v, i) => {
        if (v.jobcard_date) $('#jobcardDate-'+i).datepicker('setDate', new Date(v.jobcard_date));
        else $('#jobcardDate-'+i).val(null);
        if (v.is_charged == 1) $('#chargeCheck-'+i).prop('checked', true).change();
    });    
</script>
@endsection