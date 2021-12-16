@extends ('core.layouts.app')

@php
    $query_str = request()->getQueryString();
    $part_title = preg_match('/page=copy/', $query_str) ? ' | Copy' : ' | Edit PI';
@endphp

@section ('title', 'PI Management' . $part_title )

@section('page-header')
    <h1>Edit PI</h1>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="content-header-title mb-0">PI MANAGEMENT</h4>
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
                    @if (isset($last_quote))
                        {{ Form::model($quote, ['route' => 'biller.quotes.store', 'class' => 'form-horizontal', 'method' => 'POST']) }}
                    @else
                        {{ Form::model($quote, ['route' => ['biller.quotes.update', $quote], 'class' => 'form-horizontal', 'method' => 'PATCH']) }}
                    @endif                    
                    <div class="row">
                        <div class="col-sm-6 cmp-pnl">
                            <div id="customerpanel" class="inner-cmp-pnl">
                                <div class="form-group row">
                                    <div class="fcol-sm-12">
                                        @if (isset($last_quote))
                                            <h3 class="title pl-1">Proforma Invoice Copy</h3>
                                        @else
                                            <h3 class="title pl-1">Edit Proforma Invoice</h3>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <label for="ref_type" class="caption">Leads</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                            <select class="form-control  round  select-box" name="lead_id" id="lead_id" required>                                                 
                                                <option value="0">-- Select Lead --</option>
                                                @foreach ($leads as $lead)
                                                    @php
                                                        $name = $lead->client_name;
                                                        $tid = 'Tkt-'.sprintf('%04d', $lead->reference);
                                                        if ($lead->client_status == "customer") {
                                                            $name = $lead->customer->company.' - '. $lead->branch->name;                                                                
                                                        }
                                                    @endphp
                                                    @if ($lead->id == $quote->lead_id)
                                                        <option value="{{ $lead->id }}" selected>{{ $tid }} - {{ $name }} - {{ $lead->title }}</option>
                                                    @else
                                                        <option value="{{ $lead->id }}">{{ $tid }} - {{ $name }} - {{ $lead->title }}</option>
                                                    @endif
                                                @endforeach                                                                                             
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <label for="ref_type" class="caption">Bank Details</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                            <select class="form-control  round  select-box" name="bank_id" id="bank_id" required>
                                                <option value="0">-- Select Bank --</option>
                                                @foreach ($banks as $bank) 
                                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class='col-md-6'>
                                        <div class='col m-1'>
                                            {{ Form::label('method', 'Print Type', ['class' => 'col-12 control-label']) }}
                                            <div class="d-inline-block custom-control custom-checkbox mr-1">
                                                <input type="radio" class="custom-control-input bg-primary" name="print_type" value="inclusive" id="colorCheck6">
                                                <label class="custom-control-label" for="colorCheck6">VAT-Inclusive</label>
                                            </div>
                                            <div class="d-inline-block custom-control custom-checkbox mr-1">
                                                <input type="radio" class="custom-control-input bg-purple" name="print_type" value="exclusive" id="colorCheck7" checked>
                                                <label class="custom-control-label" for="colorCheck7">VAT-Exclusive</label>
                                            </div>
                                            <input type="hidden" id="document_type" value="QUOTE" name="document_type">
                                        </div>
                                    </div>
                                    <div class="col-sm-3"><label for="pricing" class="caption">Pricing</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                            <select id="pricing" name="pricing" class="form-control round">
                                                <option value="0" selected>Default </option>
                                                @foreach ($selling_prices as $selling_price)
                                                    <option value="{{$selling_price->id}}">{{ $selling_price->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="invocieno" class="caption">#PI {{trans('general.serial_no')}} </label>
                                        <div class="input-group">
                                            <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>
                                            @if (isset($last_quote))
                                                {{ Form::text('tid', 'PI-' . sprintf('%04d', $last_quote->tid+1), ['class' => 'form-control round', 'id' => 'tid', 'disabled']) }}
                                                <input type="hidden" name="tid" value="{{ $last_quote->tid+1 }}">
                                            @else
                                                {{ Form::text('tid', 'PI-' . sprintf('%04d', $quote->tid) . $quote->revision, ['class' => 'form-control round', 'id' => 'tid', 'disabled']) }}
                                                <input type="hidden" name="tid" value="{{ $quote->tid }}">
                                            @endif
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
                                        {{ Form::text('prepared_by', null, ['class' => 'form-control round required', 'placeholder' => 'Prepared By','autocomplete'=>'false','id'=>'prepared_by']) }}
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
                                        <label for="invocieno" class="caption">Djc Reference</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                            {{ Form::text('reference', null, ['class' => 'form-control round', 'placeholder' => 'Djc Reference']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-4"><label for="reference_date" class="caption">Djc Reference Date</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                            {{ Form::text('reference_date', null, ['class' => 'form-control round required', 'placeholder' => trans('general.date'), 'data-toggle'=>'datepicker-rd', 'autocomplete'=>'false']) }}
                                        </div>
                                    </div>   
                                    <div class="col-sm-4"><label for="client_ref" class="caption">Client Ref / Callout ID</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                            {{ Form::text('client_ref', null, ['class' => 'form-control round', 'placeholder' => 'Client Reference', 'id' => 'client_ref', 'required']) }}
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
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-4"><label for="source" class="caption">Quotation Terms <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                            <select id="term_id" name="term_id" class="form-control round  selectpicker required">
                                                <option value="0">No Terms</option>
                                                @foreach($terms as $term)
                                                    <option value="{{$term->id}}">{{$term->title}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                    <label for="taxFormat" class="caption">Select {{trans('general.tax')}}</label>
                                        <select class="form-control round" name='tax_id' id="tax_id">
                                            <option value="0">Off</option>
                                            <option value="16">16% VAT</option>
                                            <option value="8">8% VAT</option>                                            
                                        </select>
                                        <input type="hidden" name="tax_format" id="tax_format">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-4"><label for="invoicedate" class="caption">Quote {{trans('general.date')}}</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                            {{ Form::text('invoicedate', null, ['class' => 'form-control round required', 'placeholder' => trans('general.date'),'data-toggle'=>'datepicker-qd','autocomplete'=>'false']) }}
                                        </div>
                                    </div>
                                    @if (!isset($last_quote))
                                        <div class="col-sm-4"><label for="revision" class="caption">Revision</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                                <select class="form-control round  select-box" name="revision" id="revision">
                                                    <option value="0">-- Select Revision --</option>
                                                    <option value="_r1">R1</option>
                                                    <option value="_r2">R2</option>
                                                    <option value="_r3">R3</option>
                                                    <option value="_r4">R4</option>
                                                    <option value="_r5">R5</option>
                                                </select>
                                            </div>
                                        </div>
                                    @endif                                      
                                </div>
                            </div>
                        </div>                        
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-12">
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
                                        <label>SubTotal ({{ config('currency.symbol') }})</label>
                                        <div class="input-group m-bot15">
                                            <input type="text" name="subtotal" id="subtotal" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label id="tax-label">{{ trans('general.total_tax') }}
                                            <span>16%</span>
                                            <span class="text-danger">VAT-Exclusive (print type)</span>
                                        </label>
                                        <div class="input-group m-bot15">
                                            <input type="text" name="tax" id="tax" class="form-control" readonly>
                                        </div>
                                    </div>                                    
                                    <div class="form-group">
                                        <label>{{trans('general.grand_total')}} (<span class="currenty lightMode">{{config('currency.symbol')}}</span>)</label>
                                        <div class="input-group m-bot15">
                                            <input type="text" name="total" class="form-control" id="total" readonly>
                                        </div>
                                    </div>                                    
                                    @if (isset($last_quote))
                                        {{ Form::submit('Generate', ['class' => 'btn btn-success btn-lg']) }}
                                    @else
                                        {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-lg']) }}
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

    // set default field values
    $('#bank_id').val("{{ $quote->bank_id }}");
    $('#pricing').val("{{ $quote->pricing }}");
    $('#validity').val("{{ $quote->validity }}");
    $('#currency').val("{{ $quote->currency }}");
    $('#term_id').val("{{ $quote->term_id }}");
    if (@json($quote->revision)) $('#revision').val("{{ $quote->revision }}");
    $('#tax_id').val("{{ $quote->tax_id }}");
    $('#client_ref').val("{{ $quote->client_ref }}");
    $('#tax_format').val("{{ $quote->tax_format }}");

    // initialize Reference Date datepicker
    $('[data-toggle="datepicker-rd"]')
        .datepicker({ format: "{{ config('core.user_date_format') }}" })
        .datepicker('setDate', new Date());

    // initialize Quote Date datepicker
    $('[data-toggle="datepicker-qd"]')
        .datepicker({ format: "{{ config('core.user_date_format') }}" })
        .datepicker('setDate', new Date());

    // on selecting lead
    $('#lead_id').change(function() {
        const leads = @json($leads);
        leads.forEach(v => {
            if (v.id == $(this).val()) {
                $('#subject').val(v.title);
                $('#client_ref').val(v.client_ref);
            }
        });
    });

    // Check if radio button is checked
    $('input[type="radio"]').change(function() {
        const $span = $('#tax-label').find('span').eq(1);
        if ($(this).is(':checked')) {
            if ($(this).val() === 'exclusive') {
                $span.text('VAT-Exclusive (print type)');
            } else if ($(this).val() === 'inclusive') {
                $span.text('VAT-Inclusive (print type)');
            }
        }
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
                <td><input type="text" class="form-control" name="product_name[]" placeholder="{{trans('general.enter_product')}}" id="itemname-${val}"></td>
                <td><input type="text" class="form-control" name="unit[]" id="unit-${val}" value=""></td>                
                <td><input type="text" class="form-control req amnt" name="product_qty[]" id="amount-${val}" onchange="qtyChange(event)" autocomplete="off"></td>
                <td><input type="text" class="form-control req prc" name="product_price[]" id="price-${val}" onchange="priceChange(event)" autocomplete="off"></td>
                <td><input type="text" class="form-control req prcrate" name="product_subtotal[]" id="rateinclusive-${val}" autocomplete="off" readonly></td>
                <td><strong><span class='ttlText' id="result-${val}">0</span></strong></td>
                <td class="text-center">${dropDown()}</td>
                <input type="hidden" name="item_id[]" value="0" id="itemid-${val}">
                <input type="hidden" name="product_id[]" value=0 id="productid-${val}">
                <input type="hidden" name="row_index[]" value="0" id="rowindex-${val}">
                <input type="hidden" name="a_type[]" value="1" id="atype-${val}">
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
                <input type="hidden" name="a_type[]" value="2" id="atype-${val}">
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
            $('#amount-'+i).val(parseFloat(item.product_qty));
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
        if ($(this).is('.removeProd')) {
            if (confirm('Are you sure to delete this product ?')) {
                const row = $(this).closest('tr');
                row.remove();
                const itemId = row.find('input[name="item_id[]"]').val();
                // delete product api call 
                if (Number(itemId)) {
                    $.ajax({
                        url: baseurl + 'quotes/delete_product/' + itemId,
                        dataType: "json",
                        method: 'DELETE',
                    });
                }
            }
        }

        totals();
    });

    // default tax
    const taxInt = Number($('#tax_id').val());

    const $span = $('#tax-label').find('span').eq(0);
    $('#tax_format').val('exclusive');
    $span.text('OFF');
    if (taxInt) {
        $('#tax_format').val('inclusive');
        $span.text(tax+'%');
    }
        
    let taxRate = (taxInt+100)/100;
    // on select Tax change
    $('#tax_id').change(function() {
        const tax = Number($(this).val()); 
        const $span = $('#tax-label').find('span').eq(0);

        $('#tax_format').val('exclusive');
        $span.text('OFF');
        if (tax) {
            $('#tax_format').val('inclusive');
            $span.text(tax+'%');
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
    });    

    // autocompleteProp returns autocomplete object properties
    function autocompleteProp(i) {
        return {
            source: function(request, response) {
                $.ajax({
                    url: baseurl + 'products/quotesearch/' + billtype,
                    dataType: "json",
                    method: 'post',
                    data: 'keyword=' + request.term 
                        + '&type=product_list&row_num=1&pricing=' + $("#pricing").val(),
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