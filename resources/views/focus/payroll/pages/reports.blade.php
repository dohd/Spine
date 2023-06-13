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
                    <h4 class="content-header-title mb-0">Payroll Reports</h4>

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
                    
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1"
                                role="tab" aria-selected="true"><span class="">NSSF REPORTS </span>

                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2" href="#tab2"
                                role="tab" aria-selected="false"><span>PAYE REPORTS</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="base-tab3" data-toggle="tab" aria-controls="tab3" href="#tab3"
                                role="tab" aria-selected="false">
                                <span>NHIF REPORTS</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="base-tab8" data-toggle="tab" aria-controls="tab8" href="#tab8" role="tab"
                               aria-selected="false">
                               <span>PAYROLL REPORTS</span>
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content px-1 pt-1">
                        <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                            @include('focus.payroll.reports.nssf-report')
                        </div>
                        <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
                            @include('focus.payroll.reports.paye-report')
                        </div>
                        <div class="tab-pane" id="tab3" role="tabpanel" aria-labelledby="base-tab3">
                            @include('focus.payroll.reports.nhif-report')
                        </div>
                        <div class="tab-pane" id="tab8" role="tabpanel" aria-labelledby="base-tab8">
                            @include('focus.payroll.reports.payroll-report')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{-- <script>
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
        
</script> --}}
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

         $('#payrollTable').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: {
                @lang('datatable.strings')
            },
            ajax: {
                url: '{{ route("biller.payroll.get_reports") }}',
                data: {payroll_id: @json(@$payroll)},
                type: 'post',
                dataSrc: ({data}) => {
                    $('.netpay_worth').text('0.00');
                    if (data.length && data[0].aggregate) {
                        const aggr = data[0].aggregate;
                        $('.netpay_worth').text(aggr.netpay_total);
                    }
                    return data;
                },
            },
            columns: [
                {data: 'payroll_id', name: 'payroll_id'},
                {data: 'employee_id', name: 'employee_id'},
                {data: 'employee_name', name: 'employee_name'},
                {data: 'basic_pay', name: 'basic_pay'},
                {data: 'total_allowance', name: 'total_allowance'},
                {data: 'nssf', name: 'nssf'},
                {data: 'tx_deductions', name: 'tx_deductions'},
                {data: 'gross_pay', name: 'gross_pay'},
                {data: 'paye', name: 'paye'},
                {data: 'nhif', name: 'nhif'},
                {data: 'total_other_allowances', name: 'total_other_allowances'},
                {data: 'total_benefits', name: 'total_benefits'},
                {data: 'total_other_deductions', name: 'total_other_deductions'},
                {data: 'netpay', name: 'netpay', searchable: false, sortable: false}
            ],
            order: [[0, "asc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print']
        });

        $('#nssfTbl').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: {
                @lang('datatable.strings')
            },
            ajax: {
                url: '{{ route("biller.payroll.get_reports") }}',
                data: {payroll_id: @json(@$payroll)},
                type: 'post',
                dataSrc: ({data}) => {
                    $('.nssf_worth').text('0.00');
                    if (data.length && data[0].aggregate) {
                        const aggr = data[0].aggregate;
                        $('.nssf_worth').text(aggr.nssf_total);
                    }
                    return data;
                },
            },
            columns: [
                {data: 'payroll_id', name: 'payroll_id'},
                {data: 'employee_id', name: 'employee_id'},
                {data: 'employee_name', name: 'employee_name'},
                {data: 'nssf_no', name: 'nssf_no'},
                {data: 'nssf', name: 'nssf'},
                {data: 'nssf', name: 'nssf', searchable: false, sortable: false}
            ],
            order: [[0, "asc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print']
        });

        $('#payeTbl').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: {
                @lang('datatable.strings')
            },
            ajax: {
                url: '{{ route("biller.payroll.get_reports") }}',
                data: {payroll_id: @json(@$payroll)},
                type: 'post',
                dataSrc: ({data}) => {
                    $('.paye_worth').text('0.00');
                    if (data.length && data[0].aggregate) {
                        const aggr = data[0].aggregate;
                        $('.paye_worth').text(aggr.paye_total);
                    }
                    return data;
                },
            },
            columns: [
                {data: 'payroll_id', name: 'payroll_id'},
                {data: 'employee_id', name: 'employee_id'},
                {data: 'employee_name', name: 'employee_name'},
                {data: 'kra_pin', name: 'kra_pin'},
                {data: 'paye', name: 'paye'},
                {data: 'paye', name: 'paye', searchable: false, sortable: false}
            ],
            order: [[0, "asc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print']
        });
        $('#nhifTbl').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: {
                @lang('datatable.strings')
            },
            ajax: {
                url: '{{ route("biller.payroll.get_reports") }}',
                data: {payroll_id: @json(@$payroll)},
                type: 'post',
                dataSrc: ({data}) => {
                    $('.nhif_worth').text('0.00');
                    if (data.length && data[0].aggregate) {
                        const aggr = data[0].aggregate;
                        $('.nhif_worth').text(aggr.nhif_total);
                    }
                    return data;
                },
            },
            columns: [
                {data: 'payroll_id', name: 'payroll_id'},
                {data: 'employee_id', name: 'employee_id'},
                {data: 'employee_name', name: 'employee_name'},
                {data: 'nhif_no', name: 'nhif_no'},
                {data: 'nhif', name: 'nhif'},
                {data: 'nhif', name: 'nhif', searchable: false, sortable: false}
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
