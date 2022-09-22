@extends ('core.layouts.app')

@section ('title', trans('labels.backend.products.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-2">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ trans('labels.backend.products.management') }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.products.partials.products-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-2 h4">Total Stock Count</div>                            
                            <div class="col-2 h4 stock-count">0</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-2 h4">Total Stock Worth</div>                           
                            <div class="col-4 h4 stock-worth">0.00</div>
                        </div>
                        <div class="row">                            
                            <div class="col-3">
                                <label for="warehouse" class="h4">Warehouse</label>
                                <select name="warehouse_id" id="warehouse" class="custom-select">
                                    <option value="">-- select warehouse --</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <label for="category" class="h4">Product Category</label>
                                <select name="category_id" id="category" class="custom-select">
                                    <option value="">-- select category --</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->title }}</option>
                                    @endforeach
                                </select>
                            </div>  
                            <div class="col-2">
                                <label for="status" class="text-primary h4">Product Status</label>
                                <select name="status" id="status" class="custom-select">
                                    <option value="">-- select status --</option>
                                    @foreach (['in stock', 'out of stock'] as $status)
                                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
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
                            <table id="productsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>{{ trans('labels.backend.products.table.id') }}</th>
                                        <th>Description</th>
                                        <th>Product Code</th>
                                        <th>Unit (Qty)</th>
                                        <th>Unit Code</th>
                                        <th>Purchase Price</th>
                                        <th>{{ trans('general.createdat') }}</th>
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
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}" }}
    };
    
    const Index = {
        status: 'in stock',
        warehouseId: '',
        categoryId: '',

        init() {
            this.fromWarehouseRedirect();
            this.drawDataTable();
            $('#warehouse').change(this.warehouseChange);
            $('#category').change(this.categoryChange);
            $('#status').change(this.statusChange).val('in stock');
        },

        fromWarehouseRedirect() {
            const queryString = window.location.search.substring(1);
            const warehouseId = new URLSearchParams(queryString).get('warehouse_id');
            $('#warehouse').val(warehouseId);
            this.warehouseId = warehouseId;
        },

        categoryChange() {
            Index.categoryId = $(this).val();
            $('#productsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        warehouseChange() {
            Index.warehouseId = $(this).val();
            $('#productsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        statusChange() {
            Index.status = $(this).val();
            $('#productsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        drawDataTable() {
            $('#productsTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.products.get") }}',
                    type: 'post',
                    data: {
                        warehouse_id: this.warehouseId,
                        category_id: this.categoryId,
                        status: this.status
                    },
                    dataSrc: ({data}) => {
                        $('.stock-count').text('0');
                        $('.stock-worth').text('0.00');
                        if (data.length) {
                            const aggr = data[0].aggregate;
                            $('.stock-count').text(aggr.product_count);
                            $('.stock-worth').text(aggr.product_worth);
                        }
                        return data;
                    },
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'code', name: 'code'},                    
                    {data: 'qty', name: 'qty'},
                    {data: 'unit', name: 'unit'},
                    {data: 'price', name: 'price'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print']
            });
        },
    };    

    $(() => Index.init());
</script>
@endsection
