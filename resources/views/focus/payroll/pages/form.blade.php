<div class="card-content">
    
    <div class="card-body">
            
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1" role="tab"
                   aria-selected="true"><span class="">Basic Salary </span> 
                   <i class="text-danger fa fa-times float-right cancel" aria-hidden="true"></i>
                   <i class="text-success fa fa-check float-right tick" aria-hidden="true"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2" href="#tab2" role="tab"
                   aria-selected="false"><span>Tx Monthly Allowances</span>
                   <i class="text-danger fa fa-times float-right cancel_allowance" aria-hidden="true"></i>
                   <i class="text-success fa fa-check float-right d-none tick_allowance" aria-hidden="true"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab3" data-toggle="tab" aria-controls="tab3" href="#tab3" role="tab"
                   aria-selected="false">
                   <span>Tx Monthly Deductions</span>
                   <i class="text-danger fa fa-times float-right cancel_deduction" aria-hidden="true"></i>
                   <i class="text-success fa fa-check float-right d-none tick_deduction" aria-hidden="true"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab4" data-toggle="tab" aria-controls="tab4" href="#tab4" role="tab"
                   aria-selected="false">
                   <span>PAYE</span>
                   <i class="text-danger fa fa-times float-right cancel_paye" aria-hidden="true"></i>
                   <i class="text-success fa fa-check float-right d-none tick_paye" aria-hidden="true"></i>
                </a>
            </li>
        </ul>
        <div class="tab-content px-1 pt-1">
            <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
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
                                        <td><input type="text" name="processing_date" class="form-control datepicker" value=""></td>
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
                                            <td>{{ gen4tid('EMP-', $employee->employees_salary->employee_id) }}</td>
                                            <td>{{ $employee->employees_salary->employee_name }}</td>
                                            <input type="hidden" id="employee_id-{{$i}}" name="employee_id[]" value="{{ $employee->employees_salary->employee_id}}">
                                            <input type="hidden" class="basic_salary" id="basic_salary-{{$i}}" value="{{ $employee->employees_salary->basic_pay }}">
                                            <td>{{ amountFormat($employee->employees_salary->basic_pay) }}</td>
                                            <td><input type="text" name="absent_days[]" class="form-control absent"  id="absent_days-{{$i}}"></td>
                                            <input type="hidden" name="present_days[]" class="form-control present"  id="present_days-{{$i}}">
                                            {{-- <td><input type="text" name="present_days[]" class="form-control present"  id="present_days-{{$i}}"></td> --}}
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
            </div>
            <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
                <form action="{{ route('biller.payroll.store_allowance')}}" method="post">
                    @csrf
                    <div class="card-content">
                        <div class="card-body">
                            <table id="allowanceTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
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
                                            <td>{{gen4tid('EMP-', $item->employee_id)}}</td>
                                            <td>{{ $item->employee_name }}</td>
                                            <td>{{ $item->absent_days }}</td>
                                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                                            <input type="hidden" name="payroll_id" value="{{ $item->payroll_id }}">
                                            <input type="hidden" class="basic" value="{{ $item->basic_pay }}">
                                            <input type="hidden" name="total_basic_allowance[]" class="total_basic_allowance">
                                            <input type="hidden" class="form-control absent_day" value="{{ $item->absent_days }}"  id="absent_day-{{$i}}">
                                            <td>
                                                <input type="text" class="form-control house"  id="house-{{$i}}">
                                                <input type="text" name="house_allowance[]" class="form-control house_allowance"  id="house_allowance-{{$i}}" readonly>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control transport"  id="transport-{{$i}}">
                                                <input type="text" name="transport_allowance[]" class="form-control transport_allowance"  id="transport_allowance-{{$i}}" readonly>
                                            </td>
                                            <td>
                                                <input type="text" name="other_allowance[]" class="form-control other_allowance"  id="other_allowance-{{$i}}">
                                            </td>
                                            <td>
                                                <input type="text" name="total_allowance[]" class="form-control total_allowance"  id="total_allowance-{{$i}}" readonly>
                                            </td>

                                        </tr>
                                    @endforeach
                                   
                                </tbody>
                            </table>
                        </div>
                        <div class="form-group">
                            <div class="col-3">
                                <label for="total">Total Allowances</label>
                                <input type="text" name="allowance_total" class="form-control" id="allowance_total" readonly>
                            </div>
                        </div>
                        <div class="float-right">
                            <button type="submit" class="btn btn-primary">Save Allowances</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="tab-pane" id="tab3" role="tabpanel" aria-labelledby="base-tab3">
                <div class="card-content">

                    <form action="{{ route('biller.payroll.store_deduction')}}" method="post">
                        @csrf
                        <div class="card-content">
                            <div class="card-body">
                                <table id="deductionTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Employee Id</th>
                                            <th>Employee Name</th>
                                            <th>Basic + Allowances</th>
                                            <th>NSSF</th>
                                            <th>NHIF</th>
                                            <th>Gross Pay</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($payroll->payroll_items as $item)
                                        <tr>
                                            <td>{{gen4tid('EMP-', $item->employee_id)}}</td>
                                            <td>{{ $item->employee_name}}</td>
                                            <td>{{ amountFormat($item->total_basic_allowance)}}</td>
                                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                                            <input type="hidden" name="payroll_id" value="{{ $item->payroll_id }}">
                                            <input type="hidden" name="nssf[]" value="{{$item->nssf}}" id="">
                                            <td>{{ amountFormat($item->nssf) }}</td>
                                            <input type="hidden" name="nhif[]" value="{{$item->nhif}}" id="">
                                            <td>{{ amountFormat($item->nhif) }}</td>
                                            <input type="hidden" name="gross_pay[]" value="{{$item->gross_pay}}" id="">
                                            <td>{{ amountFormat($item->gross_pay) }}</td>
                                        </tr>

                                        @endforeach
                                       
                                    </tbody>
                                </table>
                            </div>
                            <div class="form-group">
                                <div class="col-3">
                                    <label for="total">Total Deductions</label>
                                    <input type="text" value="{{amountFormat($total_gross)}}" class="form-control" readonly>
                                    <input type="hidden" name="deduction_total" value="{{$total_gross}}" class="form-control" id="deduction_total" readonly>
                                </div>
                            </div>
                            <div class="float-right">
                                <button type="submit" class="btn btn-primary">Save Deductions</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="tab-pane" id="tab4" role="tabpanel" aria-labelledby="base-tab4">
                <div class="card-content">

                    <form action="{{ route('biller.payroll.store_paye')}}" method="post">
                        @csrf
                        <div class="card-content">
                            <div class="card-body">
                                <table id="payeTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Employee Id</th>
                                            <th>Employee Name</th>
                                            <th>Gross Pay</th>
                                            <th>NSSF</th>
                                            <th>NHIF</th>
                                            <th>PAYE</th>
                                        
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($payroll->payroll_items as $item)
                                            <tr>
                                                <td>{{gen4tid('EMP-', $item->employee_id)}}</td>
                                                <td>{{ $item->employee_name}}</td>
                                                <td>{{ amountFormat($item->gross_pay)}}</td>
                                                <td>{{ amountFormat($item->nssf) }}</td>
                                                <td>{{ amountFormat($item->nhif) }}</td>
                                                <td>{{ amountFormat($item->paye) }}</td>
                                                <input type="hidden" name="id[]" value="{{ $item->id }}">
                                                <input type="hidden" name="payroll_id" value="{{ $item->payroll_id }}">
                                                <input type="hidden" name="paye[]" value="{{$item->paye}}" id="">
                                            </tr>
                                        @endforeach
                                       
                                    </tbody>
                                </table>
                            </div>
                            <div class="form-group">
                                <div class="col-3">
                                    <label for="total">Total PAYE</label>
                                    <input type="text" value="{{amountFormat($total_paye)}}" class="form-control" id="" readonly>
                                    <input type="hidden" name="paye_total" value="{{$total_paye}}" class="form-control" id="paye_total" readonly>
                                </div>
                            </div>
                            <div class="float-right">
                                <button type="submit" class="btn btn-primary">Save PAYE</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@section("after-scripts")
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}" }},
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
    };

    const Index = {
        payroll_items: @json($payroll->payroll_items),
        salary_total: @json($payroll->salary_total),
        allowance_total: @json($payroll->allowance_total),
        deduction_total: @json($payroll->deduction_total),
        paye_total: @json($payroll->paye_total),
        init() {
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('.editable-cell').hover(this.hoverChange);
            $('#saveButton').click(this.valueChange);
            $('#employeeTbl').on('keyup', '.absent, .present, .rate, .rate-month, .total', this.employeeChange);
            $('#allowanceTbl').on('keyup', '.house, .house_allowance, .transport, .transport_allowance, .other, .other_allowance', this.allowanceChange);
            
            if (this.payroll_items && this.payroll_items.length) {
                $('.cancel').addClass('d-none');
                $('.tick').removeClass('d-none');
                $('.cancel_allowance').removeClass('d-none');
                $('.tick_allowance').addClass('d-none');
                $('#employeeTbl tbody').html('');
                this.payroll_items.forEach((v,i) => $('#employeeTbl tbody').append(Index.employeeRow(v,i)));
                $('#salary_total').val(accounting.formatNumber(this.salary_total));
                if (this.allowance_total && this.allowance_total.length) {
                    $('#allowanceTbl tbody').html('');
                    this.payroll_items.forEach((v,i) => $('#allowanceTbl tbody').append(Index.allowanceRow(v,i)));
                    $('#allowance_total').val(accounting.formatNumber(this.allowance_total));
                    $('.tick_allowance').removeClass('d-none');
                    $('.cancel_allowance').addClass('d-none');
                    if (this.deduction_total && this.deduction_total.length) {
                        $('.cancel_deduction').addClass('d-none');
                        $('.tick_deduction').removeClass('d-none');
                        if (this.paye_total && this.paye_total.length) {
                            $('.cancel_paye').addClass('d-none');
                            $('.tick_paye').removeClass('d-none');
                        }
                        
                    }
                }
            }else{
                $('.tick').addClass('d-none');
                $('.cancel').removeClass('d-none');
                // $('.tick_allowance').addClass('d-none');
                // $('.cancel_allowance').removeClass('d-none');
            }
            //Index.calTotal();
        },
        hoverChange() {
            // Show the modal when hovering over the editable cell
            $('#hover-modal').modal('show');

            // Get the current value of the editable cell
            var currentValue = $(this).text();

            // Set the input field value to the current value
            $('#editInput').val(currentValue);
        },
        valueChange() {
            // Get the updated value from the input field
            var updatedValue = $('#editInput').val();

            // Update the content of the editable cell with the updated value
            $('.editable-cell:hover').text(updatedValue);

            // Hide the modal
            $('#hover-modal').modal('hide');
        },
        allowanceChange() {
            const el = $(this);
            const row = el.parents('tr:first');
            
            const absent_day = accounting.unformat(row.find('.absent_day').val());
            const house = accounting.unformat(row.find('.house').val());
            const basic = accounting.unformat(row.find('.basic').val());
            const house_allowance = accounting.unformat(row.find('.house_allowance').val());
            const transport = accounting.unformat(row.find('.transport').val());
            const transport_allowance = accounting.unformat(row.find('.transport_allowance').val());
            const other_allowance = accounting.unformat(row.find('.other_allowance').val());
            const month_days = $('.month_days').val();
            const working_days = $('.working_days').val();
            
            const absent_allowance = house/month_days * absent_day;
            const cal_house_allowance = house - absent_allowance;
            const ab_allowance = transport/month_days * absent_day;
            const cal_transport_allowance = transport - ab_allowance;
            

            const cal_total_allowance = cal_house_allowance + cal_transport_allowance + other_allowance;
            const total_basic_allowance =  cal_total_allowance + basic;
            row.find('.house_allowance').val(accounting.unformat(cal_house_allowance));
            row.find('.transport_allowance').val(accounting.unformat(cal_transport_allowance));
            row.find('.total_allowance').val(accounting.unformat(cal_total_allowance));
            row.find('.total_basic_allowance').val(accounting.unformat(total_basic_allowance));

            Index.calallowanceTotal();
        },
        
        employeeChange() {
            const el = $(this);
            const row = el.parents('tr:first');
            

            const absent = accounting.unformat(row.find('.absent').val());
            const rate = accounting.unformat(row.find('.rate').val());
            const basic_pay = accounting.unformat(row.find('.basic_salary').val());
            const working_days = $('.working_days').val();
            const month_days = $('.month_days').val();
            const absent_amount_deduct = basic_pay/month_days * absent;
            const total_basic_salary = basic_pay - absent_amount_deduct;
            const days_to_be_paid = month_days - absent;
            const rate_per_day = basic_pay/month_days;
            const month_rate = days_to_be_paid * rate_per_day;

            row.find('.rate').val(accounting.unformat(rate_per_day));
            row.find('.rate-month').val(accounting.unformat(month_rate));
            row.find('.total').val(accounting.unformat(month_rate));
            Index.calTotal();

        },
        calTotal() {
            let grandTotal = 0;
            $('#employeeTbl tbody tr').each(function() {
                if (!$(this).find('.absent').val()) return;
                const absent = accounting.unformat($(this).find('.absent').val());
                const basic_pay = accounting.unformat($(this).find('.basic_salary').val());
                const working_days = $('.working_days').val();

                const absent_amount = basic_pay/working_days * absent;
                const total_basic_salary = basic_pay - absent_amount;
                grandTotal += total_basic_salary;
            });
        
            $('#salary_total').val(accounting.unformat(grandTotal));
        },
        calallowanceTotal() {
            let grandTotal = 0;
            $('#allowanceTbl tbody tr').each(function() {
                if (!$(this).find('.absent_day').val()) return;
                const absent_day = accounting.unformat($(this).find('.absent_day').val());
                const house = accounting.unformat($(this).find('.house').val());
                const house_allowance = accounting.unformat($(this).find('.house_allowance').val());
                const transport = accounting.unformat($(this).find('.transport').val());
                const transport_allowance = accounting.unformat($(this).find('.transport_allowance').val());
                const other_allowance = accounting.unformat($(this).find('.other_allowance').val());
                const month_days = $('.month_days').val();
                const working_days = $('.working_days').val();
                
                const absent_allowance = house/month_days * absent_day;
                const cal_house_allowance = house - absent_allowance;
                const ab_allowance = transport/month_days * absent_day;
                const cal_transport_allowance = transport - ab_allowance;

                const cal_total_allowance = cal_house_allowance + cal_transport_allowance + other_allowance;
                grandTotal += cal_total_allowance;
            });
        
            $('#allowance_total').val(accounting.unformat(grandTotal));
        },
        employeeRow(v,i) {
            return `
                    <tr>
                        <td>${i+1}</td>    
                        <td>${v.employee_name}</td>    
                        <td class="editable-cell">${accounting.formatNumber(v.basic_pay)}</td>    
                        <td>${v.absent_days}</td>      
                        <td>${accounting.formatNumber(v.rate_per_day)}</td>    
                        <td>${accounting.formatNumber(v.basic_pay)}</td> 
                        <input type="hidden" name="absent_days[]" value="${v.absent_days}" class="form-control absent"  id="absent_days-${i}"> 
                        <input type="hidden" name="present_days[]" value="${v.present_days}" class="form-control present"  id="present_days-${i}"> 
                        <input type="hidden" name="rate_per_day[]" value="${v.rate_per_day} class="form-control rate"  id="rate-days-${i}">
                        <input type="hidden" name="rate_per_month[]" value="${v.basic_pay} class="form-control rate-month"  id="rate-month-${i}"> 
                    </tr>
                `;
        },
        allowanceRow(v,i) {
            return `
                    <tr>
                        <td>${i+1}</td>    
                        <td>${v.employee_name}</td>   
                        <td>${v.absent_days}</td>  
                        <td><input type="text" name="house_allowance[]" value="${accounting.formatNumber(v.house_allowance)}" class="form-control house_allowance"  id="house_allowance-${i}" readonly></td>      
                        <td><input type="text" name="transport_allowance[]" value="${accounting.formatNumber(v.transport_allowance)}" class="form-control transport_allowance"  id="transport_allowance-${i}" readonly></td>    
                        <td><input type="text" name="other_allowance[]" value="${accounting.formatNumber(v.other_allowance)}" class="form-control other_allowance"  id="other_allowance-${i}" readonly></td>    
                        <td><input type="text" name="total_allowance[]" value="${accounting.formatNumber(v.total_all)}" class="form-control total_allowance"  id="total_allowance-${i}" readonly></td> 
                        <input type="hidden" name="absent_days[]" value="${v.absent_days}" class="form-control absent"  id="absent_days-${i}"> 
                        <input type="hidden" name="present_days[]" value="${v.present_days}" class="form-control present"  id="present_days-${i}"> 
                        <input type="hidden" name="rate_per_day[]" value="${v.rate_per_day} class="form-control rate"  id="rate-days-${i}">
                        <input type="hidden" name="rate_per_month[]" value="${v.basic_pay} class="form-control rate-month"  id="rate-month-${i}"> 
                    </tr>
                `;
        },
        
    };
    $(() => Index.init());

</script>
@endsection
@include('focus.payroll.partials.hover-modal')