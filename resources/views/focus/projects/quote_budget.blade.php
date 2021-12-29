@extends('core.layouts.app')

@section('title', 'Project Budget')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="alert alert-warning alert-dismissible fade show col-12 d-none budget-alert" role="alert">
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
                {{ Form::model($quote, ['route' => ['biller.projects.quote_budget', $quote], 'method' => 'PATCH' ]) }}
                <div class="form-group row">
                    <div class="col-12">
                        @php
                            $title = $quote->bank_id ? 'Project Proforma Invoice' : 'Project Quote';
                        @endphp
                        <h3 class="title">{{ $title }}</h3>                                        
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
                                <th width="16%" class="text-center">Price (VAT Exc)</th>
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
                                        <th width="30%" class="text-center">Skill Type</th>
                                        <th width="15%" class="text-center">Working Hrs</th>
                                        <th width="15%" class="text-center">No. of Technicians</th> 
                                        <th width="20%" class="text-center">Amount</th>
                                        <th width="10%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <button type="button" class="btn btn-success mt-1" id="add-skill">
                                <i class="fa fa-plus-square"></i> Add Skill
                            </button>
                        </div>  
                        <div class="col-4">
                            <div><label for="tools">Tools Required</label></div>
                            <textarea name="tools" id="tools" cols="45" rows="6" class="form-control"></textarea>   
                            <div class="form-group mt-2">
                                <div><label for="quote-total">Total Quote <span class="text-danger">(VAT Exclusive)</span></label></div>
                                <div><input type="text" class="form-control" id="quote-total" readonly></div>
                            </div>
                            <div class="form-group">
                                <div><label for="budget-total">Total Budget</label></div>
                                <div><input type="text" value="0" class="form-control" id="budget-total" readonly></div>
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
    // ajax setup
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // set default options
    $('#pricing').val("{{ $quote->pricing }}");
    $('#quote-total').val("{{ number_format($quote->subtotal, 2) }}");
    
    $('#tax_id').val("{{ $quote->tax_id }}");
    $('#client_ref').val("{{ $quote->client_ref }}");
    $('#tax_format').val("{{ $quote->tax_format }}");

    // initialize Quote Date datepicker
    $('.datepicker')
        .datepicker({ format: "{{ config('core.user_date_format') }}" })
        .datepicker('setDate', new Date("{{ $quote->invoicedate }}"));

    // skill row
    function skillRow(v) {
        return `
            <tr>
                <td>
                    <select class="form-control update" id="skilltype-${v}" required>
                        <option value="0" class="text-center">-- Select Skill Type --</option>
                        <option value="350">Contract</option>
                        <option value="200">Casual</option>
                        <option value="500">Outsourced</option>
                    </select>
                </td>
                <td><input type="number" class="form-control update" name="hours[]" id="hours-${v}"></td>                
                <td><input type="number" class="form-control update" name="no_tech[]" id="notech-${v}"></td>
                <td class="text-center"><span>0</span></td>
                <td><button type="button" class="btn btn-primary removeItem">Remove</button></td>
            </tr>
        `;
    }
    // default skill row
    let skillIndx = 0;
    $('#skill-item tbody').append(skillRow(0));
    $('#add-skill').click(function() {
        // append row
        skillIndx++;
        $('#skill-item tbody').append(skillRow(skillIndx));
    });
    // Remove skill row
    $('#skill-item').on('click', '.removeItem', function() {
        $(this).closest('tr').remove();
        calcBudget();
    });


    // product row
    function productRow(n) {
        return `
            <tr>
                <td><input type="text" class="form-control" name="product_name[]" id="itemname-${n}"></td>
                <td><input type="text" class="form-control" name="unit[]" id="unit-${n}"></td>                
                <td><input type="number" class="form-control" name="product_qty[]" id="amount-${n}" readonly></td>
                <td><input type="number" class="form-control update" name="new_qty[]" id="newqty-${n}"></td>
                <td><input type="text" class="form-control update" name="price[]" id="price-${n}"></td>
                <td class="text-center"><span>0</span></td>
                <td><button type="button" class="btn btn-primary removeItem">Remove</button></td>
                <input type="hidden" name="item_id[]" value="0" id="itemid-${n}">
                <input type="hidden" name="product_id[]" value="0" id="productid-${n}">
                <input type="hidden" name="row_index[]" value="0" id="rowindex-${n}">
                <input type="hidden" name="a_type[]" value="1" id="atype-${n}">
            </tr>
        `;
    }

    // product row counter
    let productIndx = 0;
    // set default product rows
    const quoteItems = @json($products);
    quoteItems.forEach(v => {
        const i = productIndx;
        const item = {...v};
        // format float values to integer
        const keys = ['product_price','product_qty','product_subtotal'];
        keys.forEach(key => {
            item[key] = parseFloat(item[key].replace(/,/g, ''));
        });

        // check type if item is product (a_type = 1)
        if (item.a_type === 1) {
            $('#quote-item tbody').append(productRow(i));
            $('#itemname-'+productIndx).autocomplete(autocompleteProp(productIndx));
            // set default values
            $('#itemid-'+i).val(item.id);
            $('#productid-'+i).val(item.product_id);
            $('#itemname-'+i).val(item.product_name);
            $('#unit-'+i).val(item.unit);                
            $('#amount-'+i).val(parseFloat(item.product_qty));
        } 
        productIndx++;
    });
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
        $(this).closest('tr').remove();
        calcBudget();
    });

    // autocompleteProp returns autocomplete object properties
    function autocompleteProp(i) {
        return {
            source: function(request, response) {
                $.ajax({
                    url: baseurl + 'products/quotesearch/' + billtype,
                    dataType: "json",
                    method: 'post',
                    data: 'keyword=' + request.term + '&type=product_list&row_num=1&pricing=' 
                        + $("#pricing").val(),
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                label: item.name,
                                value: item.name,
                                data: item
                            };
                        }));
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
                $('#amount-'+i).val(1);
                const productPrice = parseFloat(data.price.replace(',',''));
                $('#price-'+i).val(productPrice.toFixed(2));

                // Initial values                
                const rateInclusive = taxRate * productPrice;
                $('#rateinclusive-'+i).val(rateInclusive.toFixed(2));                
                // displayed Amount
                $('#result-'+i).text(rateInclusive.toFixed(2));
            }
        };
    }

    // On quote-item update
    $('#quote-item').on('change', '.update', function() {
        if (!$(this).val()) $(this).val(0);

        const id = $(this).attr('id');
        const rowIndx = id.split('-')[1];        
        const priceStr = $('#price-'+rowIndx).val().replace(/,/g, '');
        const qtyStr = $('#newqty-'+rowIndx).val();

        const amount = qtyStr * parseFloat(priceStr);
        const amountStr = parseFloat(amount).toLocaleString();
        if (id.includes('price')) {
            const n = $(this).val();
            $(this).val(parseFloat(n).toLocaleString());
            $(this).parent().next().children().text(amountStr);
        } else if (priceStr) {
            $(this).parent().next().next().children().text(amountStr);
        }

        calcBudget();
    });

    // On skill-item update
    $('#skill-item').on('change', '.update', function() {
        if (!$(this).val()) $(this).val(0);

        const id = $(this).attr('id');
        const rowIndx = id.split('-')[1];   
        const hrStr = $('#hours-'+rowIndx).val();
        const notechStr = $('#notech-'+rowIndx).val();
        const skilltypeStr = $('#skilltype-'+rowIndx).val();

        const amount = hrStr * notechStr * skilltypeStr;
        const amountStr = parseFloat(amount).toLocaleString();
        $(this).parentsUntil('tbody').eq(1).children().eq(3).children().text(amountStr);

        calcBudget();
    });

    // total budget
    function calcBudget() {
        let total = 0;
        $('#quote-item tbody tr').each(function() {
            const spanText = $(this).find('td').eq(5).children().text();
            const amount = parseFloat(spanText.replace(/,/g, ''));
            total += amount;
        });
        $('#skill-item tbody tr').each(function() {
            const spanText = $(this).find('td').eq(3).children().text();
            const amount = parseFloat(spanText.replace(/,/g, ''));
            total += amount;
        });
        $('#budget-total').val(parseFloat(total).toLocaleString());

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