@extends ('core.layouts.app')

@section ('title', trans('labels.backend.quotes.management')." | Create Quote" )

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
                {{ Form::open(['route' => 'biller.quotes.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'POST', 'id' => 'create-quote']) }}
                    <div class="row">
                        <div class="col-sm-6 cmp-pnl">
                            <div id="customerpanel" class="inner-cmp-pnl">
                                <div class="form-group row">
                                    <div class="fcol-sm-12">                                        
                                        <h3 class="title pl-1">Create Quote</h3>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <label for="ref_type" class="caption">Search Lead</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                            <select class="form-control  round  select-box required" name="lead_id" id="lead_id">                                                 
                                                <option>-- Select Lead --</option>
                                                @foreach ($leads as $lead)
                                                    @php
                                                        $name = $lead->client_name;
                                                        if ($lead->client_status == "customer") {
                                                            $name = $lead->customer->company.' '. $lead->branch->name;                                                                
                                                        }
                                                    @endphp
                                                    <option value="{{ $lead['id'] }}">
                                                        {{$lead['reference']}} - {{$name}} - {{dateFormat($lead->date_of_request)}} - {{$lead->employee_id}} - {{$lead->title}}
                                                    </option>
                                                @endforeach                                                                                             
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class='col-md-6'>
                                        <div class='col m-1'>
                                            {{ Form::label( 'method', 'Print Type',['class' => 'col-12 control-label']) }}
                                            <div class="d-inline-block custom-control custom-checkbox mr-1">
                                                <input type="radio" class="custom-control-input bg-primary" name="print_type" id="colorCheck6" value="inclusive" checked="">
                                                <label class="custom-control-label" for="colorCheck6">VATInclusive</label>
                                            </div>
                                            <div class="d-inline-block custom-control custom-checkbox mr-1">
                                                <input type="radio" class="custom-control-input bg-purple" name="print_type" value="exclusive" id="colorCheck7">
                                                <label class="custom-control-label" for="colorCheck7">VATExclusive</label>
                                            </div>
                                            <input type="hidden" id="document_type" value="QUOTE" name="document_type">
                                        </div>
                                    </div>
                                    <div class="col-sm-3"><label for="pricing" class="caption"> Pricing</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span>
                                            </div>
                                            <select id="pricing" name="pricing" class="form-control round required">
                                                <option value="0">Default </option>
                                                @foreach($selling_prices as $selling_price)
                                                    <option value="{{$selling_price->id}}">{{$selling_price->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="invocieno" class="caption">{{trans('general.serial_no')}} #{{prefix(5)}}</label>
                                        <div class="input-group">
                                            <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                                            {{ Form::number('tid', @$last_quote->tid+1, ['class' => 'form-control round', 'placeholder' => trans('invoices.tid'), 'id' => 'tid']) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-6">
                                        <label for="attention" class="caption">Attention</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                            {{ Form::text('attention', null, ['class' => 'form-control round required', 'placeholder' => 'Attention','autocomplete'=>'false','id'=>'attention']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="prepared_by" class="caption"> Prepared By</label>
                                        {{ Form::text('prepared_by', null, ['class' => 'form-control round required', 'placeholder' => 'Prepaired By','autocomplete'=>'false','id'=>'prepared_by']) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 cmp-pnl">
                            <div class="inner-cmp-pnl">
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <h3 class="title">{{trans('quotes.properties')}}</h3>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="invocieno" class="caption">{{trans('general.reference')}} (Diagnosis JobCard)</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                            {{ Form::text('reference', null, ['class' => 'form-control round', 'placeholder' => trans('general.reference')]) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-4"><label for="reference_date" class="caption">Reference {{trans('general.date')}}</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                            {{ Form::text('reference_date', null, ['class' => 'form-control round required', 'placeholder' => trans('general.date'), 'data-toggle'=>'datepicker-rd', 'autocomplete'=>'false']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-4"><label for="invoicedate" class="caption">Quote {{trans('general.date')}}</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                            {{ Form::text('invoicedate', null, ['class' => 'form-control round required', 'placeholder' => trans('general.date'),'data-toggle'=>'datepicker-qd','autocomplete'=>'false']) }}
                                        </div>
                                    </div>                                    
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-4"><label for="revision" class="caption">Validity Period</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                            <select class="form-control round  select-box" name="validity" id="validity" data-placeholder="{{trans('tasks.assign')}}">
                                                <option value="0">On Reciept</option>
                                                <option value="14">Valid For 14 Days</option>
                                                <option value="30">Valid For 30 Days</option>
                                                <option value="45">Valid For 45 Days</option>
                                                <option value="60">Valid For 60 Days</option>
                                                <option value="90">Valid For 90 Days</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4"><label for="ref_type" class="caption">Currency <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                            <select class="form-control  select-box " name="currency" id="currency" data-placeholder="{{trans('tasks.assign')}}">
                                                <option value="0">Default</option>
                                                @foreach($currencies as $currency)
                                                    <option value="{{$currency->id}}">{{$currency->symbol}} - {{$currency->code}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4"><label for="client_ref" class="caption">Client Reference</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                            {{ Form::text('client_ref', null, ['class' => 'form-control round required', 'placeholder' => 'Client Reference']) }}
                                        </div>
                                    </div>                                                                          
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-4"><label for="source" class="caption">Quotation Terms <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                            <select id="term_id" name="term_id" class="form-control round  selectpicker required">
                                                <option value="">-- Select Term --</option>
                                                @foreach($terms as $term)
                                                    <option value="{{$term->id}}">{{$term->title}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="taxFormat" class="caption">Select {{trans('general.tax')}}</label>
                                        <select class="form-control round" name='tax_id' id="tax_id" onchange="onTaxChange(event);">
                                            <option value="0">Off</option>
                                            <option value="16">16% VAT</option>
                                            <option value="8">8% VAT</option>
                                        </select>
                                        <input type="hidden" name="tax_format" value="exclusive" id="tax_format">
                                    </div>
                                </div>
                            </div>
                        </div>                        
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-10">
                            <label for="subject" class="caption">Subject / Title</label>
                            {{ Form::text('notes', null, ['class' => 'form-control round required', 'placeholder' => 'Subject Or Title','autocomplete'=>'false','id'=>'subject']) }}
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
                                    {{ Form::submit('Generate', ['class' => 'btn btn-success btn-lg']) }}
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
    // initialize Reference Date datepicker
    $('[data-toggle="datepicker-rd"]')
        .datepicker({ format: "{{ config('core.user_date_format') }}" })
        .datepicker('setDate', new Date());

    // initialize Quote Date datepicker
    $('[data-toggle="datepicker-qd"]')
        .datepicker({ format: "{{ config('core.user_date_format') }}" })
        .datepicker('setDate', new Date());

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
                <td><input type="text" class="form-control" name="product_name[]" placeholder="{{trans('general.enter_product')}}" id='itemname-${val}' required></td>
                <td><input type="text" class="form-control" name="unit[]" id="unit-${val}"></td>                
                <td><input type="text" class="form-control req amnt" name="product_qty[]" id="amount-${val}" onchange="qtyChange(event)" autocomplete="off"></td>
                <td><input type="text" class="form-control req prc" name="product_price[]" id="price-${val}" onchange="priceChange(event)" autocomplete="off"></td>
                <td><input type="text" class="form-control req prcrate" name="product_subtotal[]" id="rateinclusive-${val}" autocomplete="off" readonly></td>
                <td><strong><span class='ttlText' id="result-${val}">0</span></strong></td>
                <td class="text-center">${dropDown()}</td>
                <input type="hidden" name="row_index[]" value="0" id="rowindex-${val}">
                <input type="hidden" name="product_id[]" value="0" id="productid-${val}">
                <input type="hidden" name="a_type[]" value="1" id="atype-${val}">
            </tr>
        `;
    }

    // product title row
    // with extra hidden input fields to imitate product row state
    function productTitleRow(val) {
        return `
            <tr>
                <td><input type="text" class="form-control" name="title_numbering[]" id="numbering-${val}" autocomplete="off" ></td>
                <td colspan="6"><input type="text"  class="form-control" name="product_title[]" placeholder="Enter Title Or Heading " titlename-${val}" required></td>
                <td class="text-center">${dropDown()}</td>
                <input type="hidden" name="product_id[]" value="${val}" id="customfieldid-${val}">
                <input type="hidden" name="unit[]" value="">
                <input type="hidden" name="product_qty[]" value="0">
                <input type="hidden" name="product_price[]" value="0">
                <input type="hidden" name="product_subtotal[]" value="0">
                <input type="hidden" name="row_index[]" value="0" id="rowindex-${val}">
                <input type="hidden" name="a_type[]" value="2" id="atype-${val}">
            </tr>
        `;
    }

    // product row counter
    let cvalue = 1;
    // default row
    const row = productRow(0);
    $('#quotation tr:last').after(row);
    $('#itemname-0').autocomplete(autocompleteProp(0));

    // on clicking Add Product button
    $('#add-product').click(function() {
        // append row
        const row = productRow(cvalue);
        $('#quotation tr:last').after(row);
        // autocomplete on added product row
        $('#itemname-'+cvalue).autocomplete(autocompleteProp(cvalue));
        cvalue++;
    });
    // on clicking Add Title button
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
        if ($(this).is('.removeProd')) $(this).closest('tr').remove();
        
        totals();
    });

    // on select Tax change
    let taxRate = 1;
    function onTaxChange(e) {
        const tax = Number(e.target.value); 
        if (tax === 0) {
            $('#tax_format').val('exclusive');
        } else {
            $('#tax_format').val('inclusive');
        }
        // loop throw product rows while adjusting values
        taxRate = (tax+100)/100;
        $('#quotation tr').each(function(i) {
            if (!i) return;
            const productQty = $(this).find('td').eq(3).children().val()
            if (productQty) {
                const productPrice = $(this).find('td').eq(4).children().val();

                const rateInclusive = taxRate * parseFloat(productPrice.replace(',', ''));
                $(this).find('td').eq(5).children().val(rateInclusive.toFixed(2));

                const rowAmount = productQty * parseFloat(rateInclusive);
                $(this).find('td').eq(6).find('.ttlText').text(rowAmount.toFixed(2))
            }
        });
        totals();
    }    

    // ajax setup
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
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