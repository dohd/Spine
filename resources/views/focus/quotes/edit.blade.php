@extends ('core.layouts.app')

@php
    $query_str = request()->getQueryString();
    $part_title = preg_match('/page=copy/', $query_str) ? ' | Copy' : ' | Edit Quote';
@endphp

@section ('title', trans('labels.backend.quotes.management') . $part_title)

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
                    @if (@$last_quote->tid)
                        {{ Form::model($quote, ['route' => 'biller.quotes.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'POST', 'id' => 'create-quote']) }}
                    @else
                        {{ Form::model($quote, ['route' => ['biller.quotes.update', $quote], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'edit-quote']) }}
                    @endif
                    <div class="row">
                        <div class="col-sm-6 cmp-pnl">
                            <div id="customerpanel" class="inner-cmp-pnl">
                                <div class="form-group row">
                                    <div class="fcol-sm-12">
                                        @if (@$last_quote->tid)
                                            <h3 class="title pl-1">Quote Copy</h3>
                                        @else
                                            <h3 class="title pl-1">Edit Quote</h3>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <label for="ref_type" class="caption">Search Ticket</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                            <select class="form-control  round  select-box" name="lead_id" id="lead_id" required>                                                 
                                                <option value="">-- Select Ticket --</option>
                                                @foreach ($leads as $lead)
                                                    @php
                                                        $tid = 'Tkt-'.sprintf('%04d', $lead->reference);
                                                        $name =  isset($lead->customer) ? $lead->customer->company : $lead->client_name;
                                                        $branch = isset($lead->branch) ? $lead->branch->name : '';
                                                        if ($name && $branch) $name .= ' - ' . $branch; 
                                                    @endphp
                                                    <option 
                                                        value="{{ $lead->id }}" 
                                                        {{ $lead->id == $quote->lead_id ? 'selected' : '' }}
                                                        title="{{ $lead->title }}"
                                                        client_ref="{{ $lead->client_ref }}"
                                                    >
                                                        {{ $tid }} - {{ $name }} - {{ $lead->title }}
                                                    </option>
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
                                                <input type="radio" class="custom-control-input bg-primary" name="print_type" value="inclusive" id="colorCheck6" {{ @$quote->print_type == 'inclusive' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="colorCheck6">VATInclusive</label>
                                            </div>
                                            <div class="d-inline-block custom-control custom-checkbox mr-1">
                                                <input type="radio" class="custom-control-input bg-purple" name="print_type" value="exclusive" id="colorCheck7" {{ @$quote->print_type == 'exclusive' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="colorCheck7">VATExclusive</label>
                                            </div>
                                            <input type="hidden" id="document_type" value="QUOTE" name="document_type">
                                        </div>
                                    </div>
                                    <div class="col-sm-3"><label for="pricing" class="caption"> Pricing</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span>
                                            </div>
                                            <select id="pricing" name="pricing" class="form-control round">
                                                <option value="0">Default</option>
                                                @foreach($selling_prices as $selling_price)
                                                    <option value="{{ $selling_price->id }}" {{ $quote->pricing == $selling_price->id ? 'selected' : '' }}>
                                                        {{$selling_price->name}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="invocieno" class="caption">#{{prefix(5)}} {{trans('general.serial_no')}}</label>
                                        <div class="input-group">
                                            <div class="input-group-text"><span class="fa fa-list" aria-hidden="true"></span></div>                                            
                                            @if (isset($last_quote))
                                                {{ Form::text('tid', 'QT-'.sprintf('%04d', $last_quote->tid+1), ['class' => 'form-control round', 'disabled']) }}
                                                <input type="hidden" name="tid", value="{{ $last_quote->tid+1 }}">
                                            @else
                                                {{ Form::text('tid', 'QT-' . sprintf('%04d', $quote->tid) . $quote->revision, ['class' => 'form-control round', 'disabled']) }}
                                                <input type="hidden" name="tid", value="{{ $quote->tid }}">
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
                                            {{ Form::text('reference', null, ['class' => 'form-control round', 'placeholder' => trans('general.reference'), 'id' => 'reference']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-4"><label for="reference_date" class="caption">Djc Reference Date</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                            {{ Form::text('reference_date', null, ['class' => 'form-control round datepicker', 'id' => 'referencedate']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-4"><label for="invoicedate" class="caption">Quote {{trans('general.date')}}</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                            {{ Form::text('invoicedate', null, ['class' => 'form-control round datepicker', 'id' => 'invoicedate']) }}
                                        </div>
                                    </div>                                                                      
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-4"><label for="revision" class="caption">Validity Period</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                            <select class="form-control round  select-box" name="validity" id="validity" data-placeholder="{{trans('tasks.assign')}}">
                                                @foreach (array(0, 14, 30, 45, 60, 90) as $n)
                                                    <option value="{{ $n }}" {{ $n == $quote->validity ? 'selected' : '' }}>
                                                        {{ !$n ? 'On Receipt' : 'Valid for '. $n .' days' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4"><label for="ref_type" class="caption">Currency *</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                            <select class="form-control  select-box" name="currency" id="currency" data-placeholder="{{trans('tasks.assign')}}">
                                                <option value="0">Default</option>
                                                @foreach($currencies as $currency)
                                                    <option value="{{ $currency->id }}" {{ $quote->currency == $currency->id ? 'selected' : '' }}>
                                                        {{$currency->symbol}} - {{$currency->code}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4"><label for="client_ref" class="caption">Client Reference / Callout ID</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                            {{ Form::text('client_ref', @$quote->client_ref, ['class' => 'form-control round', 'id' => 'client_ref', 'required']) }}
                                        </div>
                                    </div> 
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-4"><label for="source" class="caption">Quotation Terms *</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                            <select id="term_id" name="term_id" class="form-control round" required>
                                                <option value="">-- Select Term --</option>
                                                @foreach($terms as $term)
                                                    <option value="{{ $term->id }}" {{ $quote->term_id == $term->id ? 'selected' : '' }}>
                                                        {{ $term->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="taxFormat" class="caption">{{trans('general.tax')}}</label>
                                        <select class="form-control round" name='tax_id' id="tax_id">
                                            @foreach (array(16, 8, 0) as $n)
                                                <option value="{{ $n }}" {{ $n == $quote->tax_id ? 'selected' : ''}}>
                                                    {{ !$n ? 'Off' : $n . '% VAT' }}
                                                </option>
                                            @endforeach                                            
                                        </select>
                                        <input type="hidden" name="tax_format" id="tax_format" value="{{ @$quote->tax_format }}">
                                    </div>
                                    @if (!isset($last_quote))
                                        <div class="col-sm-4"><label for="revision" class="caption">Revision</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                                <select class="form-control round  select-box" name="revision" id="revision">
                                                    <option value="">-- Select Revision --</option>
                                                    @foreach (array(1, 2, 3, 4, 5) as $n)
                                                        <option value="_r{{ $n }}" {{ @$quote->revision == '_r'.$n ? 'selected' : '' }}>
                                                            R{{ $n }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif
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
                                            <input type="text" name="total" class="form-control" id="total" placeholder="Total" readonly>
                                        </div>
                                    </div>
                                    @isset($copy_from_pi)
                                        <input type="hidden" name="bank_id" value="0">                                 
                                    @endisset                                     
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
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } });

    // initialize datepicker
    $('.datepicker').datepicker({ format: "{{ config('core.user_date_format') }}" })
    $('#referencedate').datepicker('setDate', new Date("{{ $quote->reference_date }}"));
    $('#invoicedate').datepicker('setDate', new Date("{{ $quote->invoicedate }}"));

    // Check if radio button is checked
    $('input[type="radio"]').change(function() {
        const $span = $('#tax-label').find('span').eq(1);
        if ($(this).is(':checked')) {
            if ($(this).val() === 'exclusive') 
                $span.text('VAT-Exclusive (print type)');
            else $span.text('VAT-Inclusive (print type)');            
        }
    });

    // on selecting lead update subjec and client_ref
    $('#lead_id').change(function() {
        $option = $('#lead_id option:selected');
        $('#subject').val($option.attr('title'));
        $('#client_ref').val($option.attr('client_ref'));
    });

    // on selecting Djc reference update subject
    $('#reference').change(function() {
        const title = $('#lead_id option:selected').attr('title')
        $('#subject').val(title);
        if ($(this).val()) $('#subject').val(title + ' ; Djc-' + $(this).val());
    });
    
    // row dropdown menu
    function dropDown() {
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
    function productRow(n) {
        return `
            <tr>
                <td><input type="text" class="form-control" name="numbering[]" id="numbering-${n}" required></td>
                <td><input type="text" class="form-control" name="product_name[]" placeholder="{{trans('general.enter_product')}}" id='itemname-${n}' required></td>
                <td><input type="text" class="form-control" name="unit[]" id="unit-${n}" value=""></td>                
                <td><input type="text" class="form-control req qty" name="product_qty[]" id="amount-${n}"></td>
                <td><input type="text" class="form-control req price" name="product_price[]" id="price-${n}"></td>
                <td><input type="text" class="form-control req prcrate" name="product_subtotal[]" id="rateinclusive-${n}" readonly></td>
                <td><strong><span class='ttlText' id="result-${n}">0</span></strong></td>
                <td class="text-center">${dropDown()}</td>
                <input type="hidden" name="item_id[]" value="0" id="itemid-${n}">
                <input type="hidden" name="product_id[]" value=0 id="productid-${n}">
                <input type="hidden" name="row_index[]" value="${n}" id="rowindex-${n}">
                <input type="hidden" name="a_type[]" value="1" id="atype-${n}">
            </tr>
        `;
    }

    // product title row
    // with extra hidden input fields to imitate product row state
    function productTitleRow(n) {
        return `
            <tr>
                <td><input type="text" class="form-control" name="numbering[]" id="numbering-${n}" required></td>
                <td colspan="6"><input type="text"  class="form-control" name="product_name[]" id="itemname-${n}" placeholder="Enter Title Or Heading" required></td>
                <td class="text-center">${dropDown()}</td>
                <input type="hidden" name="item_id[]" value="0" id="itemid-${n}">
                <input type="hidden" name="product_id[]" value="${n}" id="productid-${n}">
                <input type="hidden" name="unit[]" value="">
                <input type="hidden" name="product_qty[]" value="0">
                <input type="hidden" name="product_price[]" value="0">
                <input type="hidden" name="product_subtotal[]" value="0">
                <input type="hidden" name="row_index[]" value="${n}" id="rowindex-${n}">
                <input type="hidden" name="a_type[]" value="2" id="atype-${n}">
            </tr>
        `;
    }

    // On change quantity and rate exclusive
    $("#quotation").on('change', '.qty, .price', function() {
        const i = $(this).parents('tr:first').index() - 1;
        const qty = $('#amount-'+i).val() || 0;
        const rateExc = $('#price-'+i).val().replace(/,/g, '') || 0;
        const tax = $('#tax_id').val() * 0.01 + 1;
        const amount = qty * rateExc * tax;
        const rateIncl = rateExc * tax;

        $('#price-'+i).val((rateExc*1).toLocaleString());
        $('#rateinclusive-'+i).val(rateIncl.toLocaleString());
        $('#result-'+i).text(amount.toLocaleString());
        calcTotal();
    });

    // product row counter
    let rowIndx = 0;
    const quoteItems = @json($products);
    quoteItems.forEach(v => {
        const i = rowIndx;
        const item = {...v};
        // format float values to integer
        const keys = ['product_price','product_qty','product_subtotal'];
        for (let prop in item) {
            if (keys.includes(prop) && item[prop]) {
                item[prop] = parseFloat(item[prop].replace(/,/g, ''));
            }
        }
        // check if item is of type product
        if (item.a_type == 1) {
            const row = productRow(i);
            $('#quotation tr:last').after(row);
            $('#itemname-'+i).autocomplete(autocompleteProp(i));
            // set default values
            $('#itemid-'+i).val(item.id);
            $('#productid-'+i).val(item.product_id);
            $('#numbering-'+i).val(item.numbering);
            $('#itemname-'+i).val(item.product_name);
            $('#unit-'+i).val(item.unit);                
            $('#amount-'+i).val(item.product_qty.toLocaleString());
            $('#rateinclusive-'+i).val(item.product_subtotal.toLocaleString());                
            $('#result-'+i).text(item.product_subtotal.toLocaleString());
            $('#price-'+i).val(item.product_price.toLocaleString()).change();
        } else {
            const row = productTitleRow(i);
            $('#quotation tr:last').after(row);
            // set default values
            $('#itemid-'+i).val(item.id);
            $('#productid-'+i).val(item.product_id);
            $('#numbering-'+i).val(item.numbering);
            $('#itemname-'+i).val(item.product_name);
        }
        rowIndx++;
        calcTotal();
    });    

    $('#add-product').click(function() {
        const i = rowIndx;
        $('#quotation tr:last').after(productRow(i));
        $('#itemname-'+i).autocomplete(autocompleteProp(i));
        rowIndx++;
    });
    // on clicking Add Title button
    $('#add-title').click(function() {
        const i = rowIndx;
        $('#quotation tr:last').after(productTitleRow(i));
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
            if (confirm('Are you sure to delete this product ?')) {                
                $.ajax({
                    url: baseurl + 'quotes/delete_product/' + itemId,
                    // method: 'DELETE',
                });
                $row.remove();
            }
        }
        calcTotal();
    });

    // on Tax id change
    $('#tax_id').change(function() {
        const tax = $(this).val();
        const $span = $('#tax-label').find('span').eq(0);
        $('#tax_format').val('inclusive');
        $span.text(tax+'%');
        if (tax == 0) {
            $('#tax_format').val('exclusive');
            $span.text('OFF');
        }

        $('#quotation tr').each(function() {
            const i = $(this).index();
            if ($('#atype-'+i).val() == 1) {
                const tax = $('#tax_id').val() * 0.01 + 1;
                const qty = $('#amount-'+i).val() || 0;
                const rateExc = $('#price-'+i).val().replace(/,/g, '') || 0;

                const amount = qty * rateExc * tax;
                const rateIncl = rateExc * tax;
                $('#rateinclusive-'+i).val(rateIncl.toLocaleString());
                $('#result-'+i).text(amount.toLocaleString());
            }
        });

        calcTotal();
    });    

    function calcTotal() {
        let subTotal = 0;
        let grandTotal = 0;

        $('#quotation tr').each(function() {
            const i = $(this).index();
            $('#rowindex-'+i).val(i);

            const tax = $('#tax_id').val() * 0.01 + 1;
            const amount = $('#result-'+i).text().replace(/,/g, '');
            subTotal += amount/tax;
            grandTotal += amount*1;
        });
        $('#subtotal').val(subTotal.toLocaleString());
        $('#total').val(grandTotal.toLocaleString());
        $('#tax').val((grandTotal-subTotal).toLocaleString());        
    }

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

                const price = parseFloat(data.price.replace(/,/, ''));
                $('#price-'+i).val(price.toLocaleString());
                const rateIncl = price * $('#tax_id').val() * 0.01 + 1;
                $('#rateinclusive-'+i).val(rateIncl.toLocaleString());                
                $('#result-'+i).text(rateIncl.toLocaleString());
                
                calcTotal();
            }
        };
    }
</script>
@endsection