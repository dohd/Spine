<script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});
    // html editor
    editor();

    const quote = '4'
    console.log(quote);
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
                        <option value="attachee">Attachee</option>
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
                <input type="hidden" class="row-index" name="row_index[]" value="${n}" id="rowindex-${n}">
                <input type="hidden" name="a_type[]" value="1" id="atype-${n}">                
            </tr>
        `;
    }

    // title row html
    function titleRow(n) {
        return `
            <tr>
                <td><input type="text" class="form-control" name="numbering[]" id="numbering-${n}" required></td>
                <td colspan="6"><input type="text" class="form-control" name="product_name[]" id="itemname-${n}"></td>
                <td>${dropDown()}</td>
                <input type="hidden" name="product_id[]" value="0" id="productid-${n}">
                <input type="hidden" name="item_id[]" value="0" id="itemid-${n}">
                <input type="hidden" class="form-control" name="product_qty[]" value="0" id="amount-${n}">               
                <input type="hidden" class="form-control" name="unit[]" id="unit-${n}">               
                <input type="hidden" class="form-control update" name="new_qty[]" value="0" id="newqty-${n}">
                <input type="hidden" class="form-control update" name="price[]" value="0" id="price-${n}">
                <input type="hidden" class="row-index" name="row_index[]" value="${n}" id="rowindex-${n}">
                <input type="hidden" name="a_type[]" value="2" id="atype-${n}">                
            </tr>
        `;
    }

    // On skill-item update
    $('#skill-item').on('change', '.update', function() {
        const id = $(this).attr('id');
        const i = id.split('-')[1]; 

        const labourCharge = $('#charge-'+i);
        const labourType = $('#skill-'+i);
         
        switch (labourType.val()) {
            case 'casual': labourCharge.val(200).attr('readonly', true); break;
            case 'contract': labourCharge.val(350).attr('readonly', true); break;
            case 'attachee': labourCharge.val(150).attr('readonly', true); break;
            case 'outsourced': labourCharge.attr('readonly', false); break;
        }
        
        const amount = $('#hours-'+i).val() * $('#notech-'+i).val() * $('#charge-'+i).val();
        $(this).parents('tr:first').find('span').text(accounting.formatNumber(amount));
        budgetTotal();
    });

</script>
