<div class="card-content">
    <form id="basicSalary" action="{{ route('biller.payroll.store_basic') }}" method="post">
        @csrf
        <input type="hidden" name="payroll_id" value="{{ $payroll->id }}" id="">

        <div class="card-body">
            <table id="employeeTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0"
                width="100%">
                <thead>
                    <tr>
                        <th>Employee Id</th>
                        <th>Employee Name</th>
                        <th>Benefits Totals</th>
                        <th colspan="2">Deductions</th>
                        <th>Other Deductions Totals</th>
                        

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
                                <input type="hidden" id="employee_id-{{ $i }}" name="employee_id[]"
                                    value="{{ $employee->employees_salary->employee_id }}">
                                <input type="hidden" class="basic_salary" id="basic_salary-{{ $i }}"
                                    value="{{ $employee->employees_salary->basic_pay }}">
                                <td><input type="text" name="absent_days[]" class="form-control absent"
                                        id="absent_days-{{ $i }}"></td>



                                <td><input type="text" name="absent_days[]" class="form-control absent"
                                        id="absent_days-{{ $i }}"></td>
                                <td><input type="text" name="absent_days[]" class="form-control absent"
                                        id="absent_days-{{ $i }}"></td>


                                <td><input type="text" name="absent_days[]" class="form-control absent"
                                        id="absent_days-{{ $i }}"></td>

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
            <button type="submit" class="btn btn-primary submit-salary">Save Other Benfits and Deductions</button>
        </div>
    </form>


</div>
