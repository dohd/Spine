@extends ('core.layouts.app')

@section ('title', trans('labels.backend.purchaseorders.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ trans('labels.backend.purchaseorders.management') }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.purchaseorders.partials.purchaseorders-header-buttons')
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
                            <table id="purchaseorders-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>#Order No</th>
                                        <th>{{ trans('suppliers.supplier') }}</th>
                                        <th>Note</th>
                                        <th>Items Ordered</th>
                                        <th>{{ trans('general.amount') }}</th>
                                        <th>Date</th>
                                        <th>{{ trans('general.status') }}</th>
                                        <th>Items Received</th>
                                        <th>{{ trans('labels.general.actions') }}</th>
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

    const tableLan = {@lang('datatable.strings')};
    var dataTable = $('#purchaseorders-table').dataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        stateSave: true,
        language: tableLan,
        ajax: {
            url: '{{ route("biller.purchaseorders.get") }}',
            type: 'post',
        },
        columns: [{
                data: 'DT_Row_Index',
                name: 'id'
            },
            {
                data: 'tid',
                name: 'tid'
            },
            {
                data: 'supplier',
                name: 'supplier'
            },
            {
                data: 'note',
                name: 'note'
            },
            {
                data: 'count',
                name: 'count'
            },
            {
                data: 'amount',
                name: 'amount'
            },
            {
                data: 'date',
                name: 'date'
            },
            {
                data: 'status',
                name: 'status'
            },
            {
                data: 'grn_count',
                name: 'grn_count'
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