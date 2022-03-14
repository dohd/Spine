@extends ('core.layouts.app')

@section ('title', 'Direct Purchase')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="content-header-title mb-0">Direct Purchase Management</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.purchases.partials.purchases-header-buttons')
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
                            <table id="purchases" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>#Purchase No</th>
                                        <th>Purchase Date</th>
                                        <th>Reference</th>
                                        <th>Supplier</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>Balance</th>
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

    const tableLan = {@lang('datatable.strings')};
    var dataTable = $('#purchases').dataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        stateSave: true,
        language: tableLan,
        ajax: {
            url: "{{ route('biller.purchases.get') }}",
            type: 'post',
            data: {rel_type: 1}
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
                data: 'date',
                name: 'date'
            },
            {
                data: 'doc_ref',
                name: 'doc_ref'
            },
            {
                data: 'supplier',
                name: 'supplier'
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
                data: 'balance',
                name: 'balance'
            },
            {
                data: 'actions',
                name: 'actions',
                searchable: false,
                sortable: false
            }
        ],
        order: [
            [0, "DESC"]
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