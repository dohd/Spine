<legend>Contract Properties</legend>
<hr>
<div class="form-group row">
    <div class="col-2">
        <label for="contract_no">Contract No</label>
        {{ Form::text('tid', @$last_tid+1, ['class' => 'form-control', 'readonly']) }}
    </div>
    <div class="col-4">
        <label for="customer">Customer</label>
        <select name="customer_id" id="customer" class="form-control" data-placeholder="Choose customer" required></select>
    </div>
    <div class="col-6">
        <label for="title">Title</label>
        {{ Form::text('title', null, ['class' => 'form-control', 'required']) }}
    </div>
</div>
<div class="form-group row">
    <div class="col-2">
        <label for="start_date">Start Date</label>
        {{ Form::text('start_date', null, ['class' => 'form-control datepicker']) }}
    </div>
    <div class="col-2">
        <label for="start_date">End Date</label>
        {{ Form::text('end_date', null, ['class' => 'form-control datepicker']) }}
    </div>
    <div class="col-2">
        <label for="amount">Amount</label>
        {{ Form::text('amount', '0.00', ['class' => 'form-control', 'required']) }}
    </div>
    <div class="col-2">
        <label for="period">Duration (Years)</label>
        {{ Form::number('period', 1, ['class' => 'form-control', 'id' => 'periodYr', 'required']) }}
    </div>
    <div class="col-2">
        <label for="period">Duration per Schedule (months)</label>
        {{ Form::number('schedule_period', 3, ['class' => 'form-control', 'id' => 'periodMn', 'required']) }}
    </div>
</div>
<div class="form-group row">
    <div class="col-6">
        <label for="description">Description</label>
        {{ Form::textarea('note', null, ['class' => 'form-control', 'rows' => '3', 'required']) }}
    </div>
</div>

<legend>Task Schedules</legend><hr>
<div class="form-group row">
    <div class="col-10">
        <div class="table-responsive">
            <table id="scheduleTbl" class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th width="15%">Start Date</th>
                        <th width="15%">End Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="text" name="s_title[]" class="form-control" required></td>
                        <td><input type="text" name="s_start_date[]" class="form-control datepicker" id="startdate-0"></td>
                        <td><input type="text" name="s_end_date[]" class="form-control datepicker" id="enddate-0"></td>
                        <td>
                            <button class="btn btn-outline-light btn-sm mt-1 remove">
                                <i class="fa fa-trash fa-lg text-danger"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="form-group row">
    <div class="col-12">
        <button class="btn btn-success btn-sm ml-2" type="button" id="addSchedule">
            <i class="fa fa-plus-square" aria-hidden="true"></i> Add Row
        </button>
    </div>    
</div>

<legend>Equipments</legend><hr>
<div class="table-responsive mb-1">
    <table id="equipmentTbl" class="table">
        <thead>
            <tr>
                <th>Serial No</th>
                <th>Type</th>
                <th>Branch</th>
                <th>Location</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr class="d-none">
                <td>#unique_id</td>
                <td>#make_type</td>
                <td>#branch</td>
                <td>#location</td>
                <td>
                    <button class="btn btn-outline-light btn-sm remove">
                        <i class="fa fa-trash fa-lg text-danger"></i>
                    </button>
                </td>
                <input type="hidden" name="equipment_id[]" value="#id">
            </tr>
        </tbody>
    </table>
</div>
<div class="form-group row">
    <div class="col-12">
        <button class="btn btn-success btn-sm ml-2 d-none" type="button" id="addEquipment">
            <i class="fa fa-plus-square" aria-hidden="true"></i> Add Row
        </button>
    </div>
    <div class="col-11">
        {{ Form::submit('Generate', ['class' => 'btn btn-primary float-right btn-lg']) }}
    </div>
</div>


@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})    
    .datepicker('setDate', new Date());

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

    // auto generate schedules
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
            $('#startdate-'+rowId).datepicker('setDate', new Date());
            $('#enddate-'+rowId).datepicker('setDate', new Date());
        });
    });
    $('#periodYr').change();
    // add schedule row
    $('#addSchedule').click(function() {
        rowId++;
        let html = scheduleRow.replace(/-0/g, '-'+rowId);
        $('#scheduleTbl tbody').append('<tr>' + html + '</tr>');
        $('#startdate-'+rowId).datepicker('setDate', new Date());
        $('#enddate-'+rowId).datepicker('setDate', new Date());
    });
    // remove schedule row
    $('#scheduleTbl').on('click', '.remove', function() {
        $(this).parents('tr').remove();
        rowId--;
    });

    // load machines on customer select
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