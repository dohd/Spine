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
                            <div class="col-2 h4">Product Count</div>                            
                            <div class="col-2 h4 stock-count">0</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-2 h4">Total Unit Cost</div>                           
                            <div class="col-4 h4 stock-worth">0.00</div>
                        </div>
                        <div class="row">                            
                            <div class="col-3">
                                <label for="warehouse" class="h4">Product Location</label>
                                <select name="warehouse_id" id="warehouse" class="custom-select">
                                    <option value="">-- select location --</option>
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
                                        <option value="{{ $category->id }}">
                                            {{ $category->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>  
                            <div class="col-2">
                                <label for="status" class="text-primary h4">Product Status</label>
                                <select name="status" id="status" class="custom-select">
                                    <option value="">-- select status --</option>
                                    @foreach (['in_stock', 'out_of_stock'] as $status)
                                        <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
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
                                        <th>Category Name</th>
                                        <th>product_code</th>
                                        <th>UOM</th>
                                        <th>Unit (Qty)</th>
                                        <th>Purchase Price</th>
                                        <th>Expiry Date</th>
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
        status: '',
        warehouseId: @json(request('warehouse_id')),
        categoryId: @json(request('productcategory_id')),

        init() {
            this.drawDataTable();
            $('#status').change(this.statusChange);
            $('#warehouse').val(this.warehouseId).change(this.warehouseChange);
            $('#category').val(this.categoryId).change(this.categoryChange);
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
                        if (data.length && data[0].aggregate) {
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
                    {data: 'productcategory_id', name: 'productcategory_id'},
                    {data: 'code', name: 'code'}, 
                    {data: 'unit', name: 'unit'},                   
                    {data: 'qty', name: 'qty'},
                    {data: 'purchase_price', name: 'purchase_price'},
                    {data: 'expiry', name: 'expiry'},
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
