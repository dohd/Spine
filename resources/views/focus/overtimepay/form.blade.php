<div class="form-group">
    <div class="col-4">
        <label for="employee_name">Search Employee</label>
        <select class="form-control" id="employeebox" data-placeholder="Search Employee"></select>
        <input type="hidden" name="employee_id" value="{{ @$overtimepay->employee_name ?: 1 }}" id="employeeid">
        <input type="hidden" name="employee_name" value="{{ @$overtimepay->employee_name ?: 1 }}" id="employee">
    </div>
    <div class="col-4">
        <label for="date">Date</label>
        <input type="date" class="form-control" >
    </div>
    <div class="col-4">
        <label for="clock_in">Clock In</label>
        <input type="time" class="form-control" name="clock_in">
    </div>
    <div class="col-4">
        <label for="clock_out">Clock Out</label>
        <input type="time" class="form-control" name="clock_out" >
    </div>
    <div class="col-4">
    </div>
</div>
