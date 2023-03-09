{{ Html::script(mix('js/dataTable.js')) }}
<script>    
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    };

    // ajax setup
    $.ajaxSetup(config.ajax);

    // Intialize datepicker
    $('.datepicker').each(function() {
        const d = $(this).attr('value');
        $(this).datepicker(config.date).datepicker('setDate', new Date(d));
    });

    // on qty, tax_rate change
    $('#productsTbl').on('change', '.qty, .taxid', function() {
        const row = $(this).parents('tr');
        const qty = row.find('.qty').val()*1;
        const price = accounting.unformat(row.find('.price').val());
        const taxId = row.find('.taxid').val()*1;

        const tax = qty * price * taxId/100;
        row.find('.prodtax').val(accounting.formatNumber(tax, 4));

        const amount = (qty * price) + tax;
        row.find('.amount').val(accounting.formatNumber(amount, 4));
        calcTotals();

        // required attr
        if ($(this).is('.qty')) row.find('.remark').attr('required', true);
    });
    // on amount change
    $('#productsTbl').on('change', '.amount', function() {
        const amount = $(this).val();
        $(this).val(accounting.formatNumber(amount, 4)).attr('required', true);
        calcTotals();
    });

    // set product rows
    const initProductRow = $('#productsTbl tbody tr:first').html();
    const initTitleRow = $('#productsTbl tbody tr:last').html();
    $('#productsTbl tbody tr').remove();
    const quoteItems = @json($products);
    quoteItems.forEach((v,i) => {
        // product type
        if (v.a_type == 1) {
            $('#productsTbl tbody').append(`<tr>${initProductRow}</tr>`);
            const el =  $('#productsTbl tbody tr:last');
            el.find('.index').val(i);
            el.find('.num').val(v.numbering);
            el.find('.prodname').val(v.product_name).autocomplete(productAutocomplete());
            el.find('.unit').val(v.unit);
            el.find('.qty').val(v.product_qty*1);

            const price = v.product_subtotal*1;
            el.find('.price').val(accounting.formatNumber(v.product_subtotal, 4));

            const taxId = @json($quote->tax_id)*1;
            el.find('.taxid').val(taxId);

            const lineTax = v.product_qty * price * taxId/100;
            el.find('.prodtax').val(accounting.formatNumber(lineTax, 4));

            const amount = (v.product_qty * price) + lineTax; 
            el.find('.amount').val(accounting.formatNumber(amount, 4));
            el.find('.itemid').val(v.id);
            el.find('.prodid').val(v.product_id);
        } else {
            $('#productsTbl tbody').append(`<tr>${initTitleRow}</tr>`);
            const el =  $('#productsTbl tbody tr:last');
            el.find('.index').val(i);
            el.find('.num').val(v.numbering);
            el.find('.prodname').val(v.product_name);
            el.find('.itemid').val(v.id);
        }
        if (i == quoteItems.length-1) calcTotals();
    });    

    // on add product
    $('#add-product').click(function() {
        $('#productsTbl tbody').append(`<tr>${initProductRow}</tr>`);
        const el =  $('#productsTbl tbody tr:last');
        el.find('.prodname').autocomplete(productAutocomplete());
        el.find('.index').val(el.index());
    });

    // on add title
    $('#add-title').click(function() {
        $('#productsTbl tbody').append(`<tr>${initTitleRow}</tr>`);
        const el =  $('#productsTbl tbody tr:last');
        el.find('.index').val(el.index());
    });

    // on clicking product row drop down menu
    $("#productsTbl").on("click", ".up, .down, .remove", function() {
        const row = $(this).parents("tr");
        if ($(this).is('.up')) row.insertBefore(row.prev());
        if ($(this).is('.down')) row.insertAfter(row.next());
        if ($(this).is('.remove')) {
            if (confirm('Are you sure?') && row.siblings().length) row.remove(); 
            calcTotals();           
        }
        // re-order indexes
        $("#productsTbl tbody tr").each(function(i) { $(this).find('.index').val(i) });
    });

    // totals
    function calcTotals() {
        let taxable = 0;
        let subtotal = 0;
        let tax = 0;
        let total = 0;
        $('#productsTbl tbody tr').each(function() {
            const qty = accounting.unformat($(this).find('.qty').val());
            const price = accounting.unformat($(this).find('.price').val());
            const taxId = accounting.unformat($(this).find('.taxid').val());
            const lineTotal = accounting.unformat($(this).find('.amount').val());

            subtotal += qty * price;
            total += lineTotal;
            if (taxId > 0) {
                taxable += qty * price;
                tax += qty * price * taxId/100;
            }
        });
        $('#taxable').val(accounting.formatNumber(taxable));
        $('#subtotal').val(accounting.formatNumber(subtotal));
        $('#tax').val(accounting.formatNumber(tax));        
        $('#total').val(accounting.formatNumber(total));
    }

    // product name autocomplete
    let focusProductRow;
    $('#productsTbl').on('keyup', '.prodname', function() {
        focusProductRow = $(this).parents('tr');
    });
    function productAutocomplete() {
        return {
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('biller.products.quote_product_search') }}",
                    method: 'POST',
                    data: {keyword: request.term},
                    success: result => response(result.map(v => ({
                        label: v.name,
                        value: v.name,
                        data: v
                    })))
                });
            },
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                const {data} = ui.item;
                const el = focusProductRow;

                el.find('.prodname').val(data.name);
                el.find('.unit').val(data.unit);
                el.find('.qty').val(1);

                const price = data.price*1;
                el.find('.price').val(accounting.formatNumber(price, 4));

                const taxId = @json($quote->tax_id);
                el.find('.taxid').val(taxId);
                const lineTax = price * taxId/100;
                el.find('.prodtax').val(accounting.formatNumber(lineTax, 4));

                const amount = price + lineTax; 
                el.find('.amount').val(accounting.formatNumber(amount, 4));
                el.find('.prodid').val(data.id);
                calcTotals();
            }
        };
    }
    
    /**
     * Equipments Logic
     **/
    // on change row type
    $('#jobcardsTbl').on('change', '.type', function() {
        const el = $(this).parents('tr');
        if ($(this).val() == 2) {
            // dnote row
            el.find('.jc_fault').addClass('invisible');
            el.find('.jc_equip').addClass('invisible');
            el.find('.jc_loc').addClass('invisible');
        } else {
            // jobcard row
            el.find('.jc_fault').addClass('invisible');
            el.find('.jc_equip').addClass('invisible');
            el.find('.jc_loc').addClass('invisible');
        }
    });
    
    // set product rows
    const initProductRow = $('#productsTbl tbody tr:first').html();
    $('#productsTbl tbody tr').remove();

    // add job card row
    $('#add-jobcard').click(function() {





        const i = jcIndex;
        $('#jobcardTbl tbody').append(jobCardRow(i));
        $('#equip-'+i).autocomplete(autocompleteEquip(i));
        $('#date-'+i).datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
        .datepicker('setDate', new Date());
        jcIndex++;
    });

    // remove job card row
    $('#jobcardTbl').on('click', '.removeJc', function() {
        const row = $(this).parents('tr:first');
        if (confirm('Are you sure ?')) row.remove();
    });

    // equipment autocomplete
    let focusEquipmentRow;
    $('#equipmentsTbl').on('keyup', '.jc_equip', function() {
        focusEquipmentRow = $(this).parents('tr');
    });
    function equipmentAutocomplete() {
        return {
            source: function(request, response) {
                $.ajax({
                    url: baseurl + 'equipments/search/' + $("#client_id").val(),
                    method: 'POST',
                    data: {
                        keyword: request.term, 
                        customer_id: "{{ $quote->customer_id }}",
                        branch_id: "{{ $quote->branch_id }}",
                    },
                    success: data => {
                        data = data.map(v => {
                            for (const key in v) {
                                if (!v[key]) v[key] = '';
                            }
                            const label = `${v.unique_id} ${v.equip_serial} ${v.make_type} ${v.model} ${v.machine_gas}
                                ${v.capacity} ${v.location} ${v.building} ${v.floor}`;
                            const value = v.unique_id;
                            const data = v;
                            return {label, value, data};
                        });
                        response(data);
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: (event, ui) => {
                const {data} = ui.item;
                const el = focusEquipmentRow;

                el.find('.jc_equipid').val(data.id);
                el.find('.jc_equip').val(data.make_type);
                el.find('.jc_loc').val(data.location);

                // $('#equipmentid-'+i).val(data.id);
                // $('#equip-'+i).val(data.make_type);
                // $('#location-'+i).val(data.location);
            }
        };
    }    
</script>
