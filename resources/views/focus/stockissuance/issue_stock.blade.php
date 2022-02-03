@extends('core.layouts.app')

@section('title', 'Stock Issuance')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="alert alert-warning col-12 d-none budget-alert" role="alert">
            <strong>Budget Limit Exceeded!</strong> You should check on your list items.
        </div>
    </div>
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="content-header-title">Stock Issuance</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    <a href="{{ route('biller.stockissuance.index') }}" class="btn btn-primary">
                        <i class="ft-list"></i> List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-body">                
                {{ Form::model($quote) }}
                <input type="hidden" name="quote_id" value="{{ $quote->id }}">
                <div class="form-group row">
                    <div class="col-12">
                        <h3 class="title">
                            {{ $quote->bank_id ? 'Budgeted Proforma Invoice' : 'Budgeted Quote' }}
                        </h3>                                        
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12">
                        <label for="subject" class="caption">Subject / Title</label>
                        {{ Form::text('notes', null, ['class' => 'form-control', 'id'=>'subject', 'disabled']) }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 cmp-pnl">
                        <div id="customerpanel" class="inner-cmp-pnl">                        
                            <div class="form-group row">                                  
                                <div class="col-4">
                                    <label for="invoiceno" class="caption">                                        
                                        {{ $quote->bank_id ? '#PI Serial No' : '#QT Serial No' }}
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                                        @php
                                            $tid = sprintf('%04d', $quote->tid);
                                            $tid = $quote->bank_id ? 'PI-'.$tid : 'QT-'.$tid;                                             
                                        @endphp
                                        {{ Form::text('tid', $tid, ['class' => 'form-control round', 'disabled']) }}
                                    </div>
                                </div>
                                <div class="col-4"><label for="invoicedate" class="caption">Quote {{trans('general.date')}}</label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                        {{ Form::text('invoicedate', null, ['class' => 'form-control round datepicker', 'disabled']) }}
                                    </div>
                                </div>                                                                
                                <div class="col-4"><label for="client_ref" class="caption">Client Reference / Callout ID</label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                        {{ Form::text('client_ref', null, ['class' => 'form-control round', 'id' => 'client_ref', 'disabled']) }}
                                    </div>
                                </div> 
                            </div> 
                        </div>
                    </div>
                </div>                    

                <div>                            
                    <table id="budget-item" class="table-responsive tfr my_stripe_single mb-1">
                        <thead>
                            <tr class="item_header bg-gradient-directional-blue white">
                                <th width="6%" class="text-center">#</th>
                                <th width="38%" class="text-center">Name</th>
                                <th width="6%" class="text-center">Quoted Qty</th>                                
                                <th width="8%" class="text-center">UOM</th>
                                <th width="6%" class="text-center">Approve Qty</th>     
                                <th width="14%" class="text-center">Buy Price (VAT Exc)</th>
                                <th width="8%" class="text-center">Amount</th>
                                <th width="6%" class="text-center">Issued Qty</th> 
                                <th width="10%" class="text-center">Qty</th>  
                                <th width="7%" class="text-center">Action</th>                             
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <div class="row mb-1">
                        <div class="col-12 payment-method last-item-row sub_c">
                            <button type="button" class="btn btn-success" id="add-product">
                                <i class="fa fa-plus-square"></i> Add Item
                            </button>
                        </div>                            
                    </div>
                    <div class="row mb-1">
                        <div class="col-8">
                            <table id="skill-item" class="table-responsive tfr my_stripe_single">
                                <thead>
                                    <tr class="item_header bg-gradient-directional-blue white">
                                        <th class="text-center">#</th>
                                        <th width="20%" class="text-center">Skill Type</th>
                                        <th width="15%" class="text-center">Charge</th>
                                        <th width="15%" class="text-center">Working Hrs</th>
                                        <th width="15%" class="text-center">No. Technicians</th> 
                                        <th width="15%" class="text-center">Amount</th>
                                        <th width="10%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <button type="button" class="btn btn-success mt-1" id="add-skill" disabled>
                                <i class="fa fa-plus-square"></i> Add Skill
                            </button>
                            <div class="form-group float-right mt-1">
                                <div><label for="budget-total">Total Amount</label></div>
                                <div><input type="text" value="0" class="form-control" id="labour-total" name="labour_total" readonly></div>
                            </div>
                        </div>  
                        <div class="col-4">
                            <div class="form-group">
                                <div><label for="tool">Tools Required & Notes</label></div>
                                <textarea name="tool" id="tool" cols="45" rows="6" class="form-control html_editor">                                    
                                    {{ $budget->tool }}
                                </textarea>   
                            </div>                                                     
                            <div class="form-group">
                                <div>
                                    <label for="quote-total">Total Quote</label>
                                    <span class="text-danger">(VAT Exc)</span>
                                </div>
                                <input type="text" class="form-control" id="quote-total" name="quote_total" readonly>
                            </div>
                            <div class="form-group">
                                <div>
                                    <label for="budget-total">Total Budget</label>&nbsp;
                                    <span class="text-primary font-weight-bold">
                                        (Profit: &nbsp;<span class="text-dark profit">0</span>)
                                    </span>
                                </div>
                                <input type="text" value="0" class="form-control" id="budget-total" name="budget_total" readonly>
                            </div>                            
                            {{ Form::submit('Generate', ['class' => 'btn btn-success btn-lg', 'id' => 'submit']) }}
                        </div>                              
                    </div>
                </div>
                {{ Form::close() }}
            </div>             
        </div>
    </div>
</div>
@include('focus.stockissuance.issuance_log');
@endsection

@section('extra-scripts')
<script>
    // ajax setup
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});

    // initialize html editor
    editor();

    // set default values
    const subtotal = @json($quote->subtotal);
    $('#quote-total').val(parseFloat(subtotal).toLocaleString());
    $('#submit').css('visibility', 'hidden');
    $('#add-skill').css('visibility', 'hidden');
    
    // initialize Quote Date datepicker
    $('.datepicker')
        .datepicker({ format: "{{ config('core.user_date_format') }}" })
        .datepicker('setDate', new Date("{{ $quote->invoicedate }}"));

    // skill row html
    function skillRow(n) {
        return `
            <tr>
                <td>${n+1}</td>
                <td>
                    <select class="form-control update" name="skill[]" id="skill-${n}" readonly>
                        <option value="" class="text-center">-- Select Skill Type --</option>                        
                        <option value="casual">Casual</option>
                        <option value="contract">Contract</option>
                        <option value="outsourced">Outsourced</option>
                    </select>
                </td>
                <td><input type="number" class="form-control update" name="charge[]" id="charge-${n}" required readonly></td>
                <td><input type="number" class="form-control update" name="hours[]" id="hours-${n}" required readonly></td>               
                <td><input type="number" class="form-control update" name="no_technician[]" id="notech-${n}" required readonly></td>
                <td class="text-center"><span>0</span></td>
                <td><button type="button" class="btn btn-primary removeItem" disabled>Remove</button></td>
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
                    <a class="dropdown-item issueItem" href="javascript:void(0);">Issue</a>
                    <a class="dropdown-item saveItem" href="javascript:void(0);">Save</a>
                    <a class="dropdown-item up" href="javascript:void(0);">Move Up</a>
                    <a class="dropdown-item down" href="javascript:void(0);">Move Down</a>
                    <a class="dropdown-item removeItem text-danger" href="javascript:void(0);">Delete</a>
                    <a class="dropdown-item logItem" href="javascript:void(0);" data-toggle="modal" data-target="#issuanceLog">
                        View Log
                    </a>
                </div>
            </div>            
        `;
    }

    // product row html
    function productRow(n) {
        return `
            <tr>
                <td><input type="text" class="form-control" name="numbering[]" id="numbering-${n}" required></td>
                <td><input type="text" class="form-control" name="product_name[]" id="itemname-${n}" required></td>
                <td><input type="number" class="form-control" name="product_qty[]" value="0" id="amount-${n}" readonly></td>                
                <td><input type="text" class="form-control" name="unit[]" id="unit-${n}" required></td>                
                <td><input type="number" class="form-control update newqty" name="new_qty[]" value="0" id="newqty-${n}" readonly></td>
                <td><input type="text" class="form-control update" name="price[]" id="price-${n}" required></td>
                <td class="text-center"><span>0</span></td>
                <td><input type="number" class="form-control issued" name="issued_qty" id="issuedqty-${n}" disabled></td>
                <td><input type="number" class="form-control issue" name="issue_qty[]" id="issueqty-${n}"></td>
                <td>${dropDown()}</td>
                <input type="hidden" name="product_id[]" value="0" id="productid-${n}">
                <input type="hidden" name="item_id[]" value="0" id="itemid-${n}">
                <input type="hidden" name="row_index[]" value="${n}" id="rowindex-${n}">
                <input type="hidden" name="a_type[]" value="1" id="atype-${n}">                
                <input type="hidden" name="budget_id[]" value="{{ $budget->id }}" id="budgetid-${n}">
            </tr>
        `;
    }

    // title row html
    function titleRow(n) {
        return `
            <tr>
                <td><span id="number-${n}"></span></td>
                <td colspan="9"><input type="text" class="form-control" name="product_name[]" id="itemname-${n}" readonly></td>
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
        $(this).parentsUntil('tbody').eq(1).children().eq(5).children().text(amountStr);

        calcBudget();
    });

    // default skill row
    let skillIndx = 0;
    const skillsets = @json($budget->skillsets);
    skillsets.forEach(v => {
        const i = skillIndx;
        $('#skill-item tbody').append(skillRow(i));
        $('#skill-'+i).val(v.skill);
        $('#charge-'+i).val(v.charge);
        $('#hours-'+i).val(v.hours);
        $('#notech-'+i).val(v.no_technician).change();
        skillIndx++;
    });

    // Dropdown menu condition
    $('#budget-item').on('click', '#dropdownMenuButton', function() {
        const $dropDown = $(this).next();
        $dropDown.find('.issueItem').addClass('d-none');
        $dropDown.find('.saveItem').addClass('d-none');
        $dropDown.find('.logItem').addClass('d-none');

        const i = $(this).parents('tr:first').index();
        const productId = $('#productid-'+i).val();
        const itemId = $('#itemid-'+i).val();
        // if new item, enable save
        if (itemId == 0) 
            return $dropDown.find('.saveItem').removeClass('d-none');
        // if not stock item or issued qty is equal to approved qty 
        // disable save and issue
        if (productId == 0) return;
        // default enable issue
        $dropDown.find('.issueItem').removeClass('d-none');
        $dropDown.find('.logItem').removeClass('d-none');
        $dropDown.find('.removeItem').addClass('d-none');
    });

    // Issuance condition
    $('#budget-item').on('change', '.issue', function() {        
        let aprvQty = $(this).parents('tr:first').find('.newqty').val();
        aprvQty = Number(aprvQty);
        if (!aprvQty) return;

        const issuedQty = $(this).parents('tr:first').find('.issued').val();
        if (issuedQty == aprvQty) $(this).attr('disabled', true);

        if (Number($(this).val()) > aprvQty) $(this).val(aprvQty);
    });

    // On budget-item update
    $('#budget-item').on('change', '.update', function() {
        if (!$(this).val()) $(this).val(0);

        const id = $(this).attr('id');
        const rowIndx = id.split('-')[1];        
        const price = $('#price-'+rowIndx).val().replace(/,/g, '');
        let qty = $('#newqty-'+rowIndx).val();
        if (qty == 0) qty = 1;

        const amount = qty * parseFloat(price);
        const amountStr = amount.toLocaleString();
        if (id.includes('price')) {
            const n = $(this).val().replace(/,/g, '');
            $(this).val(parseFloat(n).toLocaleString());
            $(this).parent().next().children().text(amountStr);
        } else if (price) {
            $(this).parent().next().next().children().text(amountStr);
        }

        calcBudget();
    });

    // set default product rows
    let productIndx = 0;
    const budgetItems = @json($budget->products()->orderByRow()->get());    
    budgetItems.forEach(v => {
        const i = productIndx;
        // check type if item type is product else assign title
        if (v.a_type === 1) {
            $('#budget-item tbody').append(productRow(i));
            $('#itemname-'+i).autocomplete(autocompleteProp(i));
            // set default values
            $('#numbering-'+i).val(v.numbering);
            $('#itemid-'+i).val(v.id);
            $('#productid-'+i).val(v.product_id);
            $('#itemname-'+i).val(v.product_name);
            $('#unit-'+i).val(v.unit);                
            $('#amount-'+i).val(parseFloat(v.product_qty));
            $('#newqty-'+i).val(parseFloat(v.new_qty));
            $('#price-'+i).val(v.price).change();
            if (v.issue_qty) $('#issuedqty-'+i).val(v.issue_qty);  
        } else {
            $('#budget-item tbody').append(titleRow(i));
            $('#itemname-'+i).val(v.product_name);
            $('#numbering-'+i).val(v.numbering);
        }
        productIndx++;
    });

    // add product row
    $('#add-product').click(function() {
        const i = productIndx;
        $('#budget-item tbody').append(productRow(i));
        // autocomplete on added product row
        $('#itemname-'+i).autocomplete(autocompleteProp(i));
        productIndx++;
    });
    // remove product row
    $('#budget-item').on('click', '.removeItem, .up, .down', function() {
        const $row = $(this).parents("tr:first");
        if ($(this).is('.up')) $row.insertBefore($row.prev());
        if ($(this).is('.down')) $row.insertAfter($row.next());
        if ($(this).is('.removeItem')) {
            const itemId = $('#itemid-'+$row.index()).val();
            if (itemId > 0) {
                $.ajax({
                    url: baseurl + `projects/budget_delete_item/${itemId}`,
                    method: 'DELETE'
                });
            }
            $row.remove();
        }
        
        calcBudget();
    });
    // On Issue or Save product
    $('#budget-item').on('click', '.issueItem, .saveItem', function() {
        const i = $(this).parents('tr:first').index();
        const data = {
            numbering: $('#numbering-'+i).val(),
            product_name: $('#itemname-'+i).val(),
            unit: $('#unit-'+i).val(),
            price: $('#price-'+i).val(),
            new_qty: $('#newqty-'+i).val(),
            issue_qty: $('#issueqty-'+i).val(),
            item_id: $('#itemid-'+i).val(),
            product_id: $('#productid-'+i).val(),
            row_index: $('#rowindex-'+i).val(),
            budget_id: $('#budgetid-'+i).val(),
            a_type: $('#atype-'+i).val(),
        };
        // ajax api calls
        const ajaxConfig = {method: 'POST', type: 'json-data', data};
        if ($(this).is('.issueItem')) {
            ajaxConfig['url'] = "{{ route('biller.stockissuance.issue_stock') }}";
            if (data.issue_qty) {
                $.ajax(ajaxConfig).done(function(data) {
                    $('#issueqty-'+i).val('');
                    if (data) {
                        $('#issuedqty-'+i).val(data.issue_qty);
                        const aprvQty = $('#newqty-'+i).val();
                        if ($('#issuedqty-'+i).val() == aprvQty) {
                            $('#issueqty-'+i).attr('disabled', true);
                        }
                    }
                }); 
            }                 
        }
        if ($(this).is('.saveItem')) {
            if (!data.numbering) return alert('Please input numbering on line item!');
            ajaxConfig['url'] = "{{ route('biller.stockissuance.store') }}";
            $.ajax(ajaxConfig).done(function(data) { location.reload(); });      
        }
    });

    // autocompleteProp returns autocomplete object properties
    function autocompleteProp(i) {
        return {
            source: function(request, response) {
                $.ajax({
                    url: baseurl + 'products/quotesearch/'+i,
                    dataType: "json",
                    method: 'post',
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
                $('#unit-'+i).val(data.unit);                

                const price = parseFloat(data.purchase_price.replace(/,/g, ''));
                $('#price-'+i).val(price.toLocaleString()).trigger('change');
            }
        };
    }

    // total budget
    function calcBudget() {
        let total = 0;
        let labourTotal = 0;
        $('#budget-item tbody tr').each(function(i) {
            const spanText = $(this).find('td').eq(6).children().text();
            const amount = parseFloat(spanText.replace(/,/g, ''));
            if (amount) total += amount;
            // update row index
            $(this).find('#rowindex-'+i).val(i);
        });
        $('#skill-item tbody tr').each(function() {
            const spanText = $(this).find('td').eq(5).children().text();
            const amount = parseFloat(spanText.replace(/,/g, ''));
            total += amount;
            labourTotal += amount;
        });
        $('#budget-total').val(parseFloat(total).toLocaleString());
        $('#labour-total').val(parseFloat(labourTotal).toLocaleString());
        // profit
        const profit = parseFloat(subtotal) - total;
        $('.profit').text(profit.toLocaleString());

        // budget limit
        $('.budget-alert').addClass('d-none');
        const quoteTotal = parseFloat($('#quote-total').val().replace(/,/g, ''));
        const limit = quoteTotal * 0.8;
        if (total > limit) {
            $('.budget-alert').removeClass('d-none');
            scroll(0, 0);
        }
    }

    // On View Log
    function logRow(v, i) {
        return `
            <tr>
                <td class="text-center">${i+1}</td>
                <td class="text-center">${v.issue_qty}</td>
                <td>${v.issuer}</td>
                <td>${new Date(v.created_at).toDateString()}</td>
            <tr>
        `;
    }
    $(document).on('click', ".logItem", function() {
        const $row = $(this).parents('tr:first');
        const itemId = $('#itemid-'+$row.index()).val();
        $.ajax({
            url: baseurl + `issued_stock_log/${itemId}`,
            dataType: 'json',
            success: function(data) {
                $('#issueItemLog tbody').html('');
                data.forEach((v, i) => {
                    $('#issueItemLog tbody').append(logRow(v, i));
                });                
            }
        });
    });
</script>
@endsection