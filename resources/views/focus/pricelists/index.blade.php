@extends ('core.layouts.app')

@section ('title', 'Price List Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Price List Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.pricelists.partials.pricelists-header-buttons')
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
                            <div class="row">
                                <div class="col-4">
                                    <label for="client">Client</label>
                                    <select name="ref_id" id="client" class="form-control" data-placeholder="Choose client"></select>
                                </div>
                                <div class="col-4">
                                    <label for="supplier">Supplier</label>
                                    <select name="ref_id" id="supplier" class="form-control" data-placeholder="Choose supplier"></select>
                                </div>
                                <div class="col-4">
                                    <button type="button" class="btn btn-primary" style="margin-top: 2em;" id="load">Load</button>
                                </div>
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
                            <table id="listTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product Name</th>
                                        <th>Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="4" class="text-center text-success font-large-1">
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
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});
    const select2Config = (url, callback) => ({
        ajax: {
            url,
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: ({term}) => ({ search: term }),
            processResults: data  => callback(data),
        }
    });

    // fetch customers
    const clientUrl = "{{ route('biller.customers.select') }}";
    function clientCb(data) {
        return {
            results: data.map(v => ({ 
                id: v.id, 
                text: `${v.name} - ${v.company}`,
            }))
        }
    };
    $("#client").select2(select2Config(clientUrl, clientCb));

    // fetch suppliers
    const suppliertUrl = "{{ route('biller.suppliers.select') }}";
    function supplierCb(data) {
        return {
            results: data.map(v => ({ 
                id: v.id, 
                text: v.name + ' - ' + v.email 
            }))
        }
    };
    $("#supplier").select2(select2Config(suppliertUrl, supplierCb));

    // filter and load
    $(document).on('change', '#client, #supplier', function() {
        if ($(this).is('#client')) $('#supplier').attr('disabled', true);
        if ($(this).is('#supplier')) $('#client').attr('disabled', true);
    });
    $('#load').click(function() {
        const referenceId = $('#client').val() || $('#supplier').val();
        const isClient = $('#client').val() ? 1 : 0;
        $('#listTbl tbody tr').remove();
        $('#listTbl').DataTable().destroy();   
        draw_data(referenceId, isClient);
    });

    // dataTable
    function draw_data(ref_id = 0, is_client = 0) {
        const language = { @lang("datatable.strings") };
        const dataTable = $('#listTbl').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language,
            ajax: {
                url: '{{ route("biller.pricelists.get") }}',
                type: 'post',
                data: {ref_id, is_client}
            },
            columns: [
                {
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'price',
                    name: 'price'
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
            buttons: ['csv', 'excel', 'pdf']
        });
    }    
</script>
@endsection