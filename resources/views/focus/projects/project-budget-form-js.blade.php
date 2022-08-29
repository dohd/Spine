{{-- Create Form --}}
<script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});
    // html editor
    editor();

    const quote = @json($quote);
    $('.datepicker').datepicker({ format: "{{ config('core.user_date_format') }}" })
    if (quote.date) $('#date').datepicker('setDate', new Date(quote.date));

    // skill row html
    function skillRow(n) {
        return `
            <tr>
                <td class="text-center">${n+1}</td>
                <td>
                    <select class="form-control update" name="skill[]" id="skill-${n}" required>
                        <option value="" class="text-center">-- Select Skill Type --</option>                        
                        <option value="casual">Casual</option>
                        <option value="contract">Contract</option>
                        <option value="outsourced">Outsourced</option>
                    </select>
                </td>
                <td><input type="number" class="form-control update" name="charge[]" id="charge-${n}" required readonly></td>
                <td><input type="number" class="form-control update" name="hours[]" id="hours-${n}" required></td>               
                <td><input type="number" class="form-control update" name="no_technician[]" id="notech-${n}" required></td>
                <td class="text-center"><span>0</span></td>
                <td><button type="button" class="btn btn-primary removeItem">Remove</button></td>
                <input type="hidden" name="skillitem_id[]" value="0" id="skillitemid-${n}">
            </tr>
        `;
    }

    // row dropdown menu
    function dropDown(n) {
        return `
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Action
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item up" href="javascript:void(0);">Up</a>
                    <a class="dropdown-item down" href="javascript:void(0);">Down</a>
                    <a class="dropdown-item removeItem text-danger" href="javascript:void(0);">Remove</a>
                </div>
            </div>            
        `;
    }

    // product row html
    function productRow(n) {
        return `
            <tr>
                <td><input type="text" class="form-control no" name="numbering[]" id="numbering-${n}" required></td>
                <td><input type="text" class="form-control name" name="product_name[]" id="itemname-${n}" required></td>
                <td><input type="number" class="form-control qty" name="product_qty[]" value="0" id="amount-${n}" readonly></td>                
                <td>
                    <div class="row no-gutters">
                        <div class="col-6"><input type="text" class="form-control unit" name="unit[]" id="unit-${n}"></div>
                        <div class="col-6">
                            <select type="text" class="custom-select unit-select" name="unit[]" id="unitselect-${n}">
                                <option value="">None</option>
                            </select>
                        </div>
                    </div>
                </td>                
                <td><input type="text" class="form-control new-qty" name="new_qty[]" id="newqty-${n}" required></td>
                <td><input type="text" class="form-control price" name="price[]" id="price-${n}" required></td>
                <td class="text-center amount">0</td>
                <td>${dropDown()}</td>
                <input type="hidden" name="product_id[]" value="0" id="productid-${n}">
                <input type="hidden" name="item_id[]" value="0" id="itemid-${n}">
                <input type="hidden" name="row_index[]" value="${n}" id="rowindex-${n}">
                <input type="hidden" name="a_type[]" value="1" id="atype-${n}">                
            </tr>
        `;
    }

    // title row html
    function titleRow(n) {
        return `
            <tr>
                <td><input type="text" class="form-control" name="numbering[]" id="numbering-${n}" required></td>
                <td colspan="7"><input type="text" class="form-control" name="product_name[]" id="itemname-${n}" readonly></td>
                <input type="hidden" name="product_id[]" value="0" id="productid-${n}">
                <input type="hidden" name="item_id[]" value="0" id="itemid-${n}">
                <input type="hidden" class="form-control" name="product_qty[]" value="0" id="amount-${n}">               
                <input type="hidden" class="form-control" name="unit[]" id="unit-${n}">               
                <input type="hidden" class="form-control update" name="new_qty[]" value="0" id="newqty-${n}">
                <input type="hidden" class="form-control update" name="price[]" value="0" id="price-${n}">
                <input type="hidden" name="row_index[]" value="${n}" id="rowindex-${n}">
                <input type="hidden" name="a_type[]" value="2" id="atype-${n}">                
            </tr>
        `;
    }

    // On skill-item update
    $('#skill-item').on('change', '.update', function() {
        if (!$(this).val()) $(this).val(0);

        const id = $(this).attr('id');
        const rowIndx = id.split('-')[1]; 

        const $input = $('#charge-'+rowIndx).attr('readonly', true);
        switch ($('#skill-'+rowIndx).val()) {
            case 'casual': $input.val(200); break;
            case 'contract': $input.val(350); break;
            case 'outsourced': $input.attr('readonly', false); break;
        }
        const hr = $('#hours-'+rowIndx).val();
        const notech = $('#notech-'+rowIndx).val();
        const charge = $('#charge-'+rowIndx).val();

        const amount = charge * hr * notech;
        const amountStr = amount.toLocaleString();
        $(this).parents('tr:first').find('span').text(amountStr);

        budgetTotal();
    });

    // default skill row
    let skillIndx = 0;
    const skillItems = @json($quote->skill_items);
    if (skillItems.length) {
        skillItems.forEach(v => {
            let i = skillIndx;
            $('#skill-item tbody').append(skillRow(i));
            $('#skillitemid-'+i).val(v.id);
            $('#skill-'+i).val(v.skill);
            $('#charge-'+i).val(v.charge);
            $('#hours-'+i).val(v.hours);
            $('#notech-'+i).val(v.no_technician);
            skillIndx++;
        });
        $('#charge-0').change();
    } else $('#skill-item tbody').append(skillRow(0));
    // on adding skill
    $('#add-skill').click(function() {
        $('#skill-item tbody').append(skillRow(skillIndx));
        skillIndx++;
    });
    // Remove skill row
    $('#skill-item').on('click', '.removeItem', function() {
        $(this).closest('tr').remove();
        budgetTotal();
    });

    // products table change
    $('#productsTbl').on('change', '.unit, .unit-select, .new-qty, .price', function() {
        const el = $(this);
        const row = el.parents('tr:first');

        const newQty = accounting.unformat(row.find('.new-qty').val());
        const price = accounting.unformat(row.find('.price').val());
        const amount = newQty * price;

        row.find('.new-qty').val(newQty);
        row.find('.price').val(accounting.formatNumber(price));
        row.find('.amount').text(accounting.formatNumber(amount));
        budgetTotal();

        if (el.is('.unit')) {
            if (el.val()) row.find('.unit-select').attr('disabled', true);
            else row.find('.unit-select').attr({
                disabled: false,
                required: true
            });
        }
        if (el.is('.unit-select')) {
            if (el.val()) {
                const purchasePrice = el.find('option:selected').attr('purchase_price');
                row.find('.price').val(purchasePrice).change();
                row.find('.unit').attr('disabled', true);
            } 
            else row.find('.unit').attr({
                disabled: false,
                required: true
            });
        }
    });

    // set default product rows
    let productIndx = 0;
    const quoteItems = @json($quote->products()->orderByRow()->get());  
    quoteItems.forEach(v => {
        let i = productIndx;
        if (v.a_type == 1) {
            // product type
            $('#productsTbl tbody').append(productRow(i));
            $('#itemname-'+i).autocomplete(autocompleteProp(i));

            $('#numbering-'+i).val(v.numbering);
            $('#itemid-'+i).val(v.id);
            $('#productid-'+i).val(v.product_id);
            $('#itemname-'+i).val(v.product_name);
            $('#amount-'+i).val(parseFloat(v.product_qty));
            $('#newqty-'+i).val(parseFloat(v.estimate_qty));
            $('#unit-'+i).val(v.unit).change();
            $('#price-'+i).val(accounting.formatNumber(v.buy_price)).change();
        } else if (v.a_type == 2) {
            // title type
            $('#productsTbl tbody').append(titleRow(i));
            $('#numbering-'+i).val(v.numbering);
            $('#itemname-'+i).val(v.product_name);
        }
        productIndx++;
    });

    // add product row
    $('#add-product').click(function() {
        const i = productIndx;
        $('#productsTbl tbody').append(productRow(i));
        $('#itemname-'+i).autocomplete(autocompleteProp(i));
        productIndx++;
    });
    // modify product row
    $('#productsTbl').on('click', '.up, .down, .removeItem', function() {
        const $row = $(this).parents("tr:first");
        if ($(this).is('.up')) $row.insertBefore($row.prev());
        if ($(this).is('.down')) $row.insertAfter($row.next());        
        if ($(this).is('.removeItem')) $(this).closest('tr').remove();
        budgetTotal();
    });

    // autocompleteProp returns autocomplete object properties
    function autocompleteProp(i) {
        return {
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('biller.products.quote_product_search') }}",
                    method: 'POST',
                    data: 'keyword=' + request.term,
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
            select: function(event, ui) {
                const {data} = ui.item;
                $('#productid-'+i).val(data.id);
                $('#itemname-'+i).val(data.name);
                $('#unit-'+i).val('').change();    
                
                const purchasePrice = parseFloat(data.purchase_price);
                $('#price-'+i).val(accounting.formatNumber(purchasePrice)).change();

                $('#unitselect-' + i + ' option:not(:eq(0))').remove();
                data.units.forEach(v => {
                    const rate = parseFloat(v.base_ratio) * purchasePrice;
                    const option = `<option value="${v.code}" purchase_price="${rate}">${v.code}</option>`;
                    $('#unitselect-'+i).append(option);
                });
            }
        };
    }

    // total budget
    function budgetTotal() {
        let total = 0;
        let labourTotal = 0;

        $('#productsTbl tbody tr').each(function() {
            const row = $(this);
            const amount = accounting.unformat(row.find('.amount').text());
            total += amount;
        });
        $('#skill-item tbody tr').each(function() {
            const spanText = $(this).find('td').eq(5).children().text();
            const amount = parseFloat(spanText.replace(/,/g, ''));
            total += amount;
            labourTotal += amount;
        });

        $('#budget-total').val(accounting.formatNumber(total));
        $('#labour-total').val(accounting.formatNumber(labourTotal));

        // profit
        const quoteTotal = accounting.unformat($('#quote_total').val());
        const profit = quoteTotal - total;
        const pcent = Math.round(profit/quoteTotal * 100);
        $('.profit').text(profit.toLocaleString() + ' : ' + pcent + '%');

        // budget limit
        $('.budget-alert').addClass('d-none');
        if (total > (quoteTotal * 0.7)) {
            $('.budget-alert').removeClass('d-none');
            scroll(0, 0);
        }
    }
