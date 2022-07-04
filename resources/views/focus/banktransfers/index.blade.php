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
                                            <th>Account</th>
                                            <th>Reference</th>
                                            <th>Debit</th>
                                            <th>Credit</th>
                                            <th>Transaction Date</th>
                                            <th>{{ trans('general.createdat') }}</th>                                             
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
                data: 'account_id',
                name: 'account_id'
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
                data: 'credit',
                name: 'credit'
            },
            {
                data: 'transaction_date',
                name: 'transaction_date'
            },
            {
                data: 'created_at',
                name: 'created_at'
            },
            {
                data: 'actions',
                name: 'actions',
                searchable: false,
                sortable: false
            }
        ],
        order: [
            [0, "desc"]
        ],
        searchDelay: 500,
        dom: 'Blfrtip',
        buttons: {
            buttons: [

                {
                    extend: 'csv',
                    footer: true,
                    exportOptions: {
                        columns: [0, 1]
                    }
                },
                {
                    extend: 'excel',
                    footer: true,
                    exportOptions: {
                        columns: [0, 1]
                    }
                },
                {
                    extend: 'print',
                    footer: true,
                    exportOptions: {
                        columns: [0, 1]
                    }
                }
            ]
        }
    });
</script>
@endsection