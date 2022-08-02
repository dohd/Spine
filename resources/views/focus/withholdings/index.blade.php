@extends ('core.layouts.app')

@section ('title', 'WithHolding Tax management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">WithHolding Tax  Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.withholdings.partials.withholdings-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="withholdings-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>#TID</th>
                                        <th>Customer</th>
                                        <th>Note</th>
                                        <th>Certificate / Serial</th>
                                        <th>Amount (Ksh.)</th>
                                        <th>Date</th>
                                        <th>Invoice</th>
                                        <th>{{ trans('labels.general.actions') }}</th>
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
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });
    const language = {@lang('datatable.strings')};
    const dataTable = $('#withholdings-table').dataTable({
        processing: true,
        responsive: true,
        language,
        ajax: {
            url: '{{ route("biller.withholdings.get") }}',
            type: 'post'
        },
        columns: [
            {data: 'DT_Row_Index', name: 'id'},
            {data: 'tid', name: 'tid'},
            {data: 'customer', name: 'customer'},
            {data: 'note', name: 'note'},
            {data: 'reference', name: 'reference'},
            {data: 'amount', name: 'amount'},
            {data: 'cert_date', name: 'cert_date'},
            {data: 'invoice_tid', name: 'invoice_tid'},
            {data: 'actions', name: 'actions', searchable: false, sortable: false}
        ],
        columnDefs: [
            { type: "custom-number-sort", targets: [5] },
            { type: "custom-date-sort", targets: [6] }
        ],
        order: [[0, "desc"]],
        searchDelay: 500,
        dom: 'Blfrtip',
        buttons: {
            buttons: [
                {extend: 'csv', footer: true, exportOptions: {columns: [0, 1]}},
                {extend: 'excel', footer: true, exportOptions: {columns: [0, 1]}},
                {extend: 'print', footer: true, exportOptions: {columns: [0, 1]}}
            ]
        }
    });
</script>
@endsection
