@extends ('core.layouts.app')

@section ('title', trans('labels.backend.purchaseorders.management'))

@section('content')
<div class="">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class="content-header-title mb-0">{{ trans('labels.backend.purchaseorders.management') }}</h4>
            </div>
            <div class="content-header-right col-md-6 col-12">
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
                                            <th>#{{ trans('purchaseorders.purchaseorder')}}</th>
                                            <th>{{ trans('suppliers.supplier') }}</th>
                                            <th>Order date</th>
                                            <th>{{ trans('general.amount') }}</th>
                                            <th>{{ trans('general.status') }}</th>
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

    const relId = @json(@$segment)['id'];
    const relType = @json(@$input)['rel_type'];
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
            data: {
                i_rel_id: relId,
                i_rel_type: relType
            },
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
                data: 'date',
                name: 'date'
            },
            {
                data: 'amount',
                name: 'amount'
            },
            {
                data: 'status',
                name: 'status'
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