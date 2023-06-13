{{-- <button class="btn btn-primary click btn-sm" type="button" data-toggle="modal" data-target="#exampleModal">Product Code</button>
@include('focus.pricelistsSupplier.partials.add-supplier-list')



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
                    {data: 'productcategory_id', name: 'productcategory_id'},
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
@endsection --}}
