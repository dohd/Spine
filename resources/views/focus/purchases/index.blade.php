@extends ('core.layouts.app')

@section ('title', 'Direct Purchase')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Direct Purchase Management</h4>
        </div>
        <div class="content-header-right col-6">
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
                                        <th>Purchase No.</th>
                                        <th>Supplier</th>
                                        <th>Tax Pin</th>
                                        <th>Note</th>
                                        <th>Date</th>
                                        <th>Reference</th>                                        
                                        <th>Amount</th>
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

    const dataTable = $('#purchases').dataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        stateSave: true,
        language: {@lang('datatable.strings')},
        ajax: {
            url: "{{ route('biller.purchases.get') }}",
            type: 'post',
            data: {rel_type: 1}
        },
        columns: [{
                data: 'DT_Row_Index',
                name: 'id'
            },
            ...[
                'tid', 'supplier', 'supplier_taxid', 'note', 'date', 'reference', 'amount', 'balance'
            ].map(v => ({data:v, name: v})),
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
        buttons: ['csv', 'excel', 'print'],
    });
</script>
@endsection