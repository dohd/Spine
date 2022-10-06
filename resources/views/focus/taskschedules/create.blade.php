@extends('core.layouts.app')

@section('title', 'Load Machine | Schedule Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Schedule Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                @include('focus.taskschedules.partials.taskschedule-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            {{ Form::open(['route' => 'biller.taskschedules.store']) }}
                                @include('focus.taskschedules.form')
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    .datepicker('setDate', new Date());

    $('#schedule').change(function () {
        const opt = $(this).find(':selected');
        const startDate = $(this).val()? new Date(opt.attr('actual_start')) : new Date();
        const endDate = $(this).val()? new Date(opt.attr('actual_end')) : new Date();
        $('#actual_startdate').datepicker('setDate', startDate);
        $('#actual_enddate').datepicker('setDate', endDate);
    });

    // on contract select
    const equipRow =  $('#equipmentTbl tbody tr').html();
    $('#contract').change(function() {
        const contract_id = $(this).val();
        $('#equipmentTbl tbody tr').remove();
        $('#schedule option:not(:first)').remove();
        // load task schedules
        $.ajax({
            url: "{{ route('biller.contracts.task_schedules')  }}",
            type: 'POST',
            data: {contract_id},
            success: data => {
                data.forEach(v => $('#schedule').append(
                    `<option value="${v.id}" 
                        actual_start="${v.actual_startdate ? v.actual_startdate : v.start_date}" 
                        actual_end="${v.actual_enddate ? v.actual_enddate : v.end_date}"
                    >
                        ${v.title}
                    </option>`
                ));               
            }
        });
        // load equipments
        $.ajax({
            url: "{{ route('biller.contracts.contract_equipment') }}",
            type: 'POST',
            data: {contract_id, is_schedule: 1},
            success: data => data.forEach(fillTable)
        })
    });
    function fillTable(obj) {
        let html = equipRow.replace('d-none', '');
        let elements = ['#id', '#unique_id', '#make_type', '#branch', '#location', '#service_rate'];
        elements.forEach(el => {
            for (let p in obj) {
                if ('#'+p == el && p == 'branch') html = html.replace(el, obj.branch.name);
                else if ('#'+p == el && p == 'service_rate') {
                    html = html.replace(el, parseFloat(obj.service_rate).toLocaleString())
                    .replace(el, obj.service_rate);
                } 
                else if ('#'+p == el) html = html.replace(el, obj[p]? obj[p] : '');                
            }
        });
        $('#equipmentTbl tbody').append('<tr>' + html + '</tr>');
    }

    // on change row checkbox
    $('#equipmentTbl').on('change', '.select', function() {
        const equipId = $(this).parents('tr').find('.equipId');
        const rate = $(this).parents('tr').find('.rate');
        if ($(this).is(':checked')) {
            equipId.attr('disabled', false);
            rate.attr('disabled', false);
        } else {
            equipId.attr('disabled', true);
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
</script>
@endsection