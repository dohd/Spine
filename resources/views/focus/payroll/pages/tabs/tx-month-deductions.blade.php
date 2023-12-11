<form action="{{ route('biller.payroll.store_deduction') }}" method="post">
    @csrf
    <div class="card-content">
        <div class="card-body">
            <table id="deductionTbl" class="table table-striped table-bordered zero-configuration"
                cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Employee Id</th>
                        <th>Employee Name</th>
                        <th>Basic + Allowances</th>
                        <th>NSSF</th>
                        <th>Taxable Deductions</th>
                        <th>Taxable Pay</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 1;
                    @endphp
                    @foreach ($payroll->payroll_items as $item)
                        <tr>
                            <td>{{ gen4tid('EMP-', $item->employee_id) }}</td>
                            <td>{{ $item->employee_name }}</td>
                            <td class="editable-cell">{{ amountFormat($item->total_basic_allowance) }}</td>
                            <input type="hidden" name="id[]" class="id" value="{{ $item->id }}">
                            <input type="hidden" name="payroll_id"
                                value="{{ $item->payroll_id }}">
                            <input type="hidden" name="nssf[]" value="{{ $item->nssf }}"
                                id="">
                            <td>{{ amountFormat($item->nssf) }}</td>
                            <input type="hidden" name="nhif[]" value="{{ $item->nhif }}"
                                id="">
                            <input type="hidden" name="total_sat_deduction[]" value="{{ $item->nhif + $item->nhif }}"
                                id="">
                            <td><input type="text" class="form-control deduction" value="{{ $item->tx_deductions }}" name="tx_deductions[]"></td>
                            <input type="hidden" name="gross_pay[]"
                                value="{{ $item->gross_pay }}" id="">
                            <td>{{ amountFormat($item->gross_pay) }}</td>
                            @if ($total_tx_deduction > 0)
                                <td>
                                    <a href="#" class="btn btn-danger btn-sm my-1 edit-deduction" data-toggle="modal" data-target="#deductionModal">
                                        <i class="fa fa-pencil" aria-hidden="true"></i> Edit
                                    </a>
                                </td>
                            @endif
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
        <div class="form-group row">
            <div class="col-3">
                <label for="total">Total NSSF</label>
                <input type="text" value="{{ amountFormat($total_nssf) }}"
                    class="form-control" readonly>
                <input type="hidden" name="total_nssf" value="{{ $total_nssf }}"
                    class="form-control" id="total_nssf" readonly>
            </div>
            <div class="col-3">
                <label for="total">Total Deductions</label>
                <input type="text" value="{{ amountFormat($total_tx_deduction) }}"
                    class="form-control" id="deduct_total" readonly>
                <input type="hidden" name="deduction_total" value="{{ $total_tx_deduction }}"
                    class="form-control" id="deduction_total" readonly>
            </div>
        </div>
        <div class="float-right">
            <button type="submit" class="btn btn-primary submit-deduction">Save Deductions</button>
        </div>
    </div>
</form>