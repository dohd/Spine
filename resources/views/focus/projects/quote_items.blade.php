@extends('core.layouts.app')

@section('title', 'Project Quote / PI')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            @php
                $title = $quote->bank_id ? 'Project Proforma Invoice' : 'Project Quote';
            @endphp
            <h4 class="content-header-title">{{ $title }}</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    <a href="{{ route('biller.projects.index') }}" class="btn btn-warning"><i class="ft-list"></i> Projects</a>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
            <div class="card">
                <div class="card-body">                    
                    {{ Form::model($quote, ['route' => ['biller.projects.quote_items', $quote], 'method' => 'PATCH' ]) }}
                    <div class="form-group row">
                        <div class="col-12">
                            <h3 class="title">Update List-items</h3>                                        
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
                                    <div class="col-6">
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
                                    <div class="col-3"><label for="invoicedate" class="caption">Quote {{trans('general.date')}}</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                            {{ Form::text('invoicedate', null, ['class' => 'form-control round', 'data-toggle' => 'datepicker-qd', 'disabled']) }}
                                        </div>
                                    </div>                                                                
                                    <div class="col-3"><label for="client_ref" class="caption">Client Reference / Callout ID</label>
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
                        <table id="quotation" class="table-responsive pb-5 tfr my_stripe_single">
                            <thead>
                                <tr class="item_header bg-gradient-directional-blue white">
                                    <th width="35%" class="text-center">{{trans('general.item_name')}}</th>
                                    <th width="7%" class="text-center">UOM</th>
                                    <th width="8%" class="text-center">{{trans('general.quantity')}}</th>                                    
                                    <th width="5%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                        <div class="row">
                            <div class="col-10 payment-method last-item-row sub_c">
                                <button type="button" class="btn btn-success" aria-label="Left Align" id="add-product">
                                    <i class="fa fa-plus-square"></i> Add Item
                                </button>
                                {{ Form::submit(trans('buttons.general.crud.update') . ' Items', ['class' => 'btn btn-success btn-lg float-right']) }}
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
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
    
    $('#tax_id').val("{{ $quote->tax_id }}");
    $('#client_ref').val("{{ $quote->client_ref }}");
    $('#tax_format').val("{{ $quote->tax_format }}");

    // initialize Quote Date datepicker
    $('[data-toggle="datepicker-qd"]')
        .datepicker({ format: "{{ config('core.user_date_format') }}" })
        .datepicker('setDate', new Date("{{ $quote->invoicedate }}"));

    // row dropdown menu
    function dropDown(val) {
        return `
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Action
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item removeProd" href="javascript:void(0);">Remove</a>
                    <a class="dropdown-item up" href="javascript:void(0);">Up</a>
                    <a class="dropdown-item down" href="javascript:void(0);">Down</a>
                </div>
            </div>            
        `;
    }

    // product row
    function productRow(val) {
        return `
            <tr>
                <td><input type="text" class="form-control" name="product_name[]" placeholder="{{trans('general.enter_product')}}" id='itemname-${val}'></td>
                <td><input type="text" class="form-control" name="unit[]" id="unit-${val}" value=""></td>                
                <td><input type="text" class="form-control req amnt" name="product_qty[]" id="amount-${val}" onchange="qtyChange(event)" readonly></td>
                <td class="text-center">${dropDown()}</td>
                <input type="hidden" name="item_id[]" value="0" id="itemid-${val}">
                <input type="hidden" name="product_id[]" value=0 id="productid-${val}">
                <input type="hidden" name="row_index[]" value="0" id="rowindex-${val}">
                <input type="hidden" name="a_type[]" value="1" id="atype-${val}">
            </tr>
        `;
    }

    // product row counter
    let cvalue = 0;
    // set default product rows
    const quoteItems = @json($products);
    quoteItems.forEach(v => {
        const i = cvalue;
        const item = {...v};
        // format float values to integer
        const keys = ['product_price','product_qty','product_subtotal'];
        keys.forEach(key => {
            item[key] = parseFloat(item[key].replace(',',''));
        });
        // check if item has product row parameters
        if (item.product_name && item.product_price) {
            const row = productRow(cvalue);
            $('#quotation tr:last').after(row);
            $('#itemname-'+cvalue).autocomplete(autocompleteProp(cvalue));

            // set default values
            $('#itemid-'+i).val(item.id);
            $('#productid-'+i).val(item.product_id);
            $('#itemname-'+i).val(item.product_name);
            $('#unit-'+i).val(item.unit);                
            $('#amount-'+i).val(parseFloat(item.product_qty));
        } 
        cvalue++;
    });    

    $('#add-product').click(function() {
        // append row
        const row = productRow(cvalue);
        $('#quotation tr:last').after(row);
        // autocomplete on added product row
        $('#itemname-'+cvalue).autocomplete(autocompleteProp(cvalue));
        cvalue++;
    });

    // on clicking Product row drop down menu
    $("#quotation").on("click", ".up,.down,.removeProd", function() {
        var row = $(this).parents("tr:first");
        // move row up 
        if ($(this).is('.up')) row.insertBefore(row.prev());
        // move row down
        if ($(this).is('.down')) row.insertAfter(row.next());
        // remove row
        if ($(this).is('.removeProd')) {
            if (confirm('Are you sure to delete this product ?')) {
                const row = $(this).closest('tr');
                row.remove();
                const itemId = row.find('input[name="item_id[]"]').val();
                // delete product api call 
                if (Number(itemId)) {
                    // $.ajax({
                    //     url: baseurl + 'quotes/delete_product/' + itemId,
                    //     dataType: "json",
                    //     method: 'DELETE',
                    // });
                }
            }
        }
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
</script>
@endsection