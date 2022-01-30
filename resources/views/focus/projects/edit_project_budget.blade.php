@extends('core.layouts.app')

@section('title', 'Project Budget | Edit')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="alert alert-warning col-12 d-none budget-alert" role="alert">
            <strong>Budget Limit Exceeded!</strong> You should check on your list items.
        </div>
    </div>
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12">
            <h4 class="content-header-title">Project Budget Management</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    <div class="btn-group">
                        <a href="{{ route('biller.projects.index') }}" class="btn btn-primary">
                            <i class="ft-list"></i> Projects
                        </a>&nbsp;&nbsp;
                        @php
                            $valid_token = token_validator('', 'q'.$quote->id .$quote->tid, true);
                            $budget_url = route('biller.print_budget', [$quote->id, 4, $valid_token, 1]);
                            $quote_url = route('biller.print_budget_quote', [$quote->id, 4, $valid_token, 1]);
                        @endphp
                        <a href="{{ $budget_url }}" class="btn btn-purple" target="_blank">
                            <i class="fa fa-print"></i> Budget
                        </a>&nbsp;
                        <a href="{{ $quote_url }}" class="btn btn-secondary" target="_blank">
                            <i class="fa fa-print"></i> Quote
                        </a> 
                    </div>                    
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-body">                
                {{ Form::model($quote, ['route' => ['biller.projects.update_project_budget', $budget], 'method' => 'POST']) }}
                <input type="hidden" name="quote_id" value="{{ $quote->id }}">
                <div class="form-group row">
                    <div class="col-12">
                        <h3 class="title">
                            {{ $quote->bank_id ? 'Ammend Budgeted Proforma Invoice' : 'Ammend Budgeted Quote' }}
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
                                <th width="8%" class="text-center">Quoted Qty</th>                                
                                <th width="7%" class="text-center">UOM</th>
                                <th width="8%" class="text-center">Approve Qty</th>     
                                <th width="12%" class="text-center">Buy Price (VAT Exc)</th>
                                <th width="10%" class="text-center">Amount</th>
                                <th width="8%" class="text-center">Issued Qty</th>  
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
                            <button type="button" class="btn btn-success mt-1" id="add-skill">
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
                            {{ Form::submit('Ammend', ['class' => 'btn btn-success btn-lg', 'id' => 'submit']) }}
                        </div>                              
                    </div>
                </div>
                {{ Form::close() }}
            </div>             
        </div>
    </div>
</div>
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
                    <select class="form-control update" name="skill[]" id="skill-${n}" required>
                        <option value="" class="text-center">-- Select Skill Type --</option>                        
                        <option value="casual">Casual</option>
                        <option value="contract">Contract</option>
                        <option value="outsourced">Outsourced</option>
                    </select>
                </td>
                <td><input type="number" class="form-control update" name="charge[]" id="charge-${n}" required></td>
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
                    <a class="dropdown-item removeItem text-danger" href="javascript:void(0);">Delete</a>
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
                <td><input type="number" class="form-control newqty update" name="new_qty[]" value="0" id="newqty-${n}"></td>
                <td><input type="text" class="form-control update" name="price[]" id="price-${n}" required></td>
                <td class="text-center"><span>0</span></td>
                <td><input type="number" class="form-control issue" name="issue_qty[]" id="issueqty-${n}" readonly></td>
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
        $('#skillitemid-'+i).val(v.id);
        $('#skill-'+i).val(v.skill);
        $('#charge-'+i).val(v.charge);
        $('#hours-'+i).val(v.hours);
        $('#notech-'+i).val(v.no_technician).change();
        skillIndx++;
    });
    // On add skill append row
    $('#add-skill').click(function() {
        const i = skillIndx;
        $('#skill-item tbody').append(skillRow(i));
        skillIndx++;
    });
    // Remove skill row
    $('#skill-item').on('click', '.removeItem', function() {
        const $row = $(this).closest('tr');
        const itemId = $row.children('input[name="skillitem_id[]"]').val();
        if (itemId > 0) {
            $.ajax({
                url: baseurl + `/projects/budget_delete_skillset/${itemId}`,
                method: 'DELETE'
            });
        }
        $row.remove();
        calcBudget();
    });

    // ApprovedQty condition on issued items
    $('#budget-item').on('change', '.newqty', function() {        
        const issuedQty = $(this).parentsUntil('tbody').eq(1).find('.issue').val();
        if ($(this).val() < issuedQty) $(this).val(issuedQty);
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
            if (v.issue_qty) $('#issueqty-'+i).val(v.issue_qty);  
        } else {
            $('#budget-item tbody').append(titleRow(i));
            $('#numbering-'+i).val(v.numbering);
            $('#itemname-'+i).val(v.product_name);
            $('#itemid-'+i).val(v.id);
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

    // On clicking action
    $('#budget-item').on('click', '#dropdownMenuButton', function() {
        const $row = $(this).parents("tr:first");
        const $btn = $(this).next().find('.removeItem');
        $btn.removeClass('d-none');
        
        const issueQty = $('#issueqty-'+$row.index()).val();
        if (issueQty > 0) $btn.addClass('d-none');        
    });

    // Modify product row
    $('#budget-item').on('click', '.removeItem, .up, .down', function() {
        const $row = $(this).parents("tr:first");
        if ($(this).is('.up')) $row.insertBefore($row.prev());
        if ($(this).is('.down')) $row.insertAfter($row.next());        
        if ($(this).is('.removeItem')) {
            const itemId = $('#itemid-'+$row.index());
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
</script>
@endsection