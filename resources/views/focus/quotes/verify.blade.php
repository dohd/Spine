@extends ('core.layouts.app')

@section ('title', trans('labels.backend.quotes.management').' | '.'Verify Quote')

@section('page-header')
    <h1>{{ trans('labels.backend.quotes.management') }}</h1>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="content-header-title mb-0">{{ trans('labels.backend.quotes.management') }}</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.quotes.partials.quotes-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
            <div class="card">
                <div class="card-body">
                    {{ Form::model($quote, ['route' => 'biller.quotes.storeverified', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'POST']) }}                   
                    <input type="hidden" name="id" value="{{ $quote->id }}">
                    <div class="row">
                        <div class="col-sm-6 cmp-pnl">
                            <div id="customerpanel" class="inner-cmp-pnl">
                                <div class="form-group row">
                                    <div class="fcol-sm-12"><h3 class="title pl-1">Verify Quote / Proforma Invoice</h3></div>
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
                                            {{ Form::text('invoicedate', null, ['class' => 'form-control round required', 'id'=>'invoicedate','autocomplete'=>'false', 'disabled']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="serial_no" class="caption">{{trans('general.serial_no')}}. #{{prefix(5)}}</label>
                                        <div class="input-group">
                                            <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>                                           
                                            {{ Form::number('tid', $quote->tid, ['class' => 'form-control round', 'id' => 'tid', 'disabled']) }}
                                        </div>
                                    </div>                                    
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <label for="subject" class="caption">Subject / Title</label>
                                        {{ Form::text('notes', null, ['class' => 'form-control round required', 'id'=>'subject', 'disabled']) }}
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
                                        <label for="invocieno" class="caption">{{trans('general.reference')}} (Diagnosis JobCard)</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                            {{ Form::text('reference', null, ['class' => 'form-control round', 'disabled']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-4"><label for="reference_date" class="caption">Reference {{trans('general.date')}}</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                            {{ Form::text('reference_date', null, ['class' => 'form-control round required', 'id'=>'reference-date', 'autocomplete'=>'false', 'disabled']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="verify_no" class="caption">Verification No.</label>
                                        <div class="input-group">
                                            <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>                                            
                                            {{ Form::text('verify_text', $quote->tid.'_v'.$verify_no, ['class' => 'form-control round', 'disabled']) }}
                                            <input type="hidden" name="verify_no" value="{{ $verify_no }}">
                                        </div>
                                    </div>                                                                             
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <table id="jobcard" class="table-responsive pb-2 tfr">
                                            <thead class="bg-gradient-directional-blue white pb-1">
                                                <tr>
                                                    <th class="text-center" width="23%">Reference No.</th>
                                                    <th class="text-center" width="22%">Date</th>
                                                    <th class="text-center" width="50%">Technician</th>
                                                    <th class="text-center" width="5%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><input type="text" class="form-control" name="reference[]" id="reference-0" required></td>
                                                    <td><input type="text" class="form-control" name="date[]" id="date-0" required></td>
                                                    <td><input type="text" class="form-control" name="technician[]" id="technician-0" required></td>
                                                    <th class="text-center">#</th>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <button type="button" class="btn btn-success" aria-label="Left Align" id="add-jobcard">
                                            <i class="fa fa-plus-square"></i> Add Job Card
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
                                    <th width="8%" class="text-center">{{trans('general.quantity')}}</th>
                                    <th width="14%" class="text-center">{{trans('general.rate')}} Exclusive</th>
                                    <th width="14%" class="text-center">{{trans('general.rate')}} Inclusive</th>
                                    <th width="10%" class="text-center">{{trans('general.amount')}} ({{config('currency.symbol')}})</th>
                                    <th width="5%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                        <div class="row">
                            <div class="col-md-8 col-xs-7 payment-method last-item-row sub_c">
                                <div id="load_instruction" class="col-md-6 col-lg-12 mg-t-20 mg-lg-t-0-force"></div>
                                <button type="button" class="btn btn-success" aria-label="Left Align" id="add-product">
                                    <i class="fa fa-plus-square"></i> Add Product
                                </button>
                                <button type="button" class="btn btn-primary" aria-label="Left Align" id="add-title">
                                    <i class="fa fa-plus-square"></i> Add Title
                                </button>
                            </div>

                            <div class="col-md-4 col-xs-5 invoice-block pull-right">
                                <div class="unstyled amounts">
                                    <div class="form-group">
                                        <label>SubTotal (<span class="currenty lightMode">{{config('currency.symbol')}}</span>)</label>
                                        <div class="input-group m-bot15">
                                            <input type="text" required readonly="readonly" name="subtotal" id="subtotal" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>{{trans('general.total_tax')}}</label>
                                        <div class="input-group m-bot15">
                                            <input type="text" required readonly="readonly" name="tax" id="tax" class="form-control">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>{{trans('general.grand_total')}} (<span class="currenty lightMode">{{config('currency.symbol')}}</span>)</label>
                                        <div class="input-group m-bot15">
                                            <input required readonly="readonly" type="text" name="total" class="form-control" id="total" placeholder="Total">
                                        </div>
                                    </div>
                                    {{ Form::submit('Verify & Save', ['class' => 'btn btn-success btn-lg']) }}
                                    @if ($verify_no > 1)
                                        <button type="button" class="btn btn-danger btn-lg" aria-label="Left Align" id="reset-items">
                                            <i class="fa fa-refresh"></i> Reset
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
    // ajax setup
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
    
    // initialize Reference Date datepicker
    $('#reference-date')
        .datepicker({ format: "{{ config('core.user_date_format') }}" })
        .datepicker('setDate', new Date("{{ $quote->reference_date }}"));

    // initialize Quote Date datepicker
    $('#invoicedate')
        .datepicker({ format: "{{ config('core.user_date_format') }}" })
        .datepicker('setDate', new Date("{{ $quote->invoicedate }}"));

    $('#date-0')
        .datepicker({ format: "{{ config('core.user_date_format') }}" })
        .datepicker('setDate', new Date());

    // job card row
    function jobCardRow(n) {
        return `
            <tr>
                <td><input type="text" class="form-control" name="reference[]" id="reference-${n}" required></td>
                <td><input type="text" class="form-control" name="date[]" id="date-${n}" required></td>
                <td><input type="text" class="form-control" name="technician[]" id="technician-${n}" required></td>
                <th><button class="btn btn-primary btn-md removeJc" type="button">Remove</button></th>
            </tr>
        `;
    }
    //job card row counter
    let jobCardNo = 1;
    // addjob card row
    $('#add-jobcard').click(function() {
        // append row
        const row = jobCardRow(jobCardNo);
        $('#jobcard tr:last').after(row);
        // initalize datepicker
        $('#date-'+jobCardNo)
            .datepicker({ format: "{{ config('core.user_date_format') }}"  })
            .datepicker('setDate', new Date());
        jobCardNo++;
    });
    // remove job card row
    $('#jobcard').on('click', '.removeJc', function() {
        if ($(this).is('.removeJc')) $(this).closest('tr').remove();
    });

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
                <td><input type="text" class="form-control" name="numbering[]" id="numbering-${val}" autocomplete="off"></td>
                <td><input type="text" class="form-control" name="product_name[]" placeholder="{{trans('general.enter_product')}}" id='itemname-${val}'></td>
                <td><input type="text" class="form-control" name="unit[]" id="unit-${val}" value=""></td>                
                <td><input type="text" class="form-control req amnt" name="product_qty[]" id="amount-${val}" onchange="qtyChange(event)" autocomplete="off"></td>
                <td><input type="text" class="form-control req prc" name="product_price[]" id="price-${val}" onchange="priceChange(event)" autocomplete="off"></td>
                <td><input type="text" class="form-control req prcrate" name="product_subtotal[]" id="rateinclusive-${val}" autocomplete="off" readonly></td>
                <td><strong><span class='ttlText' id="result-${val}">0</span></strong></td>
                <td class="text-center">${dropDown()}</td>
                <input type="hidden" name="item_id[]" value="0" id="itemid-${val}">
                <input type="hidden" name="product_id[]" value=0 id="productid-${val}">
                <input type="hidden" name="row_index[]" value="0" id="rowindex-${val}">
            </tr>
        `;
    }

    // product title row
    // with extra hidden input fields to imitate product row state
    function productTitleRow(val) {
        return `
            <tr>
                <td><input type="text" class="form-control" name="numbering[]" id="numbering-${val}" autocomplete="off" ></td>
                <td colspan="6"><input type="text"  class="form-control" name="product_name[]" id="itemname-${val}" placeholder="Enter Title Or Heading"></td>
                <td class="text-center">${dropDown()}</td>
                <input type="hidden" name="item_id[]" value="0" id="itemid-${val}">
                <input type="hidden" name="product_id[]" value="${val}" id="productid-${val}">
                <input type="hidden" name="unit[]" value="">
                <input type="hidden" name="product_qty[]" value="0">
                <input type="hidden" name="product_price[]" value="0">
                <input type="hidden" name="product_subtotal[]" value="0">
                <input type="hidden" name="row_index[]" value="0" id="rowindex-${val}">
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
            $('#numbering-'+i).val(item.numbering);
            $('#itemname-'+i).val(item.product_name);
            $('#unit-'+i).val(item.unit);                
            $('#amount-'+i).val(1);
            $('#price-'+i).val(item.product_price.toFixed(2));
            $('#rateinclusive-'+i).val(item.product_subtotal.toFixed(2));                
            $('#result-'+i).text(item.product_subtotal.toFixed(2));
        } else {
            const row = productTitleRow(cvalue);
            $('#quotation tr:last').after(row);
            // set default values
            $('#itemid-'+i).val(item.id);
            $('#productid-'+i).val(item.product_id);
            $('#numbering-'+i).val(item.numbering);
            $('#itemname-'+i).val(item.product_name);
        }
        cvalue++;
        totals();
    });    

    // On click Add Product
    $('#add-product').click(function() {
        // append row
        const row = productRow(cvalue);
        $('#quotation tr:last').after(row);
        // autocomplete on added product row
        $('#itemname-'+cvalue).autocomplete(autocompleteProp(cvalue));
        cvalue++;
    });
    // on click Add Title button
    $('#add-title').click(function() {
        // append row
        const row = productTitleRow(cvalue);
        $('#quotation tr:last').after(row);
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
            const row = $(this).closest('tr');
            row.remove();
            if (Number("{{ $verify_no }}") > 1) {
                if (confirm('Are you sure to delete this item?')) {
                    const itemId = row.find('input[name="item_id[]"]').val();
                    // delete product api call 
                    $.ajax({
                        url: baseurl + 'quotes/verified_item/' + itemId,
                        method: 'DELETE',
                    });
                }
            }            
        }
        totals();
    });

    // default tax
    const tax = "{{ $quote->tax_id }}";
    const taxInt = parseFloat(tax.replace(',', ''));
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
        productPrice = parseFloat(productPrice.replace(',', ''));

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
        productPrice = parseFloat(productPrice.replace(',', ''));

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