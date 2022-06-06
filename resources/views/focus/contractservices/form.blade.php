<div class="form-group row">
    <div class="col-6">
        <label for="customer">Customer</label>
        <select name="customer_id" id="customer" class="form-control" data-placeholder="Choose customer" required></select>
    </div>
    <div class="col-6">
        <label for="branch">Branch</label>
        <select name="branch_id" id="branch" class="form-control" data-placeholder="Choose branch" required></select>
    </div>
</div>
<div class="form-group row">
    <div class="col-6">
        <label for="contract">Contract</label>
        <select name="contract_id" id="contract" class="form-control" data-placeholder="Choose Contract" required>
            <option value="">-- Select Contract --</option>
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
        {{ Form::submit('Create', ['class' => 'btn btn-primary float-right btn-lg', 'disabled']) }}
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

    // on selecting a customer
    $("#branch").select2();
    $("#customer").change(function() {
        const customer_id = $(this).val();
        // fetch branches
        $("#branch").html('').select2({
            ajax: {
                url: "{{ route('biller.branches.branch_load') }}",
                quietMillis: 50,
                data: ({term}) => ({search: term, customer_id}),                                
                processResults: data => {
                    return { results: data.map(v => ({ text: v.name, id: v.id })) };
                },
            }
        });

    });

    $('#branch').change(function() {
        // fetch contracts
        $.ajax({
            url: "{{ route('biller.contracts.customer_contracts') }}",
            type: 'POST',
            data: {id: $('#customer').val(), branch_id: $(this).val()},
            success: data => {
                console.log(data);
                $('#contract option').remove();
                $('#contract').append(new Option('-- Select Contract --', ''));
                data.forEach(v => {
                    $('#contract').append(new Option(v.title, v.id));
                })
            }
        });
    });
  

    // on customer select
    const equipRow =  $('#equipmentTbl tbody tr').html();
    const elements = ['#id', '#unique_id', '#make_type', '#branch', '#location'];
    $('#contract').change(function() {
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