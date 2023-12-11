<form action="{{ route('biller.payroll.store_nhif') }}" method="post">
    @csrf
    <div class="card-content">
        <div class="card-body">
            <table id="nhifTbl" class="table table-striped table-bordered zero-configuration"
                cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Employee Id</th>
                        <th>Employee Name</th>
                        <th>Taxable Pay</th>
                        <th>NHIF</th>
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
                            <td>{{ amountFormat($item->taxable_gross) }}</td>
                            <td>{{ amountFormat($item->nhif) }}</td>   
                            <input type="hidden" name="payroll_id"
                                value="{{ $item->payroll_id }}">     
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
        <div class="form-group">
            <div class="col-3">
                <label for="total">Total NHIF</label>
                <input type="text" value="{{ amountFormat($total_nhif) }}"
                    class="form-control" id="" readonly>
                <input type="hidden" name="total_nhif" value="{{ $total_nhif }}"
                    class="form-control" id="total_nhif" readonly>
            </div>
        </div>
        <div class="float-right">
            <button type="submit" class="btn btn-primary submit-nhif">Save NHIF</button>
        </div>
    </div>
</form>