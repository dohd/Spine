@extends ('core.layouts.app')

@section ('title', trans('labels.backend.quotes.management') . ' | ' . trans('labels.backend.quotes.create'))

@section('page-header')
<h1>
    {{ trans('labels.backend.quotes.management') }}
    <small>{{ trans('labels.backend.quotes.create') }}</small>
</h1>
@endsection

@section('content')
<div class="">
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
                <div class="card-content">
                    <div class="card-body">
                        <form method="get" id="data_form" class='create-quote'>
                            <div class="row">
                                <div class="col-sm-6 cmp-pnl">
                                    <div id="customerpanel" class="inner-cmp-pnl">
                                        <div class="form-group row">
                                            <div class="fcol-sm-12">
                                                <h3 class="title pl-1">Create Quote
                                                </h3>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-12"><label for="ref_type" class="caption">Search Lead
                                                </label>
                                                <div class="input-group">
                                                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                                    <select class="form-control  round  select-box required" name="lead_id" id="lead_id" data-placeholder="No Lead Selected">
                                                        <option>-- Select Lead --</option>
                                                        @foreach ($leads as $lead)
                                                            @php
                                                                if ($lead->client_status == "customer") {
                                                                    $name = $lead->customer->company.' '. $lead->branch->name;                                                                
                                                                } else {
                                                                    $name = $lead->client_name;
                                                                }
                                                            @endphp
                                                        <option value="{{$lead['id']}}">
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
                                                        <label class="custom-control-label" for="colorCheck6">VAT
                                                            Inclusive</label>
                                                    </div>
                                                    <div class="d-inline-block custom-control custom-checkbox mr-1">
                                                        <input type="radio" class="custom-control-input bg-purple" name="print_type" value="exclusive" id="colorCheck7">
                                                        <label class="custom-control-label" for="colorCheck7">VAT
                                                            Exclusive</label>
                                                    </div>
                                                    <input type="hidden" id="document_type" value="QUOTE" name="document_type">
                                                </div>
                                            </div>
                                            <div class="col-sm-3"><label for="pricing" class="caption"> Pricing</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span>
                                                    </div>
                                                    <select id="pricing" name="pricing" class="form-control round required  ">
                                                        <option value="0">Default </option>
                                                        @foreach($selling_prices as $selling_price)
                                                        <option value="{{$selling_price->id}}">{{$selling_price->name}}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-3"><label for="invocieno" class="caption">{{trans('general.serial_no')}}
                                                    #{{prefix(5)}}</label>
                                                <div class="input-group">
                                                    <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span>
                                                    </div>
                                                    {{ Form::number('tid', @$last_invoice->tid+1, ['class' => 'form-control round', 'placeholder' => trans('invoices.tid')]) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-6"><label for="attention" class="caption">
                                                    Attention</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span>
                                                    </div>
                                                    {{ Form::text('attention', null, ['class' => 'form-control round required', 'placeholder' => 'Attention','autocomplete'=>'false','id'=>'attention']) }}
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <label for="prepaired_by" class="caption"> Prepaired By</label>
                                                {{ Form::text('prepaired_by', null, ['class' => 'form-control round required', 'placeholder' => 'Prepaired By','autocomplete'=>'false','id'=>'prepaired_by']) }}
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
                                            <div class="col-sm-4"><label for="invocieno" class="caption">{{trans('general.reference')}} (Diagnosis Job
                                                    Card)</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span>
                                                    </div>
                                                    {{ Form::text('reference', null, ['class' => 'form-control round', 'placeholder' => trans('general.reference')]) }}
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="reference_date" class="caption">Reference {{trans('general.date')}}</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                                    {{ Form::text('reference_date', null, ['class' => 'form-control round required', 'placeholder' => trans('general.date'), 'data-toggle'=>'datepicker', 'autocomplete'=>'false']) }}
                                                </div>
                                            </div>
                                            <div class="col-sm-4"><label for="invoicedate" class="caption">Quote
                                                    {{trans('general.date')}}</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span>
                                                    </div>
                                                    {{ Form::text('invoicedate', null, ['class' => 'form-control round required', 'placeholder' => trans('general.date'),'data-toggle'=>'datepicker','autocomplete'=>'false']) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-4"><label for="valid_till" class="caption">Validity
                                                    Period</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span>
                                                    </div>
                                                    <select class="form-control round  select-box" name="validity" id="employee" data-placeholder="{{trans('tasks.assign')}}">
                                                        <option value="0">On Reciept</option>
                                                        <option value="14">Valid For 14 Days</option>
                                                        <option value="30">Valid For 30 Days</option>
                                                        <option value="45">Valid For 45 Days</option>
                                                        <option value="60">Valid For 60 Days</option>
                                                        <option value="90">Valid For 90 Days</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-4"><label for="ref_type" class="caption">Currency*</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span>
                                                    </div>
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
                                            <div class="col-sm-4"><label for="source" class="caption">Quotation
                                                    Terms*</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span>
                                                    </div>
                                                    <select id="items" name="term_id" class="form-control round  selectpicker required  ">
                                                        <option value="0">No Terms</option>
                                                        @foreach($terms as $term)
                                                            <option value="{{$term->id}}">{{$term->title}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="taxFormat" class="caption">{{trans('general.tax')}}</label>
                                                <select class="form-control round" onchange="changeTaxFormat()" id="taxFormat">
                                                    @php
                                                        $tax_format='exclusive';
                                                        $tax_format_id=0;
                                                        $tax_format_type='exclusive';
                                                    @endphp
                                                    @foreach($additionals as $additional_tax)
                                                        @php
                                                            if ($additional_tax->id == $defaults[4][0]['feature_value'] && $additional_tax->class == 1) {
                                                                echo '<option value="'.numberFormat($additional_tax->value).'" data-type1="'.$additional_tax->type1.'" data-type2="'.$additional_tax->type2.'" data-type3="'.$additional_tax->type3.'" data-type4="'.$additional_tax->id.'" selected>
                                                                    --'.$additional_tax->name.'--</option>';
                                                                $tax_format=$additional_tax->type2;
                                                                $tax_format_id=$additional_tax->id;
                                                                $tax_format_type=$additional_tax->type3;
                                                            }
                                                        @endphp
                                                        {!! $additional_tax->class == 1 ? "<option value='".numberFormat($additional_tax->value)."' data-type1='$additional_tax->type1' data-type2='$additional_tax->type2' data-type3='$additional_tax->type3' data-type4='$additional_tax->id'>$additional_tax->name</option>"
                                                        : "" !!}
                                                    @endforeach
                                                    <option value="0" data-type1="%" data-type2="off" data-type3="off">
                                                        {{trans('general.off')}}
                                                    </option>
                                                </select>
                                            </div>
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
                            <div id="saman-row">
                                <table id="quotation" class="table-responsive tfr my_stripe_single" style="padding-bottom: 100px;">
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
                                    <tbody>
                                        <tr>
                                            <td><input type="text" class="form-control" name="numbering[]" id="numbering-0" autocomplete="off"></td>
                                            <td><input type="text" class="form-control" name="product_name[]" placeholder="{{trans('general.enter_product')}}" id='itemname-0'>
                                            </td>
                                            <td><select class="form-control unit" data-uid="0" name="u_m[]" style="display: none">
                                                </select></td>
                                            <td><input type="text" class="form-control req amnt" name="product_qty[]" id="amount-0" onkeypress="return isNumber(event)" onkeyup="qtRowTotal('0'), qtyBillUpyog()" autocomplete="off"><input type="hidden" id="alert-0" value="" name="alert[]"></td>
                                            <td><input type="text" class="form-control req prc" name="product_price[]" id="price-0" onkeypress="return isNumber(event)" onkeyup="qtRowTotal('0'), qtyBillUpyog()" autocomplete="off"></td>
                                            <td><input type="text" class="form-control req prcrate" name="rate_inclusive[]" id="rate_inclusive-0" autocomplete="off" readonly></td>
                                            <td><span class="currenty">{{config('currency.symbol')}}</span>
                                                <strong><span class='ttlText' id="result-0">0</span></strong>
                                            </td>
                                            <td class="text-center">
                                                <div class="dropdown"><button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>
                                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton"><a class="dropdown-item removeProd" href="javascript:void(0);" data-rowid="0">Remove</a><a class="dropdown-item up" href="javascript:void(0);">Up</a><a class="dropdown-item down" href="javascript:void(0);">Down</a></div>
                                                </div>
                                            </td>
                                            <input type="hidden" name="total_tax[]" id="taxa-0" value="0">
                                            <input type="hidden" name="total_discount[]" id="disca-0" value="0">
                                            <input type="hidden" class="ttInput" name="product_subtotal[]" id="total-0" value="0">
                                            <input type="hidden" name="product_exclusive[]" id="totalinc-0" value="0">
                                            <input type="hidden" class="pdIn" name="product_id[]" id="pid-0" value="0">
                                            <input type="hidden" name="item_or_title[]" id="item_or_title-0" value="0">
                                            <input type="hidden" name="unit[]" id="unit-0" value="">
                                            <input type="hidden" name="code[]" id="hsn-0" value="">
                                            <input type="hidden" name="a_type[]" id="a_type-0" value="1">
                                        </tr>
                                        <tr class="last-item-row sub_c" style="display: none"></tr>
                                    </tbody>
                                </table>
                                <div class="row">
                                    <div class="col-md-8 col-xs-7 payment-method last-item-row sub_c">
                                        <div id="load_instruction" class="col-md-6 col-lg-12 mg-t-20 mg-lg-t-0-force"></div>
                                        <button type="button" class="btn btn-success" aria-label="Left Align" id="addqproduct">
                                            <i class="fa fa-plus-square"></i> Add Product
                                        </button>
                                        <button type="button" class="btn btn-primary" aria-label="Left Align" id="addqtitle">
                                            <i class="fa fa-plus-square"></i> Add Title
                                        </button>
                                    </div>
                                    <div class="col-md-4 col-xs-5 invoice-block pull-right">
                                        <div class="unstyled amounts">
                                            <div class="form-group">
                                                <label>SubTotal
                                                    (<span class="currenty lightMode">{{config('currency.symbol')}}</span>)</label>
                                                <div class="input-group m-bot15">
                                                    <input type="text" required readonly="readonly" name="subtotal" id="exclusive" class="form-control">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>{{trans('general.total_tax')}}</label>
                                                <div class="input-group m-bot15">
                                                    <input type="text" required readonly="readonly" name="tax" id="taxr" class="form-control">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>Total Discount :</label>
                                                <div class="input-group m-bot15">
                                                    <input readonly="readonly" type="text" value="0" name="after_disc" class="form-control" id="discs" placeholder="Discount" onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>{{trans('general.grand_total')}}
                                                    (<span class="currenty lightMode">{{config('currency.symbol')}}</span>)</label>
                                                <div class="input-group m-bot15">
                                                    <input required readonly="readonly" type="text" name="total" class="form-control" id="invoiceyoghtml" placeholder="Total" onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <input type="submit" class="btn btn-success sub-btn btn-lg" value="{{trans('general.generate')}}" id="submit-data" data-loading-text="Creating...">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">{!! $fields !!}</div>
                                </div>
                            </div>
                            <input type="hidden" value="new_i" id="inv_page">
                            <input type="hidden" value="{{route('biller.quotes.store')}}" id="action-url">
                            <input type="hidden" value="search" id="billtype">
                            <input type="hidden" value="0" name="counter" id="ganak">
                            <input type="hidden" value="{{$tax_format}}" name="tax_format_static" id="tax_format">
                            <input type="hidden" value="{{$tax_format_type}}" name="tax_format" id="tax_format_type">
                            <input type="hidden" value="{{$tax_format_id}}" name="tax_id" id="tax_format_id">
                            <input type="hidden" value="{{$discount_format}}" name="discount_format" id="discount_format">
                            @if ($defaults[4][0]->ship_tax['id'] > 0) 
                                <input type='hidden' value="{{ numberFormat($defaults[4][0]->ship_tax['value']) }}" name='ship_rate' id='ship_rate'><input type='hidden' value="{{$defaults[4][0]->ship_tax['type2']}}" name='ship_tax_type' id='ship_taxtype'>
                            @else
                                <input type='hidden' value='{{numberFormat(0)}}' name='ship_rate' id='ship_rate'><input type='hidden' value='none' name='ship_tax_type' id='ship_taxtype'>
                            @endif
                            <input type="hidden" value="0" name="ship_tax" id="ship_tax">
                            <input type="hidden" value="0" id="custom_discount">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include("focus.modal.customer")
@endsection

@section('extra-scripts')

<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

<script type="text/javascript">
    $('form').submit(function(e) {
        alert('submit intercepted');
        e.preventDefault();
        console.log($(this).serializeArray());
    });


    editor();

    $('[data-toggle="datepicker"]')
        .datepicker({ format: "{{ config('core.user_date_format') }}" })
        .datepicker('setDate', new Date());

    // ajax setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#lead_id").select2();
    var billtype = $('#billtype').val();
    $('#addqproduct').on('click', function() {
        var cvalue = parseInt($('#ganak').val()) + 1;
        var nxt = parseInt(cvalue);
        $('#ganak').val(nxt);
        var functionNum = "'" + cvalue + "'";
        count = $('#saman-row div').length;
        //project details
        var project_id = $('#project_id option:selected').val();
        if (project_id = "") {
            var customer_id = "";
            var branch_id = "";
            var project_description = "";
        } else {
            var customer_id = $('#project_id option:selected').attr('data-type1');
            var branch_id = $('#project_id option:selected').attr('data-type2');
            var project_description = $('#project_id option:selected').attr('data-type3');
        }
        //product row
        var data = '<tr><td><input type="text" class="form-control" name="numbering[]"id="numbering-' + cvalue +
            '" autocomplete="off" ></td><td><input type="text" class="form-control" name="product_name[]" placeholder="Enter Product name" id="itemname-' +
            cvalue + '"></td><td><select class="form-control unit" data-uid="' + cvalue +
            '" name="u_m[]"style="display: none"></select></td><td><input type="text" class="form-control req amnt" name="product_qty[]" id="amount-' +
            cvalue + '" onkeypress="return isNumber(event)" onkeyup="qtRowTotal(' + functionNum +
            '), qtyBillUpyog()" autocomplete="off"  ><input type="hidden" id="alert-' + cvalue +
            '" value=""  name="alert[]"> </td> <td><input type="text" class="form-control req prc" name="product_price[]" id="price-' +
            cvalue + '" onkeypress="return isNumber(event)" onkeyup="qtRowTotal(' + functionNum +
            '), qtyBillUpyog()" autocomplete="off"></td><td><input type="text" class="form-control req prcrate" name="rate_inclusive[]" id="rate_inclusive-' +
            cvalue + '" onkeypress="return isNumber(event)" onkeyup="qtRowTotal(' + functionNum +
            '), qtyBillUpyog()" autocomplete="off" readonly="readonly"></td><td><span class="currenty">' +
            currency + '</span> <strong><span class=\'ttlText\' id="result-' + cvalue +
            '">0</span></strong></td><td class="text-center"><div class="dropdown"><button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button><div class="dropdown-menu" aria-labelledby="dropdownMenuButton"><a class="dropdown-item removeProd" href="javascript:void(0);" data-rowid="' +
            cvalue +
            '" >Remove</a><a class="dropdown-item up" href="javascript:void(0);">Up</a><a class="dropdown-item down" href="javascript:void(0);">Down</a></div></div></td><input type="hidden" name="total_tax[]" id="taxa-' +
            cvalue + '" value="0"><input type="hidden" name="total_discount[]" id="disca-' + cvalue +
            '" value="0"><input type="hidden" class="ttInput" name="product_subtotal[]" id="total-' + cvalue +
            '" value="0"> <input type="hidden" class="pdIn" name="product_id[]" id="pid-' + cvalue +
            '" value="0"> <input type="hidden" name="unit[]" id="unit-' + cvalue +
            '" attr-org="" value=""> <input type="hidden" name="hsn[]" id="hsn-' + cvalue +
            '" value=""><input type="hidden" name="unit_m[]" id="unit_m-' + cvalue +
            '" value="1"> <input type="hidden" name="serial[]" id="serial-' + cvalue +
            '" value=""> <input type="hidden" name="a_type[]" id="a_type-' + cvalue +
            '" value="1"><input type="hidden" name="item_or_title[]" id="item_or_title-0" value="0"><input type="text"  name="product_exclusive[]" id="totalinc-' +
            cvalue + '" value="0" ></tr>';
        //ajax request
        // $('#saman-row').append(data);
        $('tr.last-item-row').before(data);
        editor();
        row = cvalue;
        $('#itemname-' + cvalue).autocomplete({
            source: function(request, response) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: baseurl + 'products/quotesearch/' + billtype,
                    dataType: "json",
                    method: 'post',
                    data: 'keyword=' + request.term + '&type=product_list&row_num=1&pricing=' +
                        $("#pricing").val(),
                    success: function(data) {
                        console.log('product quote search', data)
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
                id_arr = $(this).attr('id');
                id = id_arr.split("-");
                var tax_format = $('#tax_format').val();
                var t_r = ui.item.data.taxrate;
                var custom = accounting.unformat($("#taxFormat option:selected").val(), accounting
                    .settings.number.decimal);
                if (custom > 0) {
                    t_r = custom;
                }
                if (tax_format == "exclusive") {
                    var rate_inclusive = accounting.unformat(ui.item.data.price) * (100 + t_r) / 100;
                } else {
                    var rate_inclusive = ui.item.data.price;
                }
                var discount = ui.item.data.disrate;
                var custom_discount = $('#custom_discount').val();
                var project_id = $('#project_id option:selected').val();
                if (project_id = "") {
                    var customer_id = "";
                    var branch_id = "";
                    var project_description = "";
                } else {
                    var customer_id = $('#project_id option:selected').attr('data-type1');
                    var branch_id = $('#project_id option:selected').attr('data-type2');
                    var project_description = $('#project_id option:selected').attr('data-type3');
                }
                var project_id = $('#project_id option:selected').val();
                if (custom_discount > 0) discount = deciFormat(custom_discount);
                $('#price-' + id[1]).val(accounting.formatNumber(ui.item.data.price));
                $('#rate_inclusive-' + id[1]).val(accounting.formatNumber(rate_inclusive));
                $('#pid-' + id[1]).val(ui.item.data.id);
                $('#vat-' + id[1]).val(accounting.formatNumber(t_r));
                $('#discount-' + id[1]).val(accounting.formatNumber(discount));
                $('#unit-' + id[1]).val(ui.item.data.unit).attr('attr-org', ui.item.data.unit);
                $('#hsn-' + id[1]).val(ui.item.data.code);
                $('#alert-' + id[1]).val(ui.item.data.alert);
                $('#serial-').val(ui.item.data.serial);
                $('#project-' + id[1]).val(project_description);
                $('#project_id-' + id[1]).val(project_id);
                $('#client_id-' + id[1]).val(customer_id);
                $('#branch_id-' + id[1]).val(branch_id);
                qtRowTotal(cvalue);
                qtyBillUpyog();
                if (typeof unit_load === "function") {
                    unit_load();
                    $('.unit').show();
                }
            },
            create: function(e) {
                $(this).prev('.ui-helper-hidden-accessible').remove();
            }
        });
    });
    $("#quotation").on("click", ".up,.down,.removeProd", function() {
        var row = $(this).parents("tr:first");
        if ($(this).is('.up')) {
            row.insertBefore(row.prev());
        } else if ($(this).is('.down')) {
            row.insertAfter(row.next());
        }
        if ($(this).is('.removeProd')) {
            var pidd = $(this).closest('tr').find('.item_pdIn').val();
            var retain = $(this).closest('tr').attr('data-re');
            var pqty = $(this).closest('tr').find('.item_amnt').val();
            pqty = pidd + '-' + pqty;
            if (retain) {
                $('<input>').attr({
                    type: 'hidden',
                    id: 'restock',
                    name: 'restock[]',
                    value: pqty
                }).appendTo('form');
            }
            $(this).closest('tr').remove();
            $('#d' + $(this).closest('tr').find('.item_pdIn').attr('id')).closest('tr').remove();
            $('.item_amnt').each(function(index) {
                expRowTotal(index);
                expBillUpyog();
            });
            return false;
        }
    });

    $('#addqtitle').on('click', function() {
        var cvalue = parseInt($('#ganak').val()) + 1;
        var nxt = parseInt(cvalue);
        $('#ganak').val(nxt);
        var functionNum = "'" + cvalue + "'";
        count = $('#saman-row div').length;
        //project details
        //product row
        var data = '<tr><td><input type="text" class="form-control" name="numbering[]"id="numbering-' + cvalue +
            '" autocomplete="off" ></td> <td colspan="6"><input type="text"  class="form-control" name="product_name[]" placeholder="Enter Title Or Heading " titlename-' +
            cvalue +
            '"></td><td class="text-center"><div class="dropdown"><button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button><div class="dropdown-menu" aria-labelledby="dropdownMenuButton"><a class="dropdown-item removeProd" href="javascript:void(0);" data-rowid="' +
            cvalue +
            '" >Remove</a><a class="dropdown-item up" href="javascript:void(0);">Up</a><a class="dropdown-item down" href="javascript:void(0);">Down</a></div></div></td><input type="hidden" name="item_or_title[]" id="item_or_title-' +
            cvalue + '" value="1"> <input type="hidden" name="a_type[]" id="a_type-' + cvalue +
            '" value="2"><input type="hidden" name="product_id[]" id="product_id-' + cvalue +
            '" value="0"><input type="hidden" name="product_qty[]" id="product_qty-' + cvalue +
            '" value="0"><input type="hidden" name="product_subtotal[]" id="product_subtotal-' + cvalue +
            '" value="0"><input type="hidden" name="product_price[]" id="product_price-' + cvalue +
            '" value="0"><input type="hidden" name="total_tax[]" id="total_tax-' + cvalue +
            '" value="0"><input type="hidden" name="total_discount[]" id="total_discount-' + cvalue +
            '" value="0"><input type="hidden" name="product_exclusive[]" id="total_discount-' + cvalue +
            '" value="0"></tr>';
        //ajax request
        // $('#saman-row').append(data);
        $('tr.last-item-row').before(data);
        editor();
        row = cvalue;
        $('#productname-' + cvalue).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: baseurl + 'products/search/' + billtype,
                    dataType: "json",
                    method: 'post',
                    data: 'keyword=' + request.term + '&type=product_list&row_num=' + row +
                        '&wid=' + $("#s_warehouses option:selected").val() + '&serial_mode=' +
                        $("#serial_mode:checked").val(),
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
                id_arr = $(this).attr('id');
                id = id_arr.split("-");
                var t_r = ui.item.data.taxrate;
                var custom = accounting.unformat($("#taxFormat option:selected").val(), accounting
                    .settings.number.decimal);
                if (custom > 0) {
                    t_r = custom;
                }
                var discount = ui.item.data.disrate;
                var dup;
                var custom_discount = $('#custom_discount').val();
                if (custom_discount > 0) discount = deciFormat(custom_discount);
                $('.pdIn').each(function() {
                    if ($(this).val() == ui.item.data.id) dup = true;
                });
                if (dup) {
                    alert('Already Exists!!');
                    return;
                }
                $('#amount-' + id[1]).val(1);
                $('#price-' + id[1]).val(accounting.formatNumber(ui.item.data.price));
                $('#pid-' + id[1]).val(ui.item.data.id);
                $('#vat-' + id[1]).val(accounting.formatNumber(t_r));
                $('#discount-' + id[1]).val(accounting.formatNumber(discount));
                $('#unit-' + id[1]).val(ui.item.data.unit).attr('attr-org', ui.item.data.unit);
                $('#hsn-' + id[1]).val(ui.item.data.code);
                $('#alert-' + id[1]).val(ui.item.data.alert);
                $('#serial-' + id[1]).val(ui.item.data.serial);
                $('#dpid-' + id[1]).summernote('code', ui.item.data.product_des);
                $("#project-" + id[1]).val(project_description);
                $("#project_id-" + id[1]).val(project_id);
                $("#client_id-" + id[1]).val(customer_id);
                $("#branch_id-" + id[1]).val(branch_id);
                qtRowTotal(cvalue);
                qtyBillUpyog();
                if (typeof unit_load === "function") {
                    unit_load();
                    $('.unit').show();
                }
            },
            create: function(e) {
                $(this).prev('.ui-helper-hidden-accessible').remove();
            }
        });
    });

    $("#quotation").on("click", ".up,.down,.removeProd", function() {
        var row = $(this).parents("tr:first");
        if ($(this).is('.up')) {
            row.insertBefore(row.prev());
        } else if ($(this).is('.down')) {
            row.insertAfter(row.next());
        }
        if ($(this).is('.removeProd')) {
            var pidd = $(this).closest('tr').find('.item_pdIn').val();
            var retain = $(this).closest('tr').attr('data-re');
            var pqty = $(this).closest('tr').find('.item_amnt').val();
            pqty = pidd + '-' + pqty;
            if (retain) {
                $('<input>').attr({
                    type: 'hidden',
                    id: 'restock',
                    name: 'restock[]',
                    value: pqty
                }).appendTo('form');
            }
            $(this).closest('tr').remove();
            $('#d' + $(this).closest('tr').find('.item_pdIn').attr('id')).closest('tr').remove();
            $('.item_amnt').each(function(index) {
                expRowTotal(index);
                expBillUpyog();
            });
            return false;
        }
    });
    
    $("#lead_id").change(function() {
        $.ajax({
            type: "POST",
            url: baseurl + 'leads/lead_search',
            data: 'keyword=' + $(this).val(),
            success: function(data) {
                $("#subject").val(data.note);
            }
        });
    });
    $('#itemname-0').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: baseurl + 'products/quotesearch/' + billtype,
                dataType: "json",
                method: 'post',
                data: 'keyword=' + request.term + '&type=product_list&row_num=1&pricing=' + $(
                    "#pricing").val(),
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
            var tax_format = $('#tax_format').val();
            var t_r = ui.item.data.taxrate;
            var custom = accounting.unformat($("#taxFormat option:selected").val(), accounting.settings.number
                .decimal);
            if (custom > 0) {
                t_r = custom;
            }
            if (tax_format == "exclusive") {
                var rate_inclusive = accounting.unformat(ui.item.data.price) * (100 + t_r) / 100;
            } else {
                var rate_inclusive = ui.item.data.price;
            }
            var discount = ui.item.data.disrate;
            var custom_discount = $('#custom_discount').val();
            //project details
            var project_id = $('#project_id option:selected').val();
            if (project_id = "") {
                var customer_id = "";
                var branch_id = "";
                var project_description = "";
            } else {
                var customer_id = $('#project_id option:selected').attr('data-type1');
                var branch_id = $('#project_id option:selected').attr('data-type2');
                var project_description = $('#project_id option:selected').attr('data-type3');
            }
            var project_id = $('#project_id option:selected').val();
            if (custom_discount > 0) discount = deciFormat(custom_discount);
            //$('#amount-0').val(1);
            $('#price-0').val(accounting.formatNumber(ui.item.data.price));
            $('#rate_inclusive-0').val(accounting.formatNumber(rate_inclusive));
            $('#pid-0').val(ui.item.data.id);
            $('#vat-0').val(accounting.formatNumber(t_r));
            $('#discount-0').val(accounting.formatNumber(discount));
            $('#unit-0').val(ui.item.data.unit).attr('attr-org', ui.item.data.unit);
            $('#hsn-0').val(ui.item.data.code);
            $('#alert-0').val(ui.item.data.alert);
            $('#serial-0').val(ui.item.data.serial);
            $('#project-0').val(project_description);
            $('#project_id-0').val(project_id);
            $('#client_id-0').val(customer_id);
            $('#branch_id-0').val(branch_id);
            $('.unit').show();
            unit_load();
            rowTotal(0);
            billUpyog();
            $('#dpid-0').summernote('code', ui.item.data.product_des);
        }
    });

    var qtRowTotal = function(numb) {
        //most res
        var result;
        var page = '';
        var totalValue = 0;
        var amountVal = accounting.unformat($("#amount-" + numb).val(), accounting.settings.number.decimal);
        var priceVal = accounting.unformat($("#price-" + numb).val(), accounting.settings.number.decimal);
        var discountVal = accounting.unformat($("#discount-" + numb).val(), accounting.settings.number.decimal);
        var vatVal = accounting.unformat($("#vat-" + numb).val(), accounting.settings.number.decimal);
        var taxo = 0;
        var disco = 0;
        var totalPrice = amountVal.toFixed(two_fixed) * priceVal;
        var tax_status = $("#taxFormat option:selected").attr('data-type2');
        var disFormat = $("#discount_format").val();
        if ($("#inv_page").val() == 'new_i' && formInputGet("#pid", numb) > 0) {
            var alertVal = accounting.unformat($("#alert-" + numb).val(), accounting.settings.number.decimal);
            if (alertVal > 0 && alertVal <= +amountVal) {
                var aqt = alertVal - amountVal;
                alert('Low Stock! ' + accounting.formatNumber(aqt));
            }
        }
        var custom = accounting.unformat($("#taxFormat option:selected").val(), accounting.settings.number.decimal);
        if (tax_status == 'exclusive') {
            var Inpercentage = precentCalc(totalPrice, custom); //tax amount per row
            var InpercentageExclusive = precentCalc(priceVal, custom); //tax amount per row
            totalValue = totalPrice + Inpercentage;
            var priceInc = priceVal + InpercentageExclusive;
            taxo = accounting.formatNumber(Inpercentage);
        } else if (tax_status == 'inclusive') {
        }
        //console.log(priceVal);
        if (priceVal < 0) {
            disco = priceInc * -1;
        }
        $("#result-" + numb).html(accounting.formatNumber(totalValue));
        $("#taxa-" + numb).val(taxo);
        $("#rate_inclusive-" + numb).val(accounting.formatNumber(priceInc));
        $("#texttaxa-" + numb).text(taxo);
        $("#disca-" + numb).val(disco);
        $("#total-" + numb).val(accounting.formatNumber(totalValue));
        $("#totalinc-" + numb).val(accounting.formatNumber(totalPrice));
        qtyBillUpyog();
    };

    var qtyBillUpyog = function() {
        var out = 0;
        var disc_val = accounting.unformat($("#discs").val(), accounting.settings.number.decimal);;
        if (disc_val) {
            out = accounting.unformat(disc_val, accounting.settings.number.decimal);
            out = parseFloat(out).toFixed(two_fixed);
            $('#disc_final').html(accounting.formatNumber(out));
            $('#after_disc').val(accounting.formatNumber(out));
        } else {
            $('#disc_final').html(0);
        }

        var totalBillVal = accounting.formatNumber(qtySamanYog() + shipTot() - coupon() - out);
        $("#mahayog").html(totalBillVal);
        $("#subttlform").val(accounting.formatNumber(qtySamanYog()));
        $("#invoiceyoghtml").val(totalBillVal);
        $("#bigtotal").html(totalBillVal);
        $('#keyword').val('');
    };

    //product total
    var qtySamanYog = function() {
        var itempriceList = [];
        var idList = [];
        var r = 0;
        $('.ttInput').each(function() {
            var vv = accounting.unformat($(this).val(), accounting.settings.number.decimal);
            var vid = $(this).attr('id');
            vid = vid.split("-");
            itempriceList.push(vv);
            idList.push(vid[1]);
            r++;
        });

        var sum = 0;
        var taxc = 0;
        var discs = 0;
        var totalInc = 0;
        for (var z = 0; z < idList.length; z++) {
            var x = idList[z];
            if (itempriceList[z] > 0) {
                sum += itempriceList[z];
            }
            var t1 = accounting.unformat($("#taxa-" + x).val(), accounting.settings.number.decimal);
            var d1 = accounting.unformat($("#disca-" + x).val(), accounting.settings.number.decimal);
            var d2 = accounting.unformat($("#totalinc-" + x).val(), accounting.settings.number.decimal);
            //if (t1 > 0) {
            taxc += t1;
            //  }
            if (d1 > 0) {
                discs += d1;
            }

            if (d2 > 0) {
                totalInc += d2;
            }
        }

        $("#discs").val(accounting.formatNumber(discs));
        $("#taxr").val(accounting.formatNumber(taxc));
        $("#exclusive").val(accounting.formatNumber(totalInc));

        return accounting.unformat(sum, accounting.settings.number.decimal);
    };
</script>
@endsection