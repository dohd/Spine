@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="">
     $('form').submit(function() {
        event.preventDefault();
        console.log($(this).serializeArray());
    })
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});
    const stockHtml = [$('#stockTbl tbody tr:eq(0)').html(), $('#stockTbl tbody tr:eq(1)').html()];
    const stockUrl = "{{ route('biller.products.purchase_search') }}"
    let stockRowId = 0;
    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    $('.stockname').autocomplete(predict(stockUrl, stockSelect));
    $('#stockTbl').on('click', '#addstock, .remove', function() {
        if ($(this).is('#addstock')) {
            stockRowId++;
            const i = stockRowId;
            const html = stockHtml.reduce((prev, curr) => {
                const text = curr.replace(/-0/g, '-'+i).replace(/d-none/g, '');
                return prev + '<tr>' + text + '</tr>';
            }, '');
            console.log(html);
            $('#stockTbl tbody').append(html);
            //$('.stockname').autocomplete(predict(stockUrl));
        }

        if ($(this).is('.remove')) {
            const $tr = $(this).parents('tr:first');
            $tr.next().remove();
            $tr.remove();
        }    
    })
     // autocomplete config method
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
    let stockNameRowId = 0;
    function stockSelect(event, ui) {
        const {data} = ui.item;
        const i = stockNameRowId;
        $('#stockitemid-'+i).val(data.id);
        $('#stockdescr-'+i).val(data.name);
        $('#qty-'+i).val(data.qty);
        $('#serial-'+i).val(data.code);
        console.log(data);

        // const purchasePrice = accounting.unformat(data.purchase_price);
        // $('#price-'+i).val(accounting.formatNumber(purchasePrice)).change();

        // $('#uom-'+i).html('');
        // if(data.units)
        // data.units.forEach(v => {
        //     const rate = accounting.unformat(v.base_ratio) * purchasePrice;
        //     const option = `<option value="${v.code}" purchase_price="${rate}" >${v.code}</option>`;
        //     $('#uom-'+i).append(option);
        // });
        // if(data.uom){
        //     const option = `<option value="${data.uom}"  >${data.uom}</option>`;
        // $('#uom-'+i).append(option);
        // }
        
    }
    $('#stockTbl').on('mouseup', '.stockname', function() {
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

    // On searching supplier
    $('#employeebox').change(function() {
        const name = $('#employeebox option:selected').text().split(' : ')[0];
        console.log(name);
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


    
</script>

{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajaxSetup: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        datepicker: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Form = {
        init() {
            $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());
            $('#stockTbl').ready(this.tableReady);
            $('#stockTbl').on('keyup change', '.qty', this.tableEventChange);
        },

        tableReady() {
            $(this).find('tbody tr').each(function() {
                const el = $(this);
                const unit = el.find('.unit');
                const warehouse = el.find('.wh');
                const qty = el.find('.qty');
                if (!warehouse.children().length) {
                    [unit, warehouse, qty].forEach(el => el.attr('disabled', true));
                    const inputs = `<input type="hidden" name="unit[]">
                        <input type="hidden" name="warehouse_id[]"><input type="hidden" name="qty[]">`;
                    el.append(inputs);
                }
            });
        },

        tableEventChange(event) {
            const el = $(this);
            const qty = parseFloat(el.val());
            const row = el.parents('tr');
            const qtyLimit = parseFloat(row.find('.qty').val());
            const qtyStock = parseFloat(row.find('.issued').val());
            console.log(qtyStock);
            if (event.type == 'change') {
                if (qty > qtyStock) el.val(qtyStock).change();
                else if (qty == 0) el.val(1).change();
            }
            Form.qtyAlert(qty, qtyLimit, qtyStock);
            Form.columnTotals();
        },

        qtyAlert(qty = 0, qtyLimit = 0, qtyStock = 0) {
            const msg = `<div class="alert alert-warning col-12 stock-alert" role="alert">
                <strong>Minimum inventory limit!</strong> Please restock product.</div>`;
            if (qtyStock <= qtyLimit && qty >= qtyStock) {
                $('.content-header div:first').before(msg);
                setTimeout(() => $('.content-header div:first').remove(), 4000);
                scroll(0,0);
            }
        },

        columnTotals() {
            let qtyTotal = 0;
            let qtyApprovedTotal = 0;
            $('#stockTbl tbody tr').each(function() {
                let qty = $(this).find('.qty').val();
                let qtyApproved = $(this).find('.issued').val();
                console.log(qty);
                if (qty > 0) {
                    qtyTotal += parseFloat(qty);
                    qtyApprovedTotal += parseFloat(qtyApproved);
                }
            });
            $('#qty_total').val(qtyTotal);
            $('#approved_qty_total').val(qtyTotal);
        },
    }

    $(() => Form.init());
</script>

@endsection