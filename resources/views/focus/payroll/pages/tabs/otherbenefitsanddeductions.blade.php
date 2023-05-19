<div class="card-content">
    <form id="basicSalary" action="{{ route('biller.payroll.store_basic')}}" method="post">
        @csrf
        <input type="hidden" name="payroll_id" value="{{ $payroll->id }}" id="">
        <div class="card-body">
            <table class="table">
                <thead>
                    <th>Payroll Reference</th>
                    <th>Payroll Date</th>
                    <th>Month Days</th>
                    <th>Working Days</th>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="text" class="form-control" value="{{ $payroll->reference }}" readonly></td>
                        <td><input type="date" class="form-control" value=""></td>
                        <td><input type="text" class="form-control month_days" value="{{ $payroll->total_month_days }}" readonly></td>
                        <td><input type="text" class="form-control working_days" value="{{ $payroll->working_days }}" readonly></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-body">
            <table id="employeeTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Employee Id</th>
                        <th>Employee Name</th>
                        <th>Basic Pay</th>
                        <th>Absent Days</th>
                        <th>Present Days</th>
                        <th>Rate Per Day</th>
                        <th>Total Basic Pay</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 1;
                    @endphp
                    @foreach ($employees as $employee)
                        @if ($employee->employees_salary)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>{{ $employee->employees_salary->employee_name }}</td>
                            <input type="hidden" id="employee_id-{{$i}}" name="employee_id[]" value="{{ $employee->employees_salary->employee_id}}">
                            <input type="hidden" class="basic_salary" id="basic_salary-{{$i}}" value="{{ $employee->employees_salary->basic_pay }}">
                            <td>{{ amountFormat($employee->employees_salary->basic_pay) }}</td>
                            <td><input type="text" name="absent_days[]" class="form-control absent"  id="absent_days-{{$i}}"></td>
                            <td><input type="text" name="present_days[]" class="form-control present"  id="present_days-{{$i}}"></td>
                            <td>
                                <input type="text" name="rate_per_day[]" class="form-control rate"  id="rate-days-{{$i}}">
                                <input type="hidden" name="rate_per_month[]" class="form-control rate-month"  id="rate-month-{{$i}}">
                            </td>
                            <td><input type="text" name="basic_pay[]" class="form-control total"  id="total_basic_pay-{{$i}}"></td>
                        </tr>
                        @endif
                    @endforeach
                   
                </tbody>
            </table>
        </div>
        <div class="form-group">
            <div class="col-3">
                <label for="grand_total">Total Salary</label>
                <input type="text" name="salary_total" class="form-control" id="salary_total" readonly>
            </div>
        </div>
        <div class="float-right">
            <button type="submit" class="btn btn-primary submit-salary">Save Basic Pay</button>
        </div>
    </form>
    
    
</div>