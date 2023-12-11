<form action="{{ route('biller.payroll.store_paye') }}" method="post">
    @csrf
    <div class="card-content">
        <div class="card-body">
            <table id="payeTbl" class="table table-striped table-bordered zero-configuration"
                cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Employee Id</th>
                        <th>Employee Name</th>
                        <th>Taxable Pay</th>
                        {{-- <th>NSSF</th>
                        <th>NHIF</th> --}}
                        <th>PAYE</th>
                        <th>Gross Pay After Tax</th>
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
                            <td>{{ amountFormat($item->gross_pay) }}</td>
                            {{-- <td>{{ amountFormat($item->nssf) }}</td>
                            <td>{{ amountFormat($item->nhif) }}</td> --}}
                            <td>{{ amountFormat($item->paye) }}</td>
                            <td>{{ amountFormat($item->gross_pay) }}</td>
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                            <input type="hidden" name="payroll_id"
                                value="{{ $item->payroll_id }}">
                            <input type="hidden" name="paye[]" value="{{ $item->paye }}"
                                id="">
                            <input type="hidden" name="taxable_gross[]" value="{{ $item->gross_pay - $item->paye }}"
                                id="">
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
        <div class="form-group">
            <div class="col-3">
                <label for="total">Total PAYE</label>
                <input type="text" value="{{ amountFormat($total_paye) }}"
                    class="form-control" id="" readonly>
                <input type="hidden" name="paye_total" value="{{ $total_paye }}"
                    class="form-control" id="paye_total" readonly>
                <input type="text" name="total_gross" value="{{ $total_gross }}"
                    class="form-control" id="paye_total" readonly>
            </div>
        </div>
        <div class="float-right">
            <button type="submit" class="btn btn-primary submit-paye">Save PAYE</button>
        </div>
    </div>
</form>