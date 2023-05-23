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
                        <th>Other Allowances Totals</th>
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
                                <td>{{ gen4tid('EMP-', $item->employee_id) }}</td>
                                <td>{{ $item->employee_name }}</td>
                                <input type="hidden" name="id[]" value="{{ $item->id }}">
                                <input type="hidden" name="payroll_id" value="{{ $item->payroll_id }}">
                                <td><input type="text" name="total_other_allowances[]" class="form-control other-allow"
                                        id="total_other_allowances-{{ $i }}">
                                </td>
                                <td><input type="text" name="total_benefits[]" class="form-control benefits"
                                        id="total_benefits-{{ $i }}">
                                </td>


                                <td>
                                    <table class="table" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>Loan</th>
                                                <th>Advance</th>
                                            </tr>

                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <input type="text" name="loan[]" class="form-control loan"
                                                        id="loan-{{ $i }}">
                                                </td>
                                                <td>
                                                    <input type="text" name="advance[]" class="form-control advance"
                                                        id="advance-{{ $i }}">
                                                </td>
                                            </tr>
                                        </tbody>


                                    </table>
                                </td>

                                <td><input type="text" name="total_other_deduction[]"
                                        class="form-control other-deductions"
                                        id="total_other_deduction-{{ $i }}">
                                </td>


                            </tr>
                        @endif
                    @endforeach

                </tbody>
            </table>
        </div>
        <div class="form-group row">
            <div class="col-3">
                <label for="grand_total">Total Other Benefits</label>
                <input type="text" name="other_benefits_total" class="form-control" id="other_benefits_total"
                    readonly>
            </div>
            <div class="col-3">
                <label for="grand_total">Total Other Deductions</label>
                <input type="text" name="other_deductions_total" class="form-control" id="other_deductions_total"
                    readonly>
            </div>
            <div class="col-3">
                <label for="grand_total">Total Other Allowances</label>
                <input type="text" name="other_allowances_total" class="form-control" id="other_allowances_total"
                    readonly>
            </div>
        </div>
        <div class="float-right">
            <button type="submit" class="btn btn-primary submit-otherbenefits">Save Other Benefits and
                Deductions</button>
        </div>
    </form>


</div>
