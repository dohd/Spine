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
                        <th>Total Allowances</th>
                        <th>Total Monthly Deductions</th>
                        <th>Total PAYE</th>
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
                           
                            $allowances =  $item->total_allowances;
                            $deductions =  $item->total_sat_deduction;
                            $paye =  $item->paye;
                            $benefits =  $item->total_benefits;
                            $otherdeductions =  $item->total_other_deduction;
                            $net = $salary + $allowances + $benefits - $deductions - $otherdeductions;
                            @endphp
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>{{ $item->employee_name }}</td>
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                            <input type="hidden" name="payroll_id" value="{{ $item->payroll_id }}">
                            <td>{{ amountFormat($salary) }}</td>
                            <td>{{amountFormat($allowances) }}</td>
                            <td>{{ amountFormat($deductions) }}</td>
                            <td >{{ amountFormat($paye) }}</td>
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
                <label for="grand_total">Total</label>
                <input type="text" class="form-control" id="total_net"  readonly>
                <input type="hidden" name="total_netpay" class="form-control" id="total_netpay_summary" readonly>
            </div>
        </div>
        <div class="float-right">
            <button type="submit" class="btn btn-primary submit-salary">Save Basic Pay</button>
        </div>
    </form>
    
    
</div>