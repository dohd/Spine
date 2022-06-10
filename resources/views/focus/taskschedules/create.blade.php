@extends('core.layouts.app')

@section('title', 'Load Machine | Task Schedule Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Task Schedule Management</h4>
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

    // form submit
    $('form').submit(function(e) {
        const equipments = $('#equipmentTbl .equipId:not(:disabled)').length;
        if (equipments < 1) {
            e.preventDefault();
            alert('Include at least one equipment!');
        }
    });

    // on contract select
    const equipRow =  $('#equipmentTbl tbody tr').html();
    $('#contract').change(function() {
        // load task schedules
        $.ajax({
            url: "{{ route('biller.contracts.task_schedules')  }}",
            type: 'POST',
            data: {id: $(this).val()},
            success: data => {
               $('#schedule option').remove();
               $('#schedule').append(new Option('-- Select Schedule --', ''));
                data.forEach(v => {                    
                    $('#schedule').append(new Option(v.title, v.id));
                });
            }
        })
        // load equipments
        $.ajax({
            url: "{{ route('biller.contracts.contract_equipment')  }}",
            type: 'POST',
            data: {id: $(this).val()},
            success: data => {
                $('#equipmentTbl tbody tr').remove();
                data.forEach(fillTable);
            }
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
        const select = $(this).is(':checked');
        const equipId = $(this).parents('tr').find('.equipId');
        const rate = $(this).parents('tr').find('.rate');
        if (select) {
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