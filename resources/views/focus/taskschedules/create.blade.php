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
        const equipments = $('#equipmentTbl tbody tr').length;
        if (equipments < 1) {
            e.preventDefault();
            alert('Include at least one equipment!');
        }
    });

    // on contract select
    const equipRow =  $('#equipmentTbl tbody tr').html();
    const elements = ['#id', '#unique_id', '#make_type', '#branch', '#location'];
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
                data.forEach(obj => {
                    let html = equipRow.replace('d-none', '');
                    elements.forEach(el => {
                        for (let p in obj) {
                            if ('#'+p == el && p == 'branch') html = html.replace(el, obj.branch.name);
                            else if ('#'+p == el) html = html.replace(el, obj[p]? obj[p] : '');
                        }
                    });
                    $('#equipmentTbl tbody').append('<tr>' + html + '</tr>');
                })
            }
        })
    });
    
    // add schedule row
    $('#addEquipment').click(function() {
        $('#equipmentTbl tbody').append('<tr>' + scheduleRow + '</tr>');
    });
    // remove schedule row
    $('#equipmentTbl').on('click', '.remove', function() {
        $(this).parents('tr').remove();
    });
</script>
@endsection