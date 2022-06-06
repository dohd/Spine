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