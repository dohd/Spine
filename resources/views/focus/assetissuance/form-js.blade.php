@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="">
   $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});
    // const stockHtml = [$('#stockTbl tbody tr:eq(0)').html(), $('#stockTbl tbody tr:eq(1)').html()];
     const stockUrl = "{{ route('biller.products.purchase_search') }}"
    // let stockRowId = 0;
    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    // date
    // $('#issue_date').datepicker('setDate', new Date("{{ $assetissuance->issue_date }}"));
    // $('#return_date').datepicker('setDate', new Date("{{ $assetissuance->return_date }}"));

    // employee
    const employeeText = "{{ $assetissuance->employee_name }} ";
    $('#requisition').prop("readonly", true);
    $('#employeebox').append(new Option(employeeText, true)).change();
    $('.serial_number').prop("readonly", true);
    $('.qty').prop("readonly", true);
    

    //New Approach
    let tableRow = $('#productsTbl tbody tr:first').html();
    $('#productsTbl tbody tr:first').remove();
    let rowIds = 1;
    $('.stockname').autocomplete(predict(stockUrl,stockSelect));
    $('#addstock').click(function() {
        rowIds++;
        let i = rowIds;
        const html = tableRow.replace(/-0/g, '-'+i);

        // $('form').append('<input type="hidden" name="hello_world2" value="Hello World2" />');

        //console.log($('#productsTbl tbody').find('input').length);
        $('#productsTbl tbody').append('<tr>' + html + '</tr>');
        //console.log($('#productsTbl tbody').find('input').length);
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
        $('#employeeid').val(id);
        $('#employee').val(name);
    });

    // load employees
    const employeeUrl = "{{ route('biller.assetissuance.select') }}";
    function employeeData(data) {
        return {results: data.map(v => ({id: v.id, text: v.first_name+' : '+v.email}))};
    }
    $('#employeebox').select2(select2Config(employeeUrl, employeeData));    
</script>

@endsection