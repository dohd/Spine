<div class="form-group row">
    <div class="col-6">
        <label for="contract">Contract</label>
        <select name="contract_id" id="contract" class="form-control" data-placeholder="Choose Contract" required>
            <option value="">-- Select Contract --</option>
            @foreach ($contracts as $row)
                <option value="{{ $row->id }}">{{ $row->tid }} - {{ $row->title }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-6">
        <label for="schedule">Task Schedule</label>
        <select name="schedule_id" id="schedule" class="form-control" data-placeholder="Choose Task Schedule" required>
            <option value="">-- Select Schedule --</option>
        </select>
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
        {{ Form::submit('Load', ['class' => 'btn btn-primary float-right btn-lg']) }}
    </div>
</div>

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

    // load machines on customer select
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