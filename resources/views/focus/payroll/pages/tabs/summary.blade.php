<div class="card-content" >
    <form id="basicSalary" action="{{ route('biller.payroll.store_summary')}}" method="post">
        @csrf
        <input type="hidden" name="payroll_id" value="{{ $payroll->id }}" id="">
        <div class="card-body">
            <table id="summaryTable" class="table table-striped table-responsive table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Employee Id</th>
                        <th>Employee Name</th>
                        <th>Total Basic Salary</th>
                        <th>Total Tx Allowances</th>
                        <th>NSSF</th>
                        <th>Total Tx Monthly Deductions</th>
                        <th>Taxable Gross</th>
                        <th>Total PAYE</th>
                        <th>NHIF</th>
                        <th>Other Allowances</th>
                        <th>Other Benefits</th>
                        <th>Other Deductions</th>
                        <th>Net Pay</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 1;
                        
                    @endphp
                    @foreach ($payroll->payroll_items as $item)
                        @if ($item)
                      

                        @php
                       
                            $salary =  $item->basic_pay;
                           
                            $allowances =  $item->total_allowance;
                            $deductions =  $item->tx_deductions;
                            $paye =  $item->paye;
                            $nssf =  $item->nssf;
                            $nhif =  $item->nhif;
                            $total_other_allowances =  $item->total_other_allowances;
                            $taxable_gross = $item->gross_pay;
                            $benefits =  $item->total_benefits;
                            $loan_advance = $item->loan + $item->advance;
                            $otherdeductions =  $item->total_other_deduction + $loan_advance;
                            $net_pay = $taxable_gross - ($item->paye + $item->nhif);
                            $net = $net_pay + $total_other_allowances + $benefits - ($otherdeductions);
                            @endphp
                        <tr>
                            <td>{{ gen4tid('EMP-', $item->employee_id) }}</td>
                            <td>{{ $item->employee_name }}</td>
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                            <input type="hidden" name="payroll_id" value="{{ $item->payroll_id }}">
                            <td>{{ amountFormat($salary) }}</td>
                            <td>{{amountFormat($allowances) }}</td>
                            <td>{{amountFormat($nssf) }}</td>
                            <td>{{ amountFormat($deductions) }}</td>
                            <td>{{ amountFormat($taxable_gross) }}</td>
                            <td >{{ amountFormat($paye) }}</td>
                            <td >{{ amountFormat($nhif) }}</td>
                            <td >{{ amountFormat($total_other_allowances) }}</td>
                            <td>{{ amountFormat($benefits) }}</td>
                            <td>{{ amountFormat($otherdeductions) }}</td>
                            <input type="hidden" name="netpay[]" value="{{ $net }}"
                            id="">
                            <td class="netpay">{{ amountFormat($net) }} </td>   
                            
                           
                        </tr>
                        
                        @endif
                    @endforeach
                   
                </tbody>
            </table>
        </div>
        <div class="form-group">
            <div class="col-3">
                <label for="grand_total">Total Net Pay</label>
                <input type="text" class="form-control" id="total_net"  readonly>
                <input type="hidden" name="total_netpay" class="form-control" id="total_netpay_summary" readonly>
            </div>
        </div>
        <div class="float-right">
            <button type="submit" class="btn btn-primary submit-netpay">Save Net Pay</button>
        </div>
    </form>
    
    
</div>