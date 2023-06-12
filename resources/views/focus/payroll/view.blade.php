@extends ('core.layouts.app')

@section('title', 'Payroll Management')

@section('page-header')
    <h1>
        Payroll Management
        <small>View</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">View Payroll</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.payroll.partials.payroll-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-2 approve">
                            <a href="#" class="btn btn-info btn-lg my-1" data-toggle="modal" data-target="#statusModal">
                                <i class="fa fa-pencil" aria-hidden="true"></i> Approve
                            </a>
                        </div>
                        {{-- <div class="col-2">
                            <a href="#" class="btn btn-danger btn-lg my-1" data-toggle="modal" data-target="#generateModal">
                                <i class="fa fa-pencil" aria-hidden="true"></i> Generate Payroll
                            </a>
                        </div> --}}
                        <div class="col-2">
                            <button type="button" class="btn btn-success btn-lg my-1 send_mail">
                                <i class="fa fa-paper-plane-o"></i> {{trans('general.send')}}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="base-tab0" data-toggle="tab" aria-controls="tab0" href="#tab0"
                                role="tab" aria-selected="true"><span class="">Payroll </span>

                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1"
                                role="tab" aria-selected="true"><span class="">Basic Salary </span>

                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2" href="#tab2"
                                role="tab" aria-selected="false"><span>Tx Monthly Allowances</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="base-tab3" data-toggle="tab" aria-controls="tab3" href="#tab3"
                                role="tab" aria-selected="false">
                                <span>Tx Monthly Deductions</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="base-tab4" data-toggle="tab" aria-controls="tab4" href="#tab4"
                                role="tab" aria-selected="false">
                                <span>PAYE</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" id="base-tab5" data-toggle="tab" aria-controls="tab5" href="#tab5"
                                role="tab" aria-selected="false">
                                <span>NHIF</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="base-tab6" data-toggle="tab" aria-controls="tab6" href="#tab6"
                                role="tab" aria-selected="false">
                                <span>Other Deductions and Benefits</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="base-tab7" data-toggle="tab" aria-controls="tab7" href="#tab7" role="tab" aria-selected="false">
                                <span>Summary</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="base-tab8" data-toggle="tab" aria-controls="tab8" href="#tab8" role="tab"
                               aria-selected="false">
                               <span>Print Payroll</span>
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content px-1 pt-1">
                        <div class="tab-pane active" id="tab0" role="tabpanel" aria-labelledby="base-tab0">
                            <div class="card-content">
                                <div class="card-body">
                                    <table class="table table-bordered table-sm">
                                        @php
                                            $details = [
                                                'Payroll No' => gen4tid('PYRLL-', $payroll->tid),
                                                'Processing Date' => dateFormat($payroll->processing_date),
                                                'Payroll Month' => dateFormat($payroll->payroll_month),
                                                'Days of Month' => $payroll->total_month_days,
                                                'Working Days' => $payroll->working_days,
                                                'Total Salary' => amountFormat($payroll->salary_total),
                                                'Total Allowances' => amountFormat($payroll->allowance_total),
                                                'Total Deductions' => amountFormat($payroll->deduction_total),
                                                'Total Netpay' => amountFormat($payroll->total_netpay),
                                            ];
                                        @endphp
                                        @foreach ($details as $key => $val)
                                            <tr>
                                                <th width="50%">{{ $key }}</th>
                                                <td>{{ $val }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="card-body">
                                        <table id="employeeTbl"
                                            class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                            width="100%">
                                            <thead>
                                                <tr>
                                                    <th>Employee Id</th>
                                                    <th>Employee Name</th>
                                                    <th>Basic Pay</th>
                                                    <th>Absent Days</th>
                                                    {{-- <th>Present Days</th> --}}
                                                    <th>Rate Per Day</th>
                                                    <th>Total Basic Pay</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $i = 1;
                                                @endphp
                                                @foreach ($payroll->payroll_items as $item)
                                                @php
                                                    $valid_token = token_validator('', 'q'.$item->id, true);
                                                    // dd($valid_token);
                                                @endphp
                                                    <tr>
                                                        <td>{{ gen4tid('EMP-', $item->employee_id) }}</td>
                                                        <td>{{ $item->employee_name }}</td>
                                                        <td>{{ amountFormat($item->basic_pay) }}</td>
                                                        <td>{{ $item->absent_days }}</td>
                                                        {{-- <td>{{ $item->present_days }}</td> --}}
                                                        <td>
                                                            {{ amountFormat($item->rate_per_day) }}
                                                        </td>
                                                        <td>{{ amountFormat($item->basic_pay) }}</td>
                                                        <td><a href={{ route('biller.print_payroll', [$item->id, 12, $valid_token,1]) }} class="btn btn-purple round"
                                                                target="_blank" data-toggle="tooltip"
                                                                data-placement="top" title="Print"><i
                                                                    class="fa fa-print" aria-hidden="true"></i></a> </td>
                                                                    {{-- <td>{{ route('biller.print_payroll', [$item->id, 12, $valid_token,1]) }}</td> --}}
                                                    </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
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
                                                    <td>
                                                        {{ amountFormat($item->house_allowance) }}
                                                    <td>
                                                        {{ amountFormat($item->transport_allowance) }}
                                                    </td>
                                                    <td>
                                                        {{ amountFormat($item->other_allowance) }}
                                                    </td>
                                                    <td>
                                                        {{ amountFormat($item->total_allowance) }}
                                                    </td>

                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab3" role="tabpanel" aria-labelledby="base-tab3">
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
                                                    <td>{{ gen4tid('EMP-', $item->employee_id) }}</td>
                                                    <td>{{ $item->employee_name }}</td>
                                                    <td>{{ amountFormat($item->total_basic_allowance) }}</td>
                                                    <td>{{ amountFormat($item->nssf) }}</td>
                                                    <td>{{ amountFormat($item->nhif) }}</td>
                                                    <td>{{ amountFormat($item->gross_pay) }}</td>
                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab4" role="tabpanel" aria-labelledby="base-tab4">
                            <div class="card-content">
                                <div class="card-body">
                                    <table id="payeTbl" class="table table-striped table-bordered zero-configuration"
                                        cellspacing="0" width="100%">
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
                                                    <td>{{ gen4tid('EMP-', $item->employee_id) }}</td>
                                                    <td>{{ $item->employee_name }}</td>
                                                    <td>{{ amountFormat($item->gross_pay) }}</td>
                                                    <td>{{ amountFormat($item->nssf) }}</td>
                                                    <td>{{ amountFormat($item->nhif) }}</td>
                                                    <td>{{ amountFormat($item->paye) }}</td>

                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab5" role="tabpanel" aria-labelledby="base-tab5">
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
                            </div>
                        </div>
                        <div class="tab-pane" id="tab6" role="tabpanel" aria-labelledby="base-tab6">
                            <div class="card-content">
                                <div class="card-body">
                                    <table id="otherBenefitsTbl"
                                        class="table table-striped table-bordered zero-configuration" cellspacing="0"
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
                                                        <td>{{ amountFormat($item->total_other_allowances) }}
                                                        </td>
                                                        <td>{{ amountFormat($item->total_benefits) }}
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
                                                                        <td>{{ amountFormat($item->loan) }}

                                                                        </td>
                                                                        <td>{{ amountFormat($item->advance) }}

                                                                        </td>
                                                                    </tr>
                                                                </tbody>


                                                            </table>
                                                        </td>

                                                        <td>{{ amountFormat($item->total_other_deduction) }}
                                                        </td>


                                                    </tr>
                                                @endif
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab7" role="tabpanel" aria-labelledby="base-tab7">
                            <div class="card-content">
                                <div class="card-body">
                                    <table id="summaryTable"
                                        class="table table-striped table-responsive table-bordered zero-configuration"
                                        cellspacing="0" width="100%">
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
                                                        
                                                        $salary = $item->basic_pay;
                                                        
                                                        $allowances = $item->total_allowance;
                                                        $deductions = $item->tx_deductions;
                                                        $paye = $item->paye;
                                                        $nssf = $item->nssf;
                                                        $nhif = $item->nhif;
                                                        $total_other_allowances = $item->total_other_allowances;
                                                        $taxable_gross = $item->taxable_gross;
                                                        $benefits = $item->total_benefits;
                                                        $loan_advance = $item->loan + $item->advance;
                                                        $otherdeductions = $item->total_other_deduction + $loan_advance;
                                                        $net_pay = $item->gross_pay - ($item->paye + $item->nhif);
                                                        $net = $net_pay + $total_other_allowances + $benefits - $otherdeductions;
                                                    @endphp
                                                    <tr>
                                                        <td>{{ gen4tid('EMP-', $item->employee_id) }}</td>
                                                        <td>{{ $item->employee_name }}</td>
                                                        <td>{{ amountFormat($salary) }}</td>
                                                        <td>{{ amountFormat($allowances) }}</td>
                                                        <td>{{ amountFormat($nssf) }}</td>
                                                        <td>{{ amountFormat($deductions) }}</td>
                                                        <td>{{ amountFormat($taxable_gross) }}</td>
                                                        <td>{{ amountFormat($paye) }}</td>
                                                        <td>{{ amountFormat($nhif) }}</td>
                                                        <td>{{ amountFormat($total_other_allowances) }}</td>
                                                        <td>{{ amountFormat($benefits) }}</td>
                                                        <td>{{ amountFormat($otherdeductions) }}</td>
                                                        <td class="netpay">{{ amountFormat($item->netpay) }} </td>


                                                    </tr>
                                                @endif
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab8" role="tabpanel" aria-labelledby="base-tab8">
                            <div class="card-content">
                                <div class="card-body">
                                 @include('focus.payroll.partials.generate-payroll')
                                </div>                    
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@include('focus.payroll.partials.approval')
@include('focus.payroll.partials.payroll-generate')
@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    config = {
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    }
    approval();
    $('#statusModal').on('shown.bs.modal', function() {
            $('.datepicker').datepicker({
                container: '#statusModal',
                ...config.date
            }).datepicker('setDate', new Date());
        });
    $('.send_mail').click(function () { 
        var id = @json($payroll->id);
        $.post("{{ route('biller.payroll.send_mail')}}", {id: id},
            function (data, textStatus, jqXHR) {
                
            },
            "dataType"
        );
        
    });
    function approval() {
        $('.send_mail').addClass('d-none');
        var status = @json($payroll->status);
        if(status == 'approved'){
            $('.send_mail').removeClass('d-none');
            $('.approve').addClass('d-none');
        }
    }
        
</script>
<script>
    $(function () {
        setTimeout(function () {
            draw_data()
        }, {{config('master.delay')}});
    });

    function draw_data() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var dataTable = $('#payrollTbl').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: {
                @lang('datatable.strings')
            },
            ajax: {
                url: '{{ route("biller.payroll.get_employee") }}',
                data: {payroll_id: @json(@$payroll->id)},
                type: 'post'
            },
            columns: [
                {data: 'employee_id', name: 'employee_id'},
                {data: 'employee_name', name: 'employee_name'},
                {data: 'basic_pay', name: 'basic_pay'},
                {data: 'absent_days', name: 'absent_days'},
                {data: 'house_allowance', name: 'house_allowance'},
                {data: 'transport_allowance', name: 'transport_allowance'},
                {data: 'other_allowance', name: 'other_allowance'},
                {data: 'gross_pay', name: 'gross_pay'},
                {data: 'nssf', name: 'nssf'},
                {data: 'tx_deductions', name: 'tx_deductions'},
                {data: 'paye', name: 'paye'},
                {data: 'taxable_gross', name: 'taxable_gross'},
                {data: 'total_other_allowances', name: 'total_other_allowances'},
                {data: 'total_benefits', name: 'total_benefits'},
                {data: 'loan', name: 'loan'},
                {data: 'advance', name: 'advance'},
                {data: 'total_other_deductions', name: 'total_other_deductions'},
                {data: 'netpay', name: 'netpay', searchable: false, sortable: false}
            ],
            order: [[0, "asc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print']
        });
        //$('#payrollTbl_wrapper').removeClass('form-inline');

    }
</script>
@endsection
