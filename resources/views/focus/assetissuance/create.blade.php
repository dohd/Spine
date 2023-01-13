@extends ('core.layouts.app')

@section ('title', 'Asset & Issuance Management | Asset & Issuance Create')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="mb-0">Create Asset & Issuance</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.assetissuance.partials.assetissuance-header-buttons')
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
                            {{ Form::open(['route' => 'biller.assetissuance.store', 'method' => 'POST', 'id' => 'assetIssuanceForm']) }}
                                <div class="form-group">
                                    @include("focus.assetissuance.form")
                                </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>

    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});
    // const stockHtml = [$('#stockTbl tbody tr:eq(0)').html(), $('#stockTbl tbody tr:eq(1)').html()];
     const stockUrl = "{{ route('biller.products.purchase_search') }}"
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    $('#issue_date').datepicker('setDate', new Date());
    $('#return_date').datepicker('setDate', new Date());

    //New Approach
    let tableRow = $('#productsTbl tbody tr:first').html();
    $('#productsTbl tbody tr:first').remove();
    let rowIds = 1;
    $('.stockname').autocomplete(predict(stockUrl,stockSelect));
    $('#addstock').click(function() {
        rowIds++;
        let i = rowIds;
        const html = tableRow.replace(/-0/g, '-'+i);
        $('#productsTbl tbody').append('<tr>' + html + '</tr>');
        $('.stockname').autocomplete(predict(stockUrl,stockSelect));
    });




    $('#productsTbl').on('click', '.remove', removeRow);
    function removeRow() {
        const $tr = $(this).parents('tr:first');
        $tr.next().remove();
        $tr.remove();
    }

    let stockNameRowId = 0;
    function stockSelect(event, ui) {
        const {data} = ui.item;
        const i = stockNameRowId;
        $('#stockitemid-'+i).val(data.id);
        $('#stockdescr-'+i).val(data.name);
        $('#qty-'+i).val(data.qty);
        $('#quantity-'+i).val(data.qty);
        $('#serial-'+i).val(data.code);
        $('#serial_numb-'+i).val(data.code);
        $('#purchase_price-'+i).val(data.purchase_price);
    }
    $('#productsTbl').on('mouseup', '.stockname', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.stockname')) stockNameRowId = id;
    }); 

    // select2 config
    function select2Config(url, callback) {
        return {
            ajax: {
                url,
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({q: term, keyword: term}),
                processResults: callback
            }
        }
    }

    function predict(url, callback) {
        return {
            source: function(request, response) {
                $.ajax({
                    url,
                    dataType: "json",
                    method: "POST",
                    data: {keyword: request.term, pricegroup_id: $('#pricegroup_id').val()},
                    success: function(data) {
                        response(data.map(v => ({
                            label: v.name,
                            value: v.name,
                            data: v
                        })));
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: callback
        };
    }

    // On searching supplier
    $('#employeebox').change(function() {
        const name = $('#employeebox option:selected').text().split(' : ')[0];
        const [id, taxId] = $(this).val().split('-');
        $('#taxid').val(taxId);
        $('#employeeid').val(id);
        $('#employee').val(name);
    });

    // load employees
    const employeeUrl = "{{ route('biller.assetissuance.select') }}";
    function employeeData(data) {
        return {results: data.map(v => ({id: v.id, text: v.first_name+' : '+v.email}))};
    }
    $('#employeebox').select2(select2Config(employeeUrl, employeeData));

    // function autoComp(i) {
    //     return {
    //         source: function(request, response) {
    //             // stock product
    //             // let term = request.term;
    //              let url = "{{ route('biller.products.purchase_search') }}";
    //             $.ajax({
    //                 url,
    //                 dataType: "json",
    //                 method: "POST",
    //                 data: {keyword: request.term, pricegroup_id: $('#pricegroup_id').val()},
                    
    //                 success: function(data) {
    //                     response(data.map(v => ({
    //                         label: v.name,
    //                         value: v.name,
    //                         data: v
    //                     })));
    //                 }
    //             });
    //         },
    //         autoFocus: true,
    //         minLength: 0,
    //         select: function(event, ui) {
    //             const {data} = ui.item;
    //             const i = stockNameRowId;
    //             $('#stockitemid-'+i).val(data.id);
    //             $('#stockdescr-'+i).val(data.name);
    //             $('#stockname-'+i).val(data.name);
    //             $('#qty-'+i).val(data.qty);
    //             $('#serial-'+i).val(data.code);

    //             console.log(i);
    //         }
    //     };
    // }    
    
</script>
@endsection