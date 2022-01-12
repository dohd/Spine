@extends('core.layouts.app')

@section('title', 'Project Budget')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="alert alert-warning col-12 d-none budget-alert" role="alert">
            <strong>Budget Limit Exceeded!</strong> You should check on your list items.
        </div>
    </div>
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="content-header-title">Project Budget</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    <a href="{{ route('biller.projects.index') }}" class="btn btn-primary">
                        <i class="ft-list"></i> Projects
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-body">                
                {{ Form::model($quote, ['route' => ['biller.projects.store_quote_budget'], 'method' => 'POST' ]) }}
                <input type="hidden" name="quote_id" value="{{ $quote->id }}">
                <div class="form-group row">
                    <div class="col-12">
                        <h3 class="title">
                            @php
                                $title = $quote->bank_id ? 'Project Proforma Invoice' : 'Project Quote';
                            @endphp
                            {{ $title }}
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
                                        @if ($quote->bank_id)
                                            #PI {{ trans('general.serial_no') }}
                                        @else
                                            #QT {{ trans('general.serial_no') }}
                                        @endif
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
                    <table id="quote-item" class="table-responsive tfr my_stripe_single mb-1">
                        <thead>
                            <tr class="item_header bg-gradient-directional-blue white">
                                <th width="38%" class="text-center">{{trans('general.item_name')}}</th>
                                <th width="7%" class="text-center">UOM</th>
                                <th width="8%" class="text-center">{{trans('general.quantity')}}</th> 
                                <th width="8%" class="text-center">New Quantity</th>     
                                <th width="16%" class="text-center">Buy Price (VAT Exc)</th>
                                <th width="16%" class="text-center">Amount</th>
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
                                    @isset($budget)
                                        {{ $budget->tool }}
                                    @endisset
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
                            {{ Form::submit('Generate', ['class' => 'btn btn-success btn-lg']) }}
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
    // initialize html editor
    editor();

    // ajax setup
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // set default values
    const subtotal = @json($quote->subtotal);
    $('#quote-total').val(parseFloat(subtotal).toLocaleString());
    
    // initialize Quote Date datepicker
    $('.datepicker')
        .datepicker({ format: "{{ config('core.user_date_format') }}" })
        .datepicker('setDate', new Date("{{ $quote->invoicedate }}"));

    // skill row
    function skillRow(v) {
        return `
            <tr>
                <td>
                    <select class="form-control update" name="skill[]" id="skill-${v}" required>
                        <option value="0" class="text-center">-- Select Skill Type --</option>                        
                        <option value="casual">Casual</option>
                        <option value="contract">Contract</option>
                        <option value="outsourced">Outsourced</option>
                    </select>
                </td>
                <td><input type="number" class="form-control update" name="charge[]" id="charge-${v}" required readonly></td>
                <td><input type="number" class="form-control update" name="hours[]" id="hours-${v}" required></td>               
                <td><input type="number" class="form-control update" name="no_technician[]" id="notech-${v}" required></td>
                <td class="text-center"><span>0</span></td>
                <td><button type="button" class="btn btn-primary removeItem">Remove</button></td>
                <input type="hidden" name="skillitem_id[]" value="0" id="skillitemid-${v}">
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
        $(this).parentsUntil('tbody').eq(1).children().eq(4).children().text(amountStr);

        calcBudget();
    });

    // default skill row
    const skillset = @json($skillset);
    let skillIndx = 0;
    if (skillset.length) {
        skillset.forEach(v => {
            assignSkill(skillIndx, v);
            skillIndx++;
        })
    } else {
        $('#skill-item tbody').append(skillRow(0));
    }

    $('#add-skill').click(function() {
        // append row
        $('#skill-item tbody').append(skillRow(skillIndx));
        skillIndx++;
    });
    // Remove skill row
    $('#skill-item').on('click', '.removeItem', function() {
        const itemId = $(this).parent().next('input[type=hidden]').val();
        if (itemId != 0) {
            if (confirm('Are you sure to delete this item ?')) {
                $.ajax({
                    url: baseurl + 'projects/quote_budget/delete_skillset/' + itemId,
                    method: 'DELETE',
                    dataType: 'json'
                });
            }
        }

        $(this).closest('tr').remove();
        calcBudget();
    });
    function assignSkill(i, v) {
        $('#skill-item tbody').append(skillRow(i));
        $('#skillitemid-'+i).val(v.id);
        $('#charge-'+i).val(v.charge);
        $('#hours-'+i).val(v.hours);
        $('#notech-'+i).val(v.no_technician);
        $('#skill-'+i).val(v.skill).change();
    }

    // On quote-item update
    $('#quote-item').on('change', '.update', function() {
        if (!$(this).val()) $(this).val(0);

        const id = $(this).attr('id');
        const rowIndx = id.split('-')[1];        
        const price = $('#price-'+rowIndx).val().replace(/,/g, '');
        const qty = $('#newqty-'+rowIndx).val();

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

    // product row
    function productRow(n) {
        return `
            <tr>
                <td><input type="text" class="form-control" name="product_name[]" id="itemname-${n}" required></td>
                <td><input type="text" class="form-control" name="unit[]" id="unit-${n}" required></td>                
                <td><input type="number" class="form-control" name="product_qty[]" value="0" id="amount-${n}" readonly></td>
                <td><input type="number" class="form-control update" name="new_qty[]" id="newqty-${n}" required></td>
                <td><input type="text" class="form-control update" name="price[]" id="price-${n}" required></td>
                <td class="text-center"><span>0</span></td>
                <td><button type="button" class="btn btn-primary removeItem">Remove</button></td>
                <input type="hidden" name="product_id[]" value="0" id="productid-${n}">
                <input type="hidden" name="item_id[]" value="0" id="itemid-${n}">
            </tr>
        `;
    }

    function assignVal(i, v) {
        $('#quote-item tbody').append(productRow(i));
        $('#itemname-'+i).autocomplete(autocompleteProp(i));
        // set default values
        $('#itemid-'+i).val(v.id);
        $('#productid-'+i).val(v.product_id);
        $('#itemname-'+i).val(v.product_name);
        $('#unit-'+i).val(v.unit);                
        $('#amount-'+i).val(parseFloat(v.product_qty));

        if (v.new_qty && v.price) {
            $('#newqty-'+i).val(v.new_qty);
            $('#price-'+i).val(parseFloat(v.price).toLocaleString());
            $('#price-'+i).change();
        }
    }

    // set default product rows
    const budgetItems = @json($budget_items);
    const quoteItems = @json($products);
    let productIndx = 0;

    if (budgetItems.length) {
        budgetItems.forEach(v => {
            assignVal(productIndx, v);        
            productIndx++;
        });
    } else {
        quoteItems.forEach(v => {
            // check type if item is product (a_type = 1)
            if (v.a_type === 1) assignVal(productIndx, v);        
            productIndx++;
        });
    }

    // add product row
    $('#add-product').click(function() {
        const i = productIndx;
        $('#quote-item tbody').append(productRow(i));
        // autocomplete on added product row
        $('#itemname-'+i).autocomplete(autocompleteProp(i));
        productIndx++;
    });
    // remove product row
    $('#quote-item').on('click', '.removeItem', function() {
        const itemId = $(this).parent().next().next('input[type=hidden]').val();
        if (itemId != 0) {
            if (confirm('Are you sure to delete this item ?')) {
                $.ajax({
                    url: baseurl + 'projects/quote_budget/delete_budget_item/' + itemId,
                    method: 'DELETE',
                    dataType: 'json'
                });
            }
        }

        $(this).closest('tr').remove();
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
                $('#price-'+i).val(price.toLocaleString());
            }
        };
    }

    // total budget
    function calcBudget() {
        let total = 0;
        let labourTotal = 0;
        $('#quote-item tbody tr').each(function() {
            const spanText = $(this).find('td').eq(5).children().text();
            const amount = parseFloat(spanText.replace(/,/g, ''));
            total += amount;
        });
        $('#skill-item tbody tr').each(function() {
            const spanText = $(this).find('td').eq(4).children().text();
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