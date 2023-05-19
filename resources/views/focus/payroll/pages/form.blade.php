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
                   <i class="text-success fa fa-check float-right tick_allowance" aria-hidden="true"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab3" data-toggle="tab" aria-controls="tab3" href="#tab3" role="tab"
                   aria-selected="false">
                   <span>Tx Monthly Deductions</span>
                   {{-- <i class="text-danger fa fa-times float-right cancel" aria-hidden="true"></i>
                   <i class="text-success fa fa-check float-right tick" aria-hidden="true"></i> --}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab4" data-toggle="tab" aria-controls="tab4" href="#tab4" role="tab"
                   aria-selected="false">
                   <span>Other Benefits and Deductions</span>
                   {{-- <i class="text-danger fa fa-times float-right cancel" aria-hidden="true"></i>
                   <i class="text-success fa fa-check float-right tick" aria-hidden="true"></i> --}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="base-tab5" data-toggle="tab" aria-controls="tab5" href="#tab5" role="tab"
                   aria-selected="false">
                   <span>Summary</span>
                   {{-- <i class="text-danger fa fa-times float-right cancel" aria-hidden="true"></i>
                   <i class="text-success fa fa-check float-right tick" aria-hidden="true"></i> --}}
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
                                        <td><input type="date" class="form-control" value=""></td>
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
                                        <th>Present Days</th>
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
                                            <td>{{ $i++ }}</td>
                                            <td>{{ $employee->employees_salary->employee_name }}</td>
                                            <input type="hidden" id="employee_id-{{$i}}" name="employee_id[]" value="{{ $employee->employees_salary->employee_id}}">
                                            <input type="hidden" class="basic_salary" id="basic_salary-{{$i}}" value="{{ $employee->employees_salary->basic_pay }}">
                                            <td>{{ amountFormat($employee->employees_salary->basic_pay) }}</td>
                                            <td><input type="text" name="absent_days[]" class="form-control absent"  id="absent_days-{{$i}}"></td>
                                            <td><input type="text" name="present_days[]" class="form-control present"  id="present_days-{{$i}}"></td>
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
                <div class="card-content">
                    
                </div>
            </div>
            <div class="tab-pane" id="tab3" role="tabpanel" aria-labelledby="base-tab3">
                <div class="card-content">
    
                </div>
            </div>
            <div class="tab-pane" id="tab4" role="tabpanel" aria-labelledby="base-tab4">
                <div class="card-content">
                    @include('focus.payroll.pages.tabs.otherbenefitsanddeductions')
                </div>
            </div>
            <div class="tab-pane" id="tab5" role="tabpanel" aria-labelledby="base-tab5">
                <div class="card-content">
                    @include('focus.payroll.pages.tabs.summary')
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
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}" }}
    };

    const Index = {
        payroll_items: @json($payroll->payroll_items),
        init() {
            $('#employeeTbl').on('keyup', '.absent, .present, .rate, .rate-month, .total', this.employeeChange);
            // $('#basicSalary').submit(function (e) {
            //     e.preventDefault();
            //     const formData = $(this).serializeArray();
            //     //{{ route('biller.payroll.store_basic')}}
            //     var formDataObject = {};
            //     $.each(formData, function(index, field) {
            //     formDataObject[field.name] = field.value;
            //     });
            //     $.post("{{ route('biller.payroll.store_basic')}}", 
            //     { formDataObject }, function (response) {
            //         //console.log(response);    
            //     }).done(response => {
            //         console.log(response);
            //             // $('#monthTbl tbody').html('');
            //             // response.forEach((v,i) => $('#monthTbl tbody').append(monthRow(v,i)));
            //         });
            //     //console.log(formData);
            // })
            //console.log(this.payroll_items);
            if (this.payroll_items > 0) {
                $('.cancel').addClass('d-none');
                $('.tick').removeClass('d-none');
                // $('.cancel_allowance').addClass('d-none');
                // $('.tick_allowance').removeClass('d-none');
                $('.cancel_allowance').removeClass('d-none');
                $('.tick_allowance').addClass('d-none');
                $('#employeeTbl tbody').html('');
                this.payroll_items.forEach((v,i) => $('#employeeTbl tbody').append(Index.employeeRow(v,i)));
                
            }else{
                $('.tick').addClass('d-none');
                $('.cancel').removeClass('d-none');
                // $('.tick_allowance').addClass('d-none');
                // $('.cancel_allowance').removeClass('d-none');
            }
           
            
            Index.calTotal();
            Index.calTotalNetPay();
        },

        dateFromChange() {

        },
        
        employeeChange() {
        const el = $(this);
        const row = el.parents('tr:first');
        

        const absent = accounting.unformat(row.find('.absent').val());
        const rate = accounting.unformat(row.find('.rate').val());
        const basic_pay = accounting.unformat(row.find('.basic_salary').val());
        const working_days = $('.working_days').val();
        const month_days = $('.month_days').val();
        //console.log(basic_pay);
        //Process 1 
        // const absent_amount = basic_pay/working_days * absent;
        // const total_basic_salary = basic_pay - absent_amount;
        // const payment_days = working_days - absent;
        // const month_rate = payment_days * rate;
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
        //console.log(grandTotal);
        $('#salary_total').val(accounting.unformat(grandTotal));
    },
     calTotalNetPay() {
        let grandTotal = 0;
        $('#summaryTable tbody tr').each(function() {
           
            const net = accounting.unformat($(this).find('.netpay').text());
            grandTotal += net;
        });
       
        $('#salary_total_summary').val(accounting.unformat(grandTotal));
    },
    employeeRow(v,i) {
        return `
                <tr>
                    <td>${i+1}</td>    
                    <td>${v.employee_id}</td>    
                    <td>${accounting.formatNumber(v.basic_pay)}</td>    
                    <td>${v.absent_days}</td>    
                    <td>${v.present_days}</td>    
                    <td>${accounting.formatNumber(v.rate_per_day)}</td>    
                    <td>${accounting.formatNumber(v.basic_pay)}</td> 
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
