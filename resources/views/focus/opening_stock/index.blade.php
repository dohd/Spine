@extends ('core.layouts.app')

@section('title', 'Product Opening Stock')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Product Opening Stock</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.opening_stock.partials.opening-stock-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        {{-- 
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <label for="supplier">Supplier</label>
                                <select name="supplier_id" id="supplier" class="form-control" data-placeholder="Choose Supplier">
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="amount">Total Amount (Ksh.)</label>
                                <input type="text" id="amount_total" class="form-control" readonly>
                            </div>                            
                            <div class="col-2">
                                <label for="unallocate">Total Unallocated (Ksh.)</label>
                                <input type="text" id="unallocated_total" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        --}}

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="openingStockTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tr No</th>
                                        <th>Date</th>
                                        <th>Note</th>
                                        <th>Warehouse</th>
                                        <th>Amount</th>                                                                     
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
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        init() {
            this.drawDataTable();
        },

        drawDataTable() {
            $('#openingStockTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.opening_stock.get') }}",
                    type: 'POST',
                    // data: {supplier_id},
                    // dataSrc: ({data}) => {
                    //     $('#amount_total').val('');
                    //     $('#unallocated_total').val('');
                    //     if (data.length) {
                    //         const aggregate = data[0].aggregate;
                    //         $('#amount_total').val(aggregate.amount_total);
                    //         $('#unallocated_total').val(aggregate.unallocated_total);
                    //     }
                    //     return data;
                    // },
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'tid', name: 'tid'},
                    {data: 'date', name: 'date'},                    
                    {data: 'note', name: 'note'},
                    {data: 'warehouse', name: 'warehouse'},
                    {data: 'amount', name: 'amount'},                    
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        }
    };

    $(() => Index.init());
</script>
@endsection
