@extends ('core.layouts.app')

@section ('title', 'Payroll Management')

@section('page-header')
    <h1>
        Payroll Management
        <small>Create</small>
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
                    <a href="#" class="btn btn-info btn-sm mr-1" data-toggle="modal" data-target="#statusModal">
                        <i class="fa fa-pencil" aria-hidden="true"></i> Approve
                    </a>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="base-tab0" data-toggle="tab" aria-controls="tab0" href="#tab0" role="tab"
                               aria-selected="true"><span class="">Payroll </span> 
                               
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1" role="tab"
                               aria-selected="true"><span class="">Basic Salary </span> 
                               
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2" href="#tab2" role="tab"
                               aria-selected="false"><span>Tx Monthly Allowances</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="base-tab3" data-toggle="tab" aria-controls="tab3" href="#tab3" role="tab"
                               aria-selected="false">
                               <span>Tx Monthly Deductions</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="base-tab4" data-toggle="tab" aria-controls="tab4" href="#tab4" role="tab"
                               aria-selected="false">
                               <span>PAYE</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" id="base-tab5" data-toggle="tab" aria-controls="tab5" href="#tab5" role="tab"
                               aria-selected="false">
                               <span>Other Benefits and Deductions</span>
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
                                                @foreach ($payroll->payroll_items as $item)
                                                    
                                                    <tr>
                                                        <td>{{ gen4tid('EMP-', $item->employee_id) }}</td>
                                                        <td>{{ $item->employee_name }}</td>
                                                        <td>{{ amountFormat($item->basic_pay) }}</td>
                                                        <td>{{ $item->absent_days}}</td>
                                                        <td>{{ $item->present_days}}</td>
                                                        <td>
                                                            {{ amountFormat($item->rate_per_day)}}
                                                        </td>
                                                        <td>{{ amountFormat($item->basic_pay)}}</td>
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
                                                    <td>
                                                        {{ amountFormat($item->house_allowance)}}
                                                    <td>
                                                        {{ amountFormat($item->transport_allowance)}}
                                                    </td>
                                                    <td>
                                                        {{ amountFormat($item->other_allowance)}}
                                                    </td>
                                                    <td>
                                                        {{ amountFormat($item->total_allowance)}}
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
                                                    
                                                </tr>
                                            @endforeach
                                           
                                        </tbody>
                                    </table>
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
@section('after-scripts')
<script>
    config = {
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    }

    $('#statusModal').on('shown.bs.modal', function() {
        $('.datepicker').datepicker({
            container: '#statusModal',
            ...config.date
        }).datepicker('setDate', new Date());
    });


</script>
@endsection