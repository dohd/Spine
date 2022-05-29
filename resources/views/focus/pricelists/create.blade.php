@extends ('core.layouts.app')

@section ('title', 'Create | Price List Management')

@section('content')
<div>
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
                                {{ Form::open(['route' => 'biller.pricelists.store']) }}
                                    @include('focus.pricelists.form')
                                {{ Form::close() }}
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

    // Add product row
    let rowId = 1;
    let rowNo = 1;
    const rowHtml = $('#listTbl tbody tr').html();
    $('#name-0').autocomplete(autoComp(0));
    $('#addRow').click(function() {
        const html = rowHtml
        .replace('<td>1</td>', '<td>' + (rowNo + 1) + '</td>')
        .replace(/-0/g, '-'+rowId)
        .replace('d-none', '');
        $('#listTbl tbody').append('<tr>' + html + '</tr>');
        $('#name-'+rowId).autocomplete(autoComp(rowId));
        rowId++;
        rowNo++;
    });
    // remove row
    $('#listTbl').on('click', '.remove', function() {
        $(this).parents('tr').remove();
        rowNo--;
    });

    // add product constraint
    $('#addRow').attr('disabled', true);
    $('#name-0').attr('readonly', true);
    $('#price-0').attr('readonly', true);
    $('form').on('change', '#client, #supplier', function() {
        if ($(this).is('#client')) $('#supplier').attr('disabled', true);
        if ($(this).is('#supplier')) {
            $('#client').attr('disabled', true);
            $('#is_client').attr('disabled', true);
        }
        if ($(this).val()) {
            $('#addRow').attr('disabled', false);
            $('#name-0').attr('readonly', false);
            $('#price-0').attr('readonly', false);
        }
    });

    // autocomplete function
    function autoComp(i) {
        return {
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('biller.products.quote_product_search') }}",
                    method: 'POST',
                    data: 'keyword=' + request.term,
                    success: result => response(result.map(v => ({label: v.name, value: v.name, data: v}))),
                });
            },
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                const {data} = ui.item;
                $('#id-'+i).val(data.product_id);
                $('#name-'+i).val(data.name);
            }
        };
    }    
</script>
@endsection
