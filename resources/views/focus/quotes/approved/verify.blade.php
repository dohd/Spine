@extends ('core.layouts.app')

@section ('title', $quote->bank_id ? 'Verify Proforma Invoice' : 'Verify Quote')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="content-header-title">Verification Management</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    <div class="btn-group float-right" role="group" aria-label="quotes">
                        <a href="{{ route('biller.quotes.project_quotes') }}" class="btn btn-info  btn-lighten-2">
                            <i class="fa fa-list-alt"></i> {{trans('general.list')}}
                        </a>
                    </div>                    
                </div>
            </div>
        </div>
    </div>
    <div class="content-body">
            <div class="card">
                <div class="card-body">
                    @php
                        $query_str = request()->getQueryString();
                        $link = route('biller.quotes.storeverified');
                        if ($query_str == 'page=pi') $link = route('biller.quotes.storeverified', 'page=pi');
                    @endphp
                    {{ Form::model($quote, ['url' => $link, 'class' => 'form-horizontal', 'method' => 'POST']) }}                   
                    <input type="hidden" name="id" value="{{ $quote->id }}">
                    <div class="row">
                        <div class="col-sm-6 cmp-pnl">
                            <div id="customerpanel" class="inner-cmp-pnl">
                                <div class="form-group row">
                                    <div class="fcol-sm-12">
                                        <h3 class="title pl-1">{{ $quote->bank_id ? 'Verify Proforma Invoice' : 'Verify Quote' }}</h3>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <label for="subject" class="caption">Subject / Title</label>
                                        {{ Form::text('notes', null, ['class' => 'form-control', 'id'=>'subject', 'disabled']) }}
                                    </div>
                                </div>   
                                <div class="form-group row">                                    
                                    <div class="col-sm-6">
                                        <label for="client" class="caption">Client</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                            {{ Form::text('client', @$quote->client->name, ['class' => 'form-control round', 'id' => 'client', 'disabled']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="branch" class="caption">Branch</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                            {{ Form::text('branch', @$quote->branch->name, ['class' => 'form-control round', 'id' => 'branch', 'disabled']) }}
                                        </div>
                                    </div>
                                </div> 
                                <div class="form-group row">
                                    <div class="col-sm-6"><label for="invoicedate" class="caption">Quote {{trans('general.date')}}</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                            {{ Form::text('invoicedate', null, ['class' => 'form-control round datepicker', 'id'=>'invoicedate', 'disabled']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-6">                                        
                                        <label for="serial_no" class="caption">{{ $quote->bank_id ? '#PI' : '#Qt' }} {{trans('general.serial_no')}}</label>
                                        <div class="input-group">
                                            <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>                                           
                                            @php
                                                $tid = 'QT-'.sprintf('%04d', $quote->tid);
                                                if ($quote->bank_id) $tid = 'PI-'.sprintf('%04d', $quote->tid);
                                            @endphp
                                            {{ Form::text('tid', $tid, ['class' => 'form-control round', 'id' => 'tid', 'disabled']) }}
                                        </div>
                                    </div>                                    
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-6">
                                        <label for="invocieno" class="caption">Djc Reference</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                            {{ Form::text('reference', null, ['class' => 'form-control round', 'disabled']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-6"><label for="reference_date" class="caption">Djc Reference Date</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                            {{ Form::text('reference_date', null, ['class' => 'form-control round datepicker', 'id'=>'reference-date', 'disabled']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 cmp-pnl">
                            <div class="inner-cmp-pnl">
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <h3 class="title">Properties</h3>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="client_ref" class="caption">Client Ref / Callout ID</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                            {{ Form::text('client_ref', null, ['class' => 'form-control round', 'placeholder' => 'Client Reference', 'id' => 'client_ref', 'disabled']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="verify_no" class="caption">Verification</label>
                                        <div class="input-group">
                                            <select class="form-control" name="verify_no" id="verify_no" required>
                                                <option value="1" selected>V1</option>
                                                <option value="2">V2</option>
                                                <option value="3">V3</option>
                                                <option value="4">V4</option>
                                                <option value="5">V5</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <table id="jobcard" class="table-responsive pb-2 tfr">
                                            <thead class="bg-gradient-directional-blue white pb-1">
                                                <tr>
                                                    <th class="text-center" width="23%">Type</th>
                                                    <th class="text-center" width="23%">Reference No</th>                                                    
                                                    <th class="text-center" width="22%">Reference Date</th>
                                                    <th class="text-center" width="50%">Technician</th>
                                                    <th class="text-center" width="5%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <select name="type[]" id="type-0" class="form-control" required>
                                                            <option value="1" selected>JobCard</option>
                                                            <option value="2">DNote</option> 
                                                        </select>
                                                    </td>
                                                    <td><input type="text" class="form-control" name="reference[]" id="reference-0" required></td>
                                                    <td><input type="text" class="form-control datepicker" name="date[]" id="date-0" required></td>
                                                    <td><input type="text" class="form-control" name="technician[]" id="technician-0" required></td>
                                                    <th class="text-center">#</th>
                                                    <input type="hidden" name="jcitem_id[]" value="0" id="jcitemid-0">
                                                </tr>
                                            </tbody>
                                        </table>
                                        <button type="button" class="btn btn-success" aria-label="Left Align" id="add-jobcard">
                                            <i class="fa fa-plus-square"></i> Add Jobcard / DNote
                                        </button>                                            
                                    </div>
                                </div>                                                                
                            </div>
                        </div>                        
                    </div>                  

                    <div>                            
                        <table id="quotation" class="table-responsive pb-5 tfr my_stripe_single">
                            <thead>
                                <tr class="item_header bg-gradient-directional-blue white">
                                    <th width="7%" class="text-center">Numbering</th>
                                    <th width="35%" class="text-center">{{trans('general.item_name')}}</th>
                                    <th width="7%" class="text-center">UOM</th>
                                    <th width="5%" class="text-center">{{trans('general.quantity')}}</th>
                                    <th width="10%" class="text-center">{{trans('general.rate')}} Exclusive</th>
                                    <th width="10%" class="text-center">{{trans('general.rate')}} Inclusive</th>
                                    <th width="10%" class="text-center">{{trans('general.amount')}} ({{config('currency.symbol')}})</th>
                                    <th width="12%" class="text-center">Remark</th>
                                    <th width="5%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                        <div class="row">
                            <div class="col-md-8 col-xs-7">
                                <button type="button" class="btn btn-success" aria-label="Left Align" id="add-product">
                                    <i class="fa fa-plus-square"></i> Add Product
                                </button>
                                <button type="button" class="btn btn-primary" aria-label="Left Align" id="add-title">
                                    <i class="fa fa-plus-square"></i> Add Title
                                </button>
                                <div class="form-group mt-3">
                                    <div><label for="gen_remark" class="caption">General Remark</label></div>
                                    <textarea class="form-control" name="gen_remark" id="gen_remark" cols="30" rows="10"></textarea>
                                </div>
                            </div>

                            <div class="col-md-4 col-xs-5 invoice-block pull-right">
                                <div class="unstyled amounts">
                                    <div class="form-group">
                                        <label>SubTotal (<span class="currenty lightMode">{{config('currency.symbol')}}</span>)</label>
                                        <div class="input-group m-bot15">
                                            <input type="text" name="subtotal" id="subtotal" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>{{trans('general.total_tax')}}</label>
                                        <div class="input-group m-bot15">
                                            <input type="text" name="tax" id="tax" class="form-control" readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>{{trans('general.grand_total')}} (<span class="currenty lightMode">{{config('currency.symbol')}}</span>)</label>
                                        <div class="input-group m-bot15">
                                            <input type="text" name="total" class="form-control" id="total" placeholder="Total" readonly>
                                        </div>
                                    </div>
                                    {{ Form::submit('Verify & Save', ['class' => 'btn btn-success btn-lg']) }}
                                    @if ($verify_no > 1)
                                        <button type="button" class="btn btn-danger btn-lg" aria-label="Left Align" id="reset-items">
                                            <i class="fa fa-refresh"></i> Undo
                                        </button>
                                    @endif
                                </div>
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
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});

    const verify_no = "{{ $verify_no }}";

    // Intialize datepicker
    $('.datepicker').datepicker({ format: "{{ config('core.user_date_format') }}" });
    $('#reference-date').datepicker('setDate', new Date("{{ $quote->reference_date }}"));
    $('#invoicedate').datepicker('setDate', new Date("{{ $quote->invoicedate }}"));
    $('#date-0').datepicker('setDate', new Date());

    // set general remark
    $('#gen_remark').val("{{ $quote->gen_remark }}");

    // reset Quote Verification 
    $('#reset-items').click(function() {
        const msg = 'This is a destructive process! Are you sure to reset all previously verified items ?'
        if (confirm(msg)) {
            $.ajax({
                url: baseurl + 'quotes/reset_verified/' + "{{ $quote->id }}",
                // method: 'DELETE',
                success: function() {
                    return location.reload();
                }
            })
        }
    });

    // job card row
    function jobCardRow(n) {
        return `
            <tr>
                <td>
                    <select name="type[]" id="type-${n}" class="form-control">
                        <option value="1" selected>JobCard</option>
                        <option value="2">DNote</option> 
                    </select>
                </td>
                <td><input type="text" class="form-control" name="reference[]" id="reference-${n}" required></td>
                <td><input type="text" class="form-control datepicker" name="date[]" id="date-${n}" required></td>
                <td><input type="text" class="form-control" name="technician[]" id="technician-${n}" required></td>
                <th><button class="btn btn-primary btn-md removeJc" type="button">Remove</button></th>
                <input type="hidden" name="jcitem_id[]" value="0" id="jcitemid-${n}">
            </tr>
        `;
    }
    //job card row counter
    let jcIndex = 1;
    // addjob card row
    $('#add-jobcard').click(function() {
        $('#jobcard tbody').append(jobCardRow(jcIndex));
        // initalize datepicker
        $('#date-'+jcIndex)
            .datepicker({ format: "{{ config('core.user_date_format') }}"  })
            .datepicker('setDate', new Date());
        jcIndex++;
    });

    // remove job card row
    $('#jobcard').on('click', '.removeJc', function() {
        if ($(this).is('.removeJc')) {
            const $row = $(this).parents('tr:first');
            const itemId = $row.find('input[name="jcitem_id[]"]').val();
            if (itemId == 0) return $row.remove();
            if (verify_no > 1) {
                if (confirm('Are you sure to delete this job card ?')) {
                    $.ajax({
                        url: baseurl + 'quotes/verified_jcs/' + itemId,
                        // method: 'DELETE'
                    });
                    $row.remove();
                }
            } 
        }
    });

    // On next verifications other than the first
    if (verify_no > 1) {
        // fetch job cards
        $.ajax({
            url: baseurl + 'quotes/verified_jcs/' + "{{ $quote->id }}",
            method: 'POST',
            dataType: 'json',
            success: function(data) {
                // set default job card rows
                data.forEach((v, i) => {
                    if (i > 0) {
                        $('#jobcard tbody').append(jobCardRow(i));
                        jcIndex++;
                    }
                    // set values
                    $('#jcitemid-'+i).val(v.id);
                    $('#reference-'+i).val(v.reference);
                    $('#type-'+i).val(v.type);
                    $('#technician-'+i).val(v.technician);
                    $('#date-'+i)
                        .datepicker({ format: "{{ config('core.user_date_format') }}" })
                        .datepicker('setDate', new Date(v.date));
                });
            }
        });
    }

    // row dropdown menu
    function dropDown(val) {
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
                <td><input type="text" class="form-control req amount" name="product_qty[]" id="amount-${val}" onchange="qtyChange(event)" autocomplete="off"></td>
                <td><input type="text" class="form-control req price" name="product_price[]" id="price-${val}" onchange="priceChange(event)" autocomplete="off"></td>
                <td><input type="text" class="form-control req prcrate" name="product_subtotal[]" id="rateinclusive-${val}" autocomplete="off" readonly></td>
                <td><strong><span class='ttlText' id="result-${val}">0</span></strong></td>
                <td><textarea class="form-control" name="remark[]" id="remark-${val}"></textarea></td>
                <td class="text-center">${dropDown()}</td>
                <input type="hidden" name="item_id[]" value="0" id="itemid-${val}">
                <input type="hidden" name="product_id[]" value=0 id="productid-${val}">
                <input type="hidden" name="row_index[]" value="0" id="rowindex-${val}">
                <input type="hidden" name="a_type[]" value="1" id="atype-${val}">
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
                <input type="hidden" name="row_index[]" value="0" id="rowindex-${val}">
                <input type="hidden" name="a_type[]" value="2" id="atype-${val}">
            </tr>
        `;
    }

    // On product amount or price change condition
    $('#quotation').on('change', '.amount, .price', function() {
        const i = $(this).attr('id').split('-')[1];
        $('#remark-'+i).attr('required', true);
    });

    // set default product rows
    let rowIndx = 0;
    const quoteItems = @json($products);
    quoteItems.forEach(v => {
        const i = rowIndx;
        const item = {...v};
        // format float values to integer
        const keys = ['product_price', 'product_qty', 'product_subtotal'];
        for (let prop in item) {
            if (keys.includes(prop) && item[prop]) {
                item[prop] = parseFloat(item[prop].replace(/,/g, ''));
            }
        }
        // check if item type is product
        if (item.a_type == 1) {
            $('#quotation tbody').append(productRow(rowIndx));
            $('#itemname-'+rowIndx).autocomplete(autocompleteProp(rowIndx));

            // set default values
            $('#itemid-'+i).val(item.id);
            $('#productid-'+i).val(item.product_id);
            $('#numbering-'+i).val(item.numbering);
            $('#itemname-'+i).val(item.product_name);
            $('#unit-'+i).val(item.unit); 
            $('#remark-'+i).val(item.remark);
            $('#amount-'+i).val(parseFloat(item.product_qty));
            $('#price-'+i).val(item.product_price.toFixed(2));
            $('#rateinclusive-'+i).val(item.product_subtotal.toFixed(2));                
            $('#result-'+i).text(item.product_subtotal.toFixed(2));
        } else {
            $('#quotation tbody').append(productTitleRow(rowIndx));
            // set default values
            $('#itemid-'+i).val(item.id);
            $('#productid-'+i).val(item.product_id);
            $('#numbering-'+i).val(item.numbering);
            $('#itemname-'+i).val(item.product_name);
        }
        rowIndx++;
        totals();
    });    

    // On click Add Product
    $('#add-product').click(function() {
        $('#quotation tbody').append(productRow(rowIndx));
        $('#itemname-'+rowIndx).autocomplete(autocompleteProp(rowIndx));
        rowIndx++;
    });
    // on click Add Title button
    $('#add-title').click(function() {
        $('#quotation tbody').append(productTitleRow(rowIndx));
        rowIndx++;
    });

    // on clicking Product row drop down menu
    $("#quotation").on("click", ".up, .down, .removeProd", function() {
        const $row = $(this).parents("tr:first");
        if ($(this).is('.up')) $row.insertBefore($row.prev());
        if ($(this).is('.down')) $row.insertAfter($row.next());
        if ($(this).is('.removeProd')) {
            const itemId = $row.find('input[name="item_id[]"]').val();
            if (itemId == 0) return $row.remove();            
            if (verify_no > 1) {
                if (confirm('Are you sure to delete this item?')) {
                    $.ajax({
                        url: baseurl + 'quotes/verified_item/' + itemId,
                        // method: 'DELETE',
                    });
                    $row.remove();                
                }
            }            
        }
        
        totals();
    });

    // default tax
    const tax = "{{ $quote->tax_id }}";
    const taxInt = parseFloat(tax.replace(/,/g, ''));
    let taxRate = (taxInt+100)/100;

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
                // Compute Totals
                totals();
            }
        };
    }

    // on quantity input change
    function qtyChange(e) {
        const id = e.target.id;
        const indx = id.split('-')[1];

        const productQty = $('#'+id).val();

        let productPrice = $('#price-'+indx).val();
        productPrice = parseFloat(productPrice.replace(/,/g, ''));

        const rateInclusive = taxRate * productPrice;
        $('#rateinclusive-'+indx).val(rateInclusive.toFixed(2));

        const rowAmount = productQty * parseFloat(rateInclusive);
        $('#result-'+indx).text(rowAmount.toFixed(2));

        totals();
    }
    // on price input change
    function priceChange(e) {
        // change value to float
        e.target.value = Number(e.target.value).toFixed(2);

        const id = e.target.id;
        indx = id.split('-')[1];

        const productQty = $('#amount-'+indx).val();

        let productPrice = $('#'+id).val();
        productPrice = parseFloat(productPrice.replace(/,/g, ''));

        const rateInclusive = taxRate * productPrice;
        $('#rateinclusive-'+indx).val(rateInclusive.toFixed(2));

        const rowAmount = productQty * parseFloat(rateInclusive);
        $('#result-'+indx).text(rowAmount.toFixed(2));
        totals();
    }

    // totals
    function totals() {
        let subTotal = 0;
        let grandTotal = 0;
        $('#quotation tr').each(function(i) {
            if (!i) return;
            const productQty = $(this).find('td').eq(3).children().val()
            if (productQty) {
                const productPrice = $(this).find('td').eq(4).children().val();
                const rateInclusive = $(this).find('td').eq(5).children().val();
                // increament
                subTotal += Number(productQty) * parseFloat(productPrice);
                grandTotal += Number(productQty) * parseFloat(rateInclusive);
            }
            // update row_index
            $(this).find('input[name="row_index[]"]').val($(this).index());
        });

        const taxTotal = parseFloat(grandTotal) - parseFloat(subTotal);
        $('#tax').val(taxTotal.toFixed(2));        
        $('#subtotal').val(subTotal.toFixed(2));
        $('#total').val(grandTotal.toFixed(2));
    }
</script>
@endsection