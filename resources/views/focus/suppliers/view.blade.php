@extends('core.layouts.app', [
    'page' => 'class = "horizontal-layout horizontal-menu content-detached-left-sidebar app-contacts" data-open = "click" data-menu = "horizontal-menu" data-col = "content-detached-left-sidebar"'
])

@section('title', trans('labels.backend.suppliers.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Supplier Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.suppliers.partials.suppliers-header-buttons')
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
                                    <a href="{{ route('biller.suppliers.edit', $supplier) }}" class="btn btn-blue btn-outline-accent-5 btn-sm">
                                        <i class="fa fa-pencil"></i> {{trans('buttons.general.crud.edit')}}
                                    </a>&nbsp;
                                    <button type="button" class="btn btn-danger btn-outline-accent-5 btn-sm" id="delCustomer">
                                        {{Form::open(['route' => ['biller.suppliers.destroy', $supplier], 'method' => 'DELETE'])}}{{Form::close()}}
                                        <i class="fa fa-trash"></i> {{trans('buttons.general.crud.delete')}}
                                    </button>
                                </div>
                                <div class="card-body">
                                    @include('focus.suppliers.partials.tabs')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    @include('focus.suppliers.partials.sidebar')
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

    // delete supplier
    $('#delCustomer').click(function() {
        $(this).children('form').submit();
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

    // insert aging table after statement table
    $('#stmentTbl').after($('.aging').clone());    

    setTimeout(() => {
        drawSupplierData();
        drawTransactionData();
        drawBillData();
        drawStatementData();
    }, "{{ config('master.delay') }}");
    const dTableConfig = {
        processing: true,
        serverSide: true,
        responsive: true,
        stateSave: true,
        language: {@lang('datatable.strings')},
        ajax: {
            url: '{{ route("biller.suppliers.get") }}',
            type: 'POST',
            data: {supplier_id: "{{ $supplier->id }}"}
        },
        columns: [{ data: 'name', name: 'name'}],
        order: [[0, "desc"]],
        searchDelay: 500,
        dom: 'Blfrtip',
        buttons: ['excel', 'csv', 'pdf']
    }
    const indexCol = [{name: 'id', data: 'DT_Row_Index'}];
    function drawSupplierData() {
        const config = JSON.parse(JSON.stringify(dTableConfig));
        config.dom = 'frt';
        const dataTable = $('#supplierTbl').DataTable(config);
    }
    function drawTransactionData(start_date='', end_date='') {
        const config = JSON.parse(JSON.stringify(dTableConfig));
        config.ajax.data = {...config.ajax.data, start_date, end_date, is_transaction: 1};
        config.order[0][1] = 'asc';
        const cols = ['date', 'type', 'note', 'bill_amount', 'amount_paid', 'balance'];
        config.columns = indexCol.concat(cols.map(v => ({data: v, name: v})));
        const dataTable = $('#transTbl').DataTable(config);
    }
    function drawBillData() {
        const config = JSON.parse(JSON.stringify(dTableConfig));
        config.ajax.data = {...config.ajax.data, is_bill: 1};
        const cols = ['date', 'reference', 'note', 'amount', 'paid'];
        config.columns = indexCol.concat(cols.map(v => ({data: v, name: v})));
        const dataTable = $('#billTbl').DataTable(config);
    }
    function drawStatementData(start_date='', end_date='') {
        const config = JSON.parse(JSON.stringify(dTableConfig));
        config.ajax.data = {...config.ajax.data, start_date, end_date, is_statement: 1};
        config.order[0][1] = 'asc';
        const cols = ['date', 'type', 'note', 'bill_amount', 'amount_paid', 'balance'];
        config.columns = indexCol.concat(cols.map(v => ({data: v, name: v})));
        config.bSort = false;
        const dataTable = $('#stmentTbl').DataTable(config);    
    }
</script>
@endsection