{{ Html::script(mix('js/dataTable.js')) }}
<script>    
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    };

    $.ajaxSetup(config.ajax);

    // Intialize datepicker
    $('.datepicker').datepicker(config.date);
    $('#reference-date').datepicker('setDate', new Date("{{ $quote->reference_date }}"));
    $('#date').datepicker('setDate', new Date("{{ $quote->date }}"));
    $('#date-0').datepicker('setDate', new Date());

    // set general remark
    $('#gen_remark').val(@json($quote->gen_remark));

    // reset Quote Verification 
    $('#reset-items').click(function() {
        swal({
            title: 'Are you sure to reset all previously verified items ?',
            icon: "warning",
            buttons: true,
            dangerMode: true,
            showCancelButton: true,
        }, () => {
            $.ajax({
                url: baseurl + 'quotes/reset_verified/' + "{{ $quote->id }}",
                success: () => location.reload(),
            })
        });
    });

    // check if quote_total not equal to verified_amount
    $(function() {
        const total = accounting.unformat($('#total').val());
        const quoteTotal = accounting.unformat($('#quote_total').val());
        if (total != quoteTotal) $('#gen_remark').attr('required', true);
        else $('#gen_remark').attr('required', false);
    });
    $('#total').change(function() {
        const total = accounting.unformat($(this).val());
        const quoteTotal = accounting.unformat($('#quote_total').val());
        if (total != quoteTotal) $('#gen_remark').attr('required', true);
        else $('#gen_remark').attr('required', false);
    });

    // job card row
    function jobCardRow(n) {
        return `
            <tr>
                <td>
                    <select class="custom-select type" name="type[]" id="type-${n}">
                        <option value="1" selected>Jobcard</option>
                        <option value="2">DNote</option> 
                    </select>
                </td>
                <td><input type="text" class="form-control" name="reference[]" id="reference-${n}" required></td>
                <td><input type="text" class="form-control datepicker" name="date[]" id="date-${n}" required></td>
                <td><input type="text" class="form-control" name="technician[]" id="technician-${n}" required></td>
                <td><textarea class="form-control equip" name="equipment[]" id="equip-${n}"></textarea>
                <td><input type="text location" class="form-control" name="location[]" id="location-${n}"></td>
                <td>
                    <select class="custom-select fault" name="fault[]" id="fault-${n}">
                        <option value="none">None</option>
                        <option value="faulty_compressor">Faulty Compressor</option>
                        <option value="faulty_pcb">Faulty PCB</option>
                        <option value="leakage_arrest">Leakage Arrest</option>
                        <option value="electrical_fault">Electrical Fault</option>
                        <option value="drainage">Drainage</option>
                        <option value="other">Other</option>
                    </select>
                </td>
                <td><a href="javascript:" class="btn btn-primary btn-md removeJc" type="button">Remove</a></td>
                <input type="hidden" name="jcitem_id[]" value="0" id="jcitemid-${n}">
                <input type="hidden" name="equipment_id[]" value="0" id="equipmentid-${n}">
            </tr>
        `;
    }

    // on change type
    $('#jobcardTbl').on('change', '.type', function() {
        let i = $(this).attr('id').split('-')[1];
        if ($(this).val() == 2) {
            $('#fault-'+i).addClass('invisible');
            $('#equip-'+i).addClass('invisible');
            $('#location-'+i).addClass('invisible');
        } else {
            $('#fault-'+i).removeClass('invisible');
            $('#equip-'+i).removeClass('invisible');
            $('#location-'+i).removeClass('invisible');
        }
    });
    
    // addjob card row
    let jcIndex = 0;
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

    // jobcards
    const jobcards = @json($jobcards);
    jobcards.forEach((v, i) => {
        jcIndex++;
        $('#jobcardTbl tbody').append(jobCardRow(i));                    
        $('#jcitemid-'+i).val(v.id);
        $('#reference-'+i).val(v.reference);
        $('#type-'+i).val(v.type);
        $('#technician-'+i).val(v.technician);
        $('#equip-'+i).autocomplete(autocompleteEquip(i)).val(v.equipment?.make_type);
        $('#equipmentid-'+i).val(v.equipment_id);
        $('#location-'+i).val(v.equipment?.location);
        $('#fault-'+i).val(v.fault)
        $('#date-'+i).datepicker({ format: "{{ config('core.user_date_format') }}" })
        .datepicker('setDate', new Date(v.date));
        // hidden dnote fields 
        if (v.type == 2) {
            $('#equip-'+i).addClass('invisible');
            $('#location-'+i).addClass('invisible');
            $('#fault-'+i).addClass('invisible');
        }
    });

    // row dropdown menu
    function dropDown() {
        return `
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Action
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item up" href="javascript:void(0);">Up</a>
                    <a class="dropdown-item down" href="javascript:void(0);">Down</a>
                    <a class="dropdown-item removeProd text-danger" href="javascript:void(0);">Remove</a>
                </div>
            </div>            
        `;
    }

    // product row
    function productRow(val) {
        return `
            <tr>
                <td><input type="text" class="form-control" name="numbering[]" id="numbering-${val}" autocomplete="off"></td>
                <td><input type="text" class="form-control" name="product_name[]" placeholder="{{trans('general.enter_product')}}" id='itemname-${val}'></td>
                <td><input type="text" class="form-control" name="unit[]" id="unit-${val}" value=""></td>                
                <td><input type="text" class="form-control qty" name="product_qty[]" id="qty-${val}" autocomplete="off"></td>
                <td><input type="text" class="form-control rate" name="product_subtotal[]" id="rate-${val}" autocomplete="off"></td>
                <td><input type="text" class="form-control price" name="product_price[]" id="price-${val}" autocomplete="off" readonly></td>
                <td><strong><span class="amount" id="amount-${val}">0</span></strong></td>
                <td><textarea class="form-control remark" name="remark[]" id="remark-${val}"></textarea></td>
                <td class="text-center">${dropDown()}</td>
                <input type="hidden" name="item_id[]" value="0" id="itemid-${val}">
                <input type="hidden" name="product_id[]" value=0 id="productid-${val}">
                <input type="hidden" class="rowindx" name="row_index[]" value="0" id="rowindex-${val}">
                <input type="hidden" name="a_type[]" value="1" id="atype-${val}">
                <input type="hidden" class="taxrate" name="tax_rate[]" value="0" id="taxrate-${val}">
            </tr>
        `;
    }

    // product title row
    function productTitleRow(val) {
        return `
            <tr>
                <td><input type="text" class="form-control" name="numbering[]" id="numbering-${val}" autocomplete="off" ></td>
                <td colspan="7"><input type="text"  class="form-control" name="product_name[]" id="itemname-${val}" placeholder="Enter Title Or Heading"></td>
                <td class="text-center">${dropDown()}</td>
                <input type="hidden" name="remark[]" id="remark-${val}">
                <input type="hidden" name="item_id[]" value="0" id="itemid-${val}">
                <input type="hidden" name="product_id[]" value="${val}" id="productid-${val}">
                <input type="hidden" name="unit[]" value="">
                <input type="hidden" name="product_qty[]" value="0">
                <input type="hidden" name="product_price[]" value="0">
                <input type="hidden" name="product_subtotal[]" value="0">
                <input type="hidden" class="rowindx" name="row_index[]" value="0" id="rowindex-${val}">
                <input type="hidden" name="a_type[]" value="2" id="atype-${val}">
                <input type="hidden" class="taxrate" name="tax_rate[]" value="0" id="taxrate-${val}">
            </tr>
        `;
    }

    // on change qty, rate
    $('#productsTbl').on('change', '.qty, .rate', function() {
        const row = $(this).parents('tr');
        const qty = accounting.unformat(row.find('.qty').val());
        const rate = accounting.unformat(row.find('.rate').val());

        const tax = @json($quote->tax_id);
        const price = rate * (tax/100 + 1);
        const amount = price * qty;

        row.find('.price').val(accounting.formatNumber(price, 4));
        row.find('.amount').text(accounting.formatNumber(amount, 4));
        calcTotals();
    });

    // set default product rows
    let rowIndx = 0;
    const quoteItems = @json($products);
    quoteItems.forEach(v => {
        const i = rowIndx;
        const item = {...v};
        // format float values to integer
        for (let prop in item) {
            let keys = ['product_price', 'product_qty', 'product_subtotal'];
            if (keys.includes(prop) && item[prop]) {
                item[prop] = accounting.unformat(item[prop]);
            }
        }
        // check if item type is product
        if (item.a_type == 1) {
            $('#productsTbl tbody').append(productRow(rowIndx));
            $('#itemname-'+rowIndx).autocomplete(autocompleteProp(rowIndx));
            // set default values
            $('#itemid-'+i).val(item.id);
            $('#productid-'+i).val(item.product_id);
            $('#numbering-'+i).val(item.numbering);
            $('#itemname-'+i).val(item.product_name);
            $('#unit-'+i).val(item.unit); 
            $('#remark-'+i).val(item.remark);
            $('#taxrate-'+i).val(item.tax_rate);
            $('#qty-'+i).val(accounting.formatNumber(item.product_qty));
            $('#rate-'+i).val(accounting.formatNumber(item.product_subtotal, 4)).attr('readonly', true);
            $('#price-'+i).val(accounting.formatNumber(item.product_price, 4));                
            $('#amount-'+i).text(accounting.formatNumber(item.product_qty * item.product_price, 4));
        } else {
            $('#productsTbl tbody').append(productTitleRow(rowIndx));
            // set default values
            $('#itemid-'+i).val(item.id);
            $('#productid-'+i).val(item.product_id);
            $('#numbering-'+i).val(item.numbering);
            $('#itemname-'+i).val(item.product_name);
        }
        rowIndx++;        
    });    
    calcTotals();

    // On click Add Product
    $('#add-product').click(function() {
        $('#productsTbl tbody').append(productRow(rowIndx));
        $('#itemname-'+rowIndx).autocomplete(autocompleteProp(rowIndx));
        rowIndx++;
        calcTotals();
    });
    // on click Add Title button
    $('#add-title').click(function() {
        $('#productsTbl tbody').append(productTitleRow(rowIndx));
        rowIndx++;
        calcTotals();
    });

    // on clicking Product row drop down menu
    $("#productsTbl").on("click", ".up, .down, .removeProd", function() {
        const row = $(this).parents("tr:first");
        if ($(this).is('.up')) row.insertBefore(row.prev());
        if ($(this).is('.down')) row.insertAfter(row.next());
        if ($(this).is('.removeProd')) {
            if (confirm('Are you sure to delete this item?')) row.remove();            
        }
        calcTotals();
    });

    // totals
    function calcTotals() {
        let taxable = 0;
        let subtotal = 0;
        let total = 0;
        $('#productsTbl tbody tr').each(function(i) {
            $(this).find('.rowindx').val(i);
            const qty = accounting.unformat($(this).find('.qty').val());
            const rate = accounting.unformat($(this).find('.rate').val());
            const amount = accounting.unformat($(this).find('.amount').text());
            const taxRate = accounting.unformat($(this).find('.taxrate').val());
            if (taxRate > 0) taxable += qty * rate;
            subtotal += qty * rate;
            total += amount;
        });
        $('#taxable').val(accounting.formatNumber(taxable));
        $('#subtotal').val(accounting.formatNumber(subtotal));
        $('#tax').val(accounting.formatNumber(total - subtotal));        
        $('#total').val(accounting.formatNumber(total)).change();
    }

    // product autocomplete
    function autocompleteProp(i) {
        return {
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('biller.products.quote_product_search') }}",
                    method: 'POST',
                    data: {
                        keyword: request.term,
                    },
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
                $('#productid-'+i).val(data.id);
                $('#itemname-'+i).val(data.name);
                $('#unit-'+i).val(data.unit);                
                $('#qty-'+i).val(1);

                const currency = @json($quote->currency);
                if (currency && parseFloat(currency['rate']) > 1) {
                    data.price = parseFloat(data.price) / parseFloat(currency['rate']);
                }

                const rate = accounting.unformat(data.price);
                const taxRate = @json($quote->tax_id);
                const amount = rate * (taxRate/100 + 1);
                $('#taxrate-'+i).val(taxRate);
                $('#rate-'+i).val(accounting.formatNumber(rate, 4)).attr('readonly', true);
                $('#price-'+i).val(accounting.formatNumber(amount, 4));                
                $('#amount-'+i).text(accounting.formatNumber(amount, 4));
                calcTotals();
            }
        };
    }
    
    // equipment autocomplete
    function autocompleteEquip(i) {
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
                $('#equipmentid-'+i).val(data.id);
                $('#equip-'+i).val(data.make_type);
                $('#location-'+i).val(data.location);
            }
        };
    }    
</script>
