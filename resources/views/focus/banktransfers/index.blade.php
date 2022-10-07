@extends ('core.layouts.app')
@section ('title', 'Transfers management')

@section('content')
<div class="">
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title mb-0">Transfers Management</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.banktransfers.partials.banktransfers-header-buttons')
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
                                <table id="banktransfers-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Account (Credit-Debit)</th>
                                            <th>Reference</th>
                                            <th>Amount</th>
                                            <th>Transaction Date</th>
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
    const dataTable = $('#banktransfers-table').dataTable({
        stateSave: true,
        processing: true,
        serverSide: true,
        responsive: true,
        language,
        ajax: {
            url: '{{ route("biller.banktransfers.get") }}',
            type: 'post'
        },
        columns: [{
                data: 'DT_Row_Index',
                name: 'id'
            },
            {
                data: 'account',
                name: 'account'
            },
            {
                data: 'note',
                name: 'note'
            },
            {
                data: 'debit',
                name: 'debit'
            },
            {
                data: 'transaction_date',
                name: 'transaction_date'
            },
            {
                data: 'actions',
                name: 'actions',
                searchable: false,
                sortable: false
            }
        ],
        order: [[0, "desc"]],
        searchDelay: 500,
        dom: 'Blfrtip',
        buttons: ['csv', 'excel', 'print']
    });
</script>
@endsection