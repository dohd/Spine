@extends ('core.layouts.app')

@section ('title', 'Customer Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Customer Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.customers.partials.customers-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="content-detached content-right">
        <div class="content-body">
            <section class="row all-contacts">
                <div class="col-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="btn-group float-right">
                                    <a href="{{ route('biller.customers.edit', $customer) }}" class="btn btn-blue btn-outline-accent-5 btn-sm">
                                        <i class="fa fa-pencil"></i> {{trans('buttons.general.crud.edit')}}
                                    </a>&nbsp;
                                    <button type="button" class="btn btn-danger btn-outline-accent-5 btn-sm" id="delCustomer">
                                        {{Form::open(['route' => ['biller.customers.destroy', $customer], 'method' => 'DELETE'])}}{{Form::close()}}
                                        <i class="fa fa-trash"></i> {{trans('buttons.general.crud.delete')}}
                                    </button>
                                </div>
                                
                                <div class="card-body">
                                    <ul class="nav nav-tabs nav-top-border no-hover-bg " role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">Customer Info</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">Transactions</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link " id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">Invoices</a>
                                        </li> 
                                        <li class="nav-item">
                                            <a class="nav-link " id="active-tab4" data-toggle="tab" href="#active4" aria-controls="active4" role="tab">Statement on Invoice</a>
                                        </li> 
                                    </ul>
                                    <div class="tab-content px-1 pt-1">
                                        <!-- Customer Info -->
                                        <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
                                            <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                                                @php
                                                    $labels = [
                                                        'Name', 'Phone', 'Email', 'Address', 'Company', 'City', 'Region', 'Country', 'PostBox',
                                                        'Tax ID' => 'taxid',  
                                                    ];
                                                @endphp
                                                <tbody> 
                                                    @foreach ($labels as $key => $val)
                                                        <tr>
                                                            <th>{{ is_numeric($key) ? $val : $key }}</th>
                                                            <td>{{ $customer[strtolower($val)] }}</td>
                                                        </tr>
                                                    @endforeach      
                                                </tbody>
                                            </table>
                                        </div>
                                                    
                                        <!-- Transactions -->
                                        <div class="tab-pane" id="active2" aria-labelledby="link-tab2" role="tabpanel">
                                            <div class="row">
                                                <div class="col-2">Search Date Between</div>
                                                <div class="col-2">
                                                    <input type="text" name="start_date" id="start_date" class="form-control form-control-sm datepicker">
                                                </div>
                                                <div class="col-2">
                                                    <input type="text" name="end_date" id="end_date" class="form-control form-control-sm datepicker">
                                                </div>
                                                <div class="col-2">
                                                    <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm">
                                                </div>
                                            </div>
                                            <table id="transTbl" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>                                            
                                                        @foreach (['Date', 'Type', 'Note', 'Invoice Amount', 'Amount Paid', 'Account Balance'] as $val)
                                                            <th>{{ $val }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                            <div class="mt-2 aging">
                                                <h5>Aging Report</h5>
                                                <table class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>                                                    
                                                            @foreach ([30, 60, 90, 120] as $val)
                                                                <th>{{ $val }} Days</th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            @for ($i = 0; $i < count($aging_cluster); $i++) 
                                                                <td>{{ numberFormat($aging_cluster[$i]) }}</td>
                                                            @endfor
                                                        </tr>
                                                    </tbody>                     
                                                </table>  
                                            </div>
                                        </div>

                                        <!-- Invoices -->
                                        <div class="tab-pane" id="active3" aria-labelledby="link-tab3" role="tabpanel">
                                            <table id="invoiceTbl" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>                                                    
                                                        @foreach (['Date', 'Status', 'Note', 'Amount', 'Paid'] as $val)
                                                            <th>{{ $val }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody></tbody> 
                                            </table>                                            
                                        </div>

                                        <!-- Statement on Invoice  -->
                                        <div class="tab-pane" id="active4" aria-labelledby="link-tab4" role="tabpanel">
                                            <div class="row mb-1">
                                                <div class="col-2">Search Date Between</div>
                                                <div class="col-2">
                                                    <input type="text" name="start_date" id="start_date" class="form-control form-control-sm datepicker">
                                                </div>
                                                <div class="col-2">
                                                    <input type="text" name="end_date" id="end_date" class="form-control form-control-sm datepicker">
                                                </div>
                                                <div class="col-2">
                                                    <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm">
                                                </div>
                                            </div>
                                            <table id="stmentTbl" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>                                            
                                                        @foreach (['Date', 'Type', 'Note', 'Invoice Amount', 'Amount Paid', 'Invoice Balance'] as $val)
                                                            <th>{{ $val }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>  
                                            </table>   
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    @include('focus.customers.partials.sidebar')
</div>
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });

    // datepicker
    $('.datepicker')
    .datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    .datepicker('setDate', new Date());

    // datefilter
    $('#search').click(() => {
        const start_date = $('#start_date').val();
        const end_date = $('#end_date').val();
        // $('#transactionsTbl').DataTable().destroy();
        // draw_data(start_date, end_date);
    });    

    // delete customer
    $('#delCustomer').click(function() {
        $(this).children('form').submit();
    });

    // insert aging table after statement table
    $('#stmentTbl').after($('.aging').clone());

    // draw data
    setTimeout(() => {
        drawCustomerData();
        drawTransactionData();
        drawInvoiceData();
        drawStatementData();
    }, "{{ config('master.delay') }}");
    const dTableConfig = {
        processing: true,
        serverSide: true,
        responsive: true,
        stateSave: true,
        language: {@lang('datatable.strings')},
        ajax: {
            url: '{{ route("biller.customers.get") }}',
            type: 'post',
            data: {customer_id: "{{ $customer->id }}" }
        },
        order: [[0, "desc"]],
        searchDelay: 500,
        dom: 'frt',
    };

    function drawCustomerData() {
        const dataTable = $('#customerTbl').DataTable({
            ...dTableConfig,
            columns: ['name'].map(v => ({data: v, name: v})),
        });
    }
    function drawTransactionData() {
        const config = JSON.parse(JSON.stringify(dTableConfig));
        config.ajax.data.is_transaction = 1;
        config.order[0][1] = 'asc';
        const cols = ['date', 'type', 'note', 'invoice_amount', 'amount_paid', 'account_balance'];
        const dataTable = $('#transTbl').DataTable({
            ...config,
            columns: cols.map(v => ({data: v, name: v})),
        });
    }
    function drawInvoiceData() {
        const config = JSON.parse(JSON.stringify(dTableConfig));
        config.ajax.data.is_invoice = 1;
        // config.order[0][1] = 'asc';
        const cols = ['date', 'status', 'note', 'amount', 'paid'];
        const dataTable = $('#invoiceTbl').DataTable({
            ...config,
            columns: cols.map(v => ({data: v, name: v})),
        });
    }
    function drawStatementData() {
        const config = JSON.parse(JSON.stringify(dTableConfig));
        config.ajax.data.is_statement = 1;
        config.order[0][1] = 'asc';
        const cols = ['date', 'type', 'note', 'invoice_amount', 'amount_paid', 'invoice_balance'];
        const dataTable = $('#stmentTbl').DataTable({
            ...config,
            columns: cols.map(v => ({data: v, name: v})),
        });
    }
</script>
@endsection