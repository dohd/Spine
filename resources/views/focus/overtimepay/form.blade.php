<div class="form-group row">
    <div class="col-3">
        <label for="employee_name">Search Employee</label>
        <select class="form-control" id="employeebox" data-placeholder="Search Employee"></select>
        <input type="hidden" name="employee_id" value="{{ @$overtimepay->employee_name ?: 1 }}" id="employeeid">
        <input type="hidden" name="employee_name" value="{{ @$overtimepay->employee_name ?: 1 }}" id="employee">
    </div>
    <div class="col-3">
        <label for="date">Date</label>
        <input type="date" class="form-control" >
    </div>
    <div class="col-3">
        <label for="clock_in">Clock In</label>
        <input type="time" class="form-control" name="clock_in">
    </div>
    <div class="col-3">
        <label for="clock_out">Clock Out</label>
        <input type="time" class="form-control" name="clock_out" >
    </div>
</div>
<div class="form-group row">
    <div class="col-3">
        <select name="rate" id="rate" aria-placeholder="Search Overtime Rate">
            {{-- @foreach ($ as $item)
                
            @endforeach --}}
        </select>
    </div>
</div>
