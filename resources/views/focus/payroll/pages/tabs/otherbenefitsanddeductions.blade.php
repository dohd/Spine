<div class="card-content">
    <form id="basicSalary" action="{{ route('biller.payroll.store_otherdeduction') }}" method="post">
        @csrf
        <input type="hidden" name="payroll_id" value="{{ $payroll->id }}" id="">

        <div class="card-body">
            <table id="otherBenefitsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0"
                width="100%">
                <thead>
                    <tr>
                        <th>Employee Id</th>
                        <th>Employee Name</th>
                        <th>Benefits Totals</th>
                        <th>Deductions</th>
                        <th>Other Deductions Totals</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 1;
                    @endphp
                    @foreach ($payroll->payroll_items as $item)
                        @if ($item)
                            <tr>
                                <td>{{ $item->employee_id }}</td>
                                <td>{{ $item->employee_name }}</td>
                                <input type="hidden" name="id[]" value="{{ $item->id }}">
                                            <input type="hidden" name="payroll_id" value="{{ $item->payroll_id }}">
                                <td><input type="text" name="benefits_total[]" class="form-control benefits"
                                        id="benefits_total-{{ $i }}"></td>
                                <td>
                                    <table class="table" style="width: 100%;">
                                            <tr>
                                                <td>Loan</td>
                                                <td>Advance</td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input type="text" name="loan[]"
                                                        class="form-control loan"
                                                        id="loan-{{ $i }}">
                                                </td>
                                                <td>
                                                    <input type="text" name="advance[]"
                                                        class="form-control advance"
                                                        id="advance-{{ $i }}">
                                                </td>
                                            </tr>
                                       
                                    </table>
                                </td>
                               
                                <input type="hidden" name="total_sat_deduction[]">
                                


                                <td><input type="text" name="other_deductions[]" class="form-control other-deductions"
                                        id="other_deductions-{{ $i }}"></td>

                            </tr>
                        @endif
                    @endforeach

                </tbody>
            </table>
        </div>
        <div class="form-group">
            <div class="col-3">
                <label for="grand_total">Total Deductions and Benefits</label>
                <input type="text" name="benefits_deductions_total" class="form-control" id="benefits_deductions_total" readonly>
            </div>
        </div>
        <div class="float-right">
            <button type="submit" class="btn btn-primary submit-otherbenefits">Save Other Benefits and Deductions</button>
        </div>
    </form>


</div>