</script>

{{-- Edit Form --}}
<script>
    const budget = @json(@$budget);
    if (budget) {
        // default skill row
        skillIndx = 0;
        $('#skill-item tbody tr').remove();
        const skillsets = @json(@$budget->skillsets);
        skillsets.forEach(v => {
            const i = skillIndx;
            $('#skill-item tbody').append(skillRow(i));
            $('#skillitemid-'+i).val(v.id);
            $('#skill-'+i).val(v.skill);
            $('#charge-'+i).val(v.charge);
            $('#hours-'+i).val(v.hours);
            $('#notech-'+i).val(v.no_technician).change();
            skillIndx++;
        });

        // set default product rows
        productIndx = 0;
        $('#productsTbl tbody tr').remove();
        const budgetItems = @json(@$budget_items);
        budgetItems.forEach(v => {
            const i = productIndx;
            if (v.a_type == 1) {
                // product type
                $('#productsTbl tbody').append(productRow(i));
                $('#itemname-'+i).autocomplete(autocompleteProp(i));

                $('#numbering-'+i).val(v.numbering);
                $('#itemid-'+i).val(v.id);
                $('#productid-'+i).val(v.product_id);
                $('#itemname-'+i).val(v.product_name);
                $('#amount-'+i).val(parseFloat(v.product_qty));
                $('#newqty-'+i).val(parseFloat(v.new_qty));
                $('#unit-'+i).val(v.unit).change();
                $('#price-'+i).val(accounting.formatNumber(v.price)).change();
            } else if (v.a_type == 2) {
                // title type
                $('#productsTbl tbody').append(titleRow(i));
                $('#numbering-'+i).val(v.numbering);
                $('#itemname-'+i).val(v.product_name);
                $('#itemid-'+i).val(v.id);
            }
            productIndx++;
        });
    }
</script>