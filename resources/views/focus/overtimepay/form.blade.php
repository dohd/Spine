<div class="form-group row">
    <div class="col-3">
        <label for="employee_name">Search Employee</label>
        <select class="form-control" id="employeebox" data-placeholder="Search Employee"></select>
        <input type="hidden" name="employee_id" value="{{ @$overtimepay->employee_id?: 1 }}" id="employeeid">
        
    </div>
    <div class="col-3">
        <label for="date">Date</label>
        {{ Form::date('date', null, ['class' => 'form-control', 'placeholder' => '']) }}
    </div>
    <div class="col-3">
        <label for="clock_in">Clock In</label>
        {{ Form::time('clock_in', null, ['class' => 'form-control']) }}
    </div>
    <div class="col-3">
        <label for="clock_out">Clock Out</label>
        {{ Form::time('clock_out', null, ['class' => 'form-control']) }}
    </div>
</div>
<div class="form-group row">
    <div class="col-3">
        <select name="overtimerate_id" id="rate" class="form-control" aria-placeholder="Search Overtime Rate">
            @foreach ($overtimerates as $overtime)
                <option value="{{ $overtime->id }}">{{ $overtime->name }}</option>
            @endforeach
        </select>
    </div>
</div>
