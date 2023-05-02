@extends ('core.layouts.app')

@section ('title',  'Reconciliations Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Reconciliations Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.reconciliations.partials.reconciliations-header-buttons')
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
                            <table id="reconciliationTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Account</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>System Amount</th>
                                        <th>Opening Amount</th>
                                        <th>Closing Amount</th>  
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="100%" class="text-center text-success font-large-1">
                                            <i class="fa fa-spinner spinner"></i>
                                        </td>
                                    </tr>
                                </tbody>
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

    const language = {
        @lang('datatable.strings')
    };
    const dataTable = $('#reconciliationTbl').dataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        language,
        ajax: {
            url: '{{ route("biller.reconciliations.get") }}',
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
                data: 'start_date',
                name: 'start_date'
            },
            {
                data: 'end_date',
                name: 'end_date'
            },
            {
                data: 'open_amount',
                name: 'open_amount'
            },
            {
                data: 'close_amount',
                name: 'close_amount'
            },
            {
                data: 'system_amount',
                name: 'system_amount'
            },
            {
                data: 'actions',
                name: 'actions',
                searchable: false,
                sortable: false
            },            
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