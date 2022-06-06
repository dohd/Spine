@extends ('core.layouts.app')

@section ('title', 'Create | Contract Management')

@section('content')
<div>
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Contract Management</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.contracts.partials.contracts-header-buttons')
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
                                {{ Form::open(['route' => 'biller.contracts.store']) }}
                                    @include('focus.contracts.form')
                                {{ Form::close() }}
                            </div>
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

    function renderDatepicker() {
        return $('.datepicker')
            .datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true}) 
            .datepicker('setDate', new Date());   
    }
    renderDatepicker();

    // form submit
    $('form').submit(function(e) {
        const schedules = $('#scheduleTbl tbody tr').length;
        const equipments = $('#equipmentTbl tbody tr').length;
        if (schedules < 1 || equipments < 1) {
            e.preventDefault();
            alert('Include at least one schedule task or equipment!');
        }
    });

    // customer select config
    $("#customer").select2({
        ajax: {
            url: "{{ route('biller.customers.select') }}",
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: ({term}) => ({ search: term }),
            processResults: data => {
                return {
                    results: data.map(v => ({ 
                        id: v.id, 
                        text: `${v.name} - ${v.company}`,
                    }))
                };
            },
        }
    });    

    // auto generate default schedules
    let rowId = 0;
    const scheduleRow = $('#scheduleTbl tbody tr').html();
    $('form').on('change', '#periodYr, #periodMn', function() {
        const yrs = $('#periodYr').val();
        const months = $('#periodMn').val();
        if (!yrs || !months) return;
        const n = Math.round(yrs * 12 / months);
        $('#scheduleTbl tbody tr').remove();
        Array.from({length: n}, v => v)
        .forEach(v => {
            rowId++;
            let html = scheduleRow.replace(/-0/g, '-'+rowId);
            $('#scheduleTbl tbody').append('<tr>' + html + '</tr>');
            renderDatepicker();
        });
    });
    $('#periodYr').change();
    // add schedule row
    $('#addSchedule').click(function() {
        rowId++;
        let html = scheduleRow.replace(/-0/g, '-'+rowId);
        $('#scheduleTbl tbody').append('<tr>' + html + '</tr>');
        $('.datepicker')
        .datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true}) 
        $('#startdate-'+ rowId).datepicker('setDate', new Date());
        $('#enddate-'+ rowId).datepicker('setDate', new Date());
    });
    // remove schedule row
    $('#scheduleTbl').on('click', '.remove', function() {
        $(this).parents('tr').remove();
        rowId--;
    });

    // on customer select load equipments
    const equipRow =  $('#equipmentTbl tbody tr').html();
    const elements = ['#id', '#unique_id', '#make_type', '#branch', '#location'];
    $('#customer').change(function() {
        $.ajax({
            url: "{{ route('biller.contracts.customer_equipment')  }}",
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
    
    // add equipmentTbl row
    $('#addEquipment').click(function() {
        $('#equipmentTbl tbody').append('<tr>' + scheduleRow + '</tr>');
    });
    // remove equipmentTbl row
    $('#equipmentTbl').on('click', '.remove', function() {
        $(this).parents('tr').remove();
    });
</script>
@endsection