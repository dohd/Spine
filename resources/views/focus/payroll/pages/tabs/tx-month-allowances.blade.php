<form action="{{ route('biller.payroll.store_allowance') }}" method="post">
    @csrf
    <div class="card-content">
        <div class="card-body">
            <table id="allowanceTbl" class="table table-striped table-bordered zero-configuration"
                cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Employee Id</th>
                        <th>Employee Name</th>
                        <th>Absent Days</th>
                        <th>Housing Allowance</th>
                        <th>Transport</th>
                        <th>Other Allowances</th>
                        <th>Total Allowance</th>
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
                            <td>{{ $item->absent_days }}</td>
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                            <input type="hidden" name="payroll_id" value="{{ $item->payroll_id }}">
                            <input type="hidden" class="basic" value="{{ $item->basic_pay }}">
                            <input type="hidden" name="total_basic_allowance[]"
                                class="total_basic_allowance">
                            <input type="hidden" class="form-control absent_day"
                                value="{{ $item->absent_days }}"
                                id="absent_day-{{ $i }}">
                            <td>
                                <input type="text" class="form-control house"
                                    id="house-{{ $i }}">
                                <input type="text" name="house_allowance[]"
                                    class="form-control house_allowance"
                                    id="house_allowance-{{ $i }}" readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control transport"
                                    id="transport-{{ $i }}">
                                <input type="text" name="transport_allowance[]"
                                    class="form-control transport_allowance"
                                    id="transport_allowance-{{ $i }}" readonly>
                            </td>
                            <td>
                                <input type="text" name="other_allowance[]"
                                    class="form-control other_allowance"
                                    id="other_allowance-{{ $i }}">
                            </td>
                            <td>
                                <input type="text" name="total_allowance[]"
                                    class="form-control total_allowance"
                                    id="total_allowance-{{ $i }}" readonly>
                            </td>

                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
        <div class="form-group">
            <div class="col-3">
                <label for="total">Total Allowances</label>
                <input type="text" name="allowance_total" class="form-control"
                    id="allowance_total" readonly>
            </div>
        </div>
        <div class="float-right">
            <button type="submit" class="btn btn-primary submit-allowances">Save Allowances</button>
        </div>
    </div>
</form>