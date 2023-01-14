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
                    <div class="card-body">
                        <div class="row form-group">
                            <div class="col-4">
                                <label for="customer">Supplier</label>
                                <select name="supplier_id" id="supplier" class="form-control" data-placeholder="Choose Supplier">
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="status">Delivery Status</label>
                                <select name="status" id="status" class="custom-select">
                                    <option value="">-- select status --</option>
                                    @foreach (['pending', 'partial', 'complete'] as $status)
                                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <label for="amount">Total Amount</label>
                                <input type="text" id="amount_total" class="form-control" readonly>
                            </div>                            
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="purchaseordersTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
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
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajax: {
            headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"},
        },
    };
    
    const Index = {
        init() {
            $.ajaxSetup(config.ajax);

            $('#status').change(this.statusChange);
            $('#supplier').select2({allowClear: true}).val('').trigger('change')
            .change(this.supplierChange);

            this.drawDataTable();
        },

        supplierChange() {
            $('#purchaseordersTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        statusChange() {
            $('#purchaseordersTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        drawDataTable() {
            $('#purchaseordersTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.purchaseorders.get") }}',
                    type: 'post',
                    data: {
                        supplier_id: $('#supplier').val(),
                        status: $('#status').val(),
                    },
                    dataSrc: ({data}) => {
                        $('#amount_total').val('');
                        // $('#balance_total').val('');
                        if (data.length && data[0].aggregate) {
                            const aggregate = data[0].aggregate;
                            $('#amount_total').val(aggregate.amount_total);
                            // $('#balance_total').val(aggregate.balance_total);
                        }
                        return data;
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
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        },
    };

    $(() => Index.init());
</script>
@endsection