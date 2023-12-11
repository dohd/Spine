<div class="form-group row">
    <div class="col-md-6">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' =>'labour_date']) }}
        {{ Form::hidden('id', null, ['class' => 'form-control']) }}
    </div>
    <div class="col-md-6">
        <label for="type">Job Type</label>
        <select name="type" id="type" class="custom-select" required>
            <option value="">-- Select Job Type --</option>
            @foreach(['diagnosis', 'repair', 'maintenance', 'installation', 'supply', 'special_movement_allowance', 'paid_idle_time', 'others'] as $value)
                <option value="{{ $value }}" {{ $value == $labour_allocation->type? 'selected' : '' }}>
                    @php
                        if ($value == 'diagnosis') echo 'Diagnosis / Site Survey';
                        elseif ($value == 'special_movement_allowance') echo 'Special Movement Allowance (> 3Hrs)';
                        elseif ($value == 'paid_idle_time') echo 'Paid Idle Time';
                        else echo ucfirst($value);
                    @endphp
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group row">
    <div class="col-md-4">
        <label for="job_card">Job Card / DNote</label>
        <div class="row no-gutters">
            <div class="col-md-6">
                <select name="ref_type" class="custom-select">
                    @foreach (['jobcard' => 'Job Card', 'dnote' => 'Delivery Note'] as $key => $value)
                        <option value="{{ $key }}" {{ $key == $labour_allocation->ref_type? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                {{ Form::text('job_card', null, ['class' => 'form-control', 'id' =>'job_card']) }}
            </div>
        </div>
    </div>
                        
    <div class="col-md-4">
        <label for="hrs">Hours</label>
        {{ Form::text('hrs', null, ['class' => 'form-control', 'required']) }}
    </div>

    <div class="col-md-4">
        <label for="hrs">Is Payable</label>
        <select name="is_payable" id="is_payable" class="custom-select">
            @foreach(['1' => 'Yes','0' => 'No'] as $key => $value)
                <option value="{{ $key }}" {{ $key == $labour_allocation->is_payable? 'selected' : '' }}>{{ $value }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 mt-1">

        <label for="project_milestone" class="caption" style="display: inline-block;">Project Budget Line</label>
        <select id="project_milestone" name="project_milestone" class="form-control">
            <option value="">Select a Budget Line</option>
        </select>

    </div>

</div>

<div class="form-group row">
    <div class="col-md-12">
        <label for="note">Note</label>
         {{ Form::text('note', null, ['class' => 'form-control', 'id' =>'note']) }}
    </div>
</div>

<div class="table-responsive" id="employeeTbl">
    <table class="table table-sm">
        <thead>
            <th>#</th>
            <th>Employee</th>
            <th>Action</th>
        </thead>
        <tbody>
            <!-- row template -->
            <tr>
                <td></td>
                <td>
                    <select name="employee_id[]" id="employee_id-0" class="form-control employee">
                        @foreach($employees as $employee)
                            <option value="{{ $employee['id'] }}">
                                {{ $employee->fullname }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" value="0" id="id-0" name="id[]">
                </td>
                <td><button type="button" class="btn btn-primary remove">Remove</button></td>
            </tr>
            <!-- end row template -->
            @isset($labour_allocation->items)
                @foreach ($labour_allocation->items as $i => $item)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>
                            <select name="employee_id[]" id="employee_id-{{$i}}" class="form-control employee">
                                @foreach($employees as $employee)
                                    <option value="{{ $employee['id'] }}" {{@$item->employee_id == $employee['id'] ? 'selected' : '' }}>
                                        {{ $employee->fullname }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" value="{{$item->id}}" id="id-{{$i}}" name="id[]">
                        </td>
                        <td><button type="button" class="btn btn-primary remove">Remove</button></td>
                    </tr>
                @endforeach
            @endisset
        </tbody>
    </table>
</div>

<div class="form-group row">
    <div class="col-md-12">
        <button type="button" class="btn btn-success btn-sm ml-3" aria-label="Left Align" id="addstock">
            <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
        </button>
    </div>
</div>

<div class="form-group row">
    <div class="col-md-12">
        {{ Form::submit('Update Record', ['class' => 'btn btn-primary btn-lg float-right mr-3']) }}
    </div>
</div>