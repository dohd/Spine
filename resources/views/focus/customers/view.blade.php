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
                                        {{ Form::open(['route' => ['biller.customers.destroy', $customer], 'method' => 'DELETE']) }}
                                        {{ Form::close() }}
                                        <i class="fa fa-trash"></i> {{ trans('buttons.general.crud.delete') }}
                                    </button>
                                </div>
                                
                                <div class="card-body">
                                    @include('focus.customers.partials.tabs')
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

    // delete customer
    $('#delCustomer').click(function() {
        const form = $(this).children('form');
        swal({
            title: 'Are You  Sure?',
            icon: "warning",
            buttons: true,
            dangerMode: true,
            showCancelButton: true,
        }, () => form.submit());
    });

    // datepicker
    $('.datepicker')
    .datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    .datepicker('setDate', new Date());

    // date filter
    $('.search').click(function() {
        const start_date = $(this).parents('.row').find('.start_date');
        const end_date = $(this).parents('.row').find('.end_date');
        const id = $(this).attr('id');
        if (id == 'search2') {
            $('#transTbl').DataTable().destroy();
            drawTransactionData(start_date.eq(0).val(), end_date.eq(0).val());
        } else if (id == 'search4') {
            $('#stmentTbl').DataTable().destroy();
            drawStatementData(start_date.eq(1).val(), end_date.eq(1).val());
        }
    });
    $('.refresh').click(function() {
        const id = $(this).attr('id');
        if (id == 'refresh2') {
            $('#transTbl').DataTable().destroy();
            drawTransactionData();
        } else if (id == 'refresh4') {
            $('#stmentTbl').DataTable().destroy();
            drawStatementData();
        }
    });

    // aging report clone
    const aging = $('.aging').clone();
    $('#stmentTbl').after(aging);
    $('#active5').append(aging.clone());

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
        columns: [{ data: 'name', name: 'name'}],
        order: [[0, "desc"]],
        searchDelay: 500,
        dom: 'Blfrtip',
        buttons: ['excel', 'csv', 'pdf']
    };
    const indexCol = [{name: 'id', data: 'DT_Row_Index'}];
    function drawCustomerData() {
        const config = JSON.parse(JSON.stringify(dTableConfig));
        config.dom = 'frt';
        const dataTable = $('#customerTbl').DataTable(config);
    }
    function drawTransactionData(start_date='', end_date='') {
        const config = JSON.parse(JSON.stringify(dTableConfig));
        config.ajax.data = {...config.ajax.data, start_date, end_date, is_transaction: 1};
        config.order[0][1] = 'asc';
        const cols = ['date', 'type', 'note', 'invoice_amount', 'amount_paid', 'account_balance'];
        config.columns = indexCol.concat(cols.map(v => ({data: v, name: v})));
        const dataTable = $('#transTbl').DataTable(config);
    }
    function drawInvoiceData() {
        const config = JSON.parse(JSON.stringify(dTableConfig));
        config.ajax.data = {...config.ajax.data, is_invoice: 1};
        const cols = ['date', 'status', 'note', 'amount', 'paid'];
        config.columns = indexCol.concat(cols.map(v => ({data: v, name: v})));
        const dataTable = $('#invoiceTbl').DataTable(config);
    }
    function drawStatementData(start_date='', end_date='') {
        const config = JSON.parse(JSON.stringify(dTableConfig));
        config.ajax.data = {...config.ajax.data, start_date, end_date, is_statement: 1};
        config.order[0][1] = 'asc';
        const cols = ['date', 'type', 'note', 'invoice_amount', 'amount_paid', 'invoice_balance'];
        config.columns = indexCol.concat(cols.map(v => ({data: v, name: v})));
        config.bSort = false;
        const dataTable = $('#stmentTbl').DataTable(config);
    }
</script>
@endsection