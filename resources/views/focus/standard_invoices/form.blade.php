<div class="row mb-1">
    <div class="col-6">
        <div>
            <button type="button" class="btn btn-blue btn-sm round float-right add-customer" data-toggle="modal" data-target="#addCustomerModal">
                <i class="fa fa-plus-circle"></i> customer
            </button>
            <label for="payer" class="caption">Customer Name</label>                      
        </div>
        
        <div class="input-group">
            <select class="form-control select2" name='customer_id' id="customer" data-placeholder="Choose Customer" required>
                <option value=""></option>
                @foreach ($customers as $row)
                    <option value="{{ $row->id }}">
                        {{ $row->company }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-2">
        <label for="tid" class="caption">Invoice No.</label>
        {{ Form::text('tid', @$tid+1, ['class' => 'form-control round', 'readonly']) }}
    </div>

    <div class="col-2">
        <label for="invoicedate" class="caption">Invoice Date</label>
        {{ Form::text('invoicedate', null, ['class' => 'form-control round datepicker', 'id' => 'invoicedate']) }}
    </div>

    <div class="col-2">
        <label for="tid" class="caption">Tax Rate*</label>
        <select class="custom-select round" name='tax_id' id="tax" required>
            <option value="">-- select tax rate --</option>
            @foreach ($tax_rates as $row)
                <option value="{{ +$row->value }}" {{ @$invoice && $invoice->tax_id == $row->value? 'selected' : '' }}>
                    {{ $row->name }}
                </option>
            @endforeach
        </select>        
    </div>   
</div>

<div class="form-group row">
    <div class="col-3"> 
        <label for="refer_no" class="caption">Payment Account*</label>                                   
        <select class="custom-select" name="bank_id" id="bank_id" required>
            <option value="">-- Select Bank --</option>
            @foreach ($banks as $bank)
                <option value="{{ $bank->id }}" {{ $bank->id == @$invoice->bank_id ? 'selected' : '' }}>
                    {{ $bank->bank }} {{ $bank->note? "- {$bank->note}" : '' }}
                </option>
            @endforeach
        </select>                               
    </div>
    <div class="col-3">
        <label for="validity" class="caption">Credit Period</label>
        <select class="custom-select" name="validity" id="validity">
            @foreach ([0, 14, 30, 45, 60, 90] as $val)
            <option value="{{ $val }}" {{ !$val ? 'selected' : ''}} {{ @$invoice->validity == $val ? 'selected' : '' }}>
                {{ $val ? 'Valid For ' . $val . ' Days' : 'On Receipt' }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="col-2">
        <label for="income_category" class="caption">Income Category*</label>
        <select class="custom-select" name="account_id" required>
            <option value="">-- Select Category --</option>                                        
            @foreach ($accounts as $row)
                @php
                    $account_type = $row->accountType;
                    if ($account_type->name != 'Income') continue;
                @endphp
                <optgroup label="{{ $account_type->name }}">
                    <option value="{{ $row->id }}" {{ $row->id == @$invoice->account_id ? 'selected' : '' }}>
                        {{ $row->holder }}
                    </option>                    
                </optgroup>
            @endforeach                                        
        </select>
    </div>

    <div class="col-2">
        <label for="currency">Currency</label>
        <select class="custom-select" name="currency_id" id="currency" data-placeholder="{{trans('tasks.assign')}}" required>
            @foreach ($currencies as $currency)
                @php 
                    if ($currency->rate != 1) continue;

                    $selected = '';
                    if ($currency->id == @$quote->currency_id) $selected = 'selected';
                    elseif ($currency->id == 1 && !@$quote) $selected = 'selected';
                    $rate_label = $currency->rate > 1? "1/" . (+$currency->rate) : '';
                @endphp
                <option 
                    value="{{ $currency->id }}" 
                    currency_rate="{{ +$currency->rate }}" 
                    {{ $selected }}
                >
                    {{ $currency->code }} {{ $rate_label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-2">
        <label for="terms">Terms</label>
        <select name="term_id" class="custom-select">
            @foreach ($terms as $term)
            <option value="{{ $term->id }}" {{ $term->id == @$invoice->term_id ? 'selected' : ''}}>
                {{ $term->title }}
            </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row mb-1">
    <div class="col-12">
        <div class="input-group"><label for="title" class="caption">Note</label></div>
        {{ Form::text('notes', null, ['class' => 'form-control']) }}
    </div>
</div>

<div class="table-responsive">
    <table id="products_tbl" class="table tfr my_stripe_single pb-1">
        <thead>
            <tr class="item_header bg-gradient-directional-blue white">
                <th width="5%">#</th>
                <th width="35%">Item Name</th>
                <th >UoM</th>
                <th width="5%">Qty</th>
                <th>Unit Price</th>
                <th>Tax Rate</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><input class="form-control num" name="numbering[]" value="1" readonly></td>                                            
                <td><input class="form-control name" name="description[]"></td>
                <td><input type="text" class="form-control unit" name="unit[]" value="ITEM"></td>
                <td><input type="text" class="form-control qty" name="product_qty[]"></td>
                <td><input type="text" class="form-control price" name="product_price[]"></td>
                <td>
                    <div class="row no-gutters">
                        <div class="col-6">
                            <select class="custom-select taxid" name='item_tax_id[]'>
                                @foreach ($tax_rates as $row)
                                    <option value="{{ +$row->value }}" {{ @$invoice && $invoice->tax_id == $row->value? 'selected' : '' }}>
                                        {{ $row->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6"><input type="text" class="form-control prodtax" name="product_tax[]" readonly></div>
                    </div>                  
                </td>
                <td><input type="text" class="form-control amount" name="product_amount[]" readonly></td>
                <td>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            action
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item add-row" href="javascript:"><i class="fa fa-plus"></i> Add Row</a>
                            <a class="dropdown-item text-danger remove-row" href="javascript:">Remove</a>
                        </div>
                    </div> 
                </td>
                <input type="hidden" class="form-control prod-id" name="product_id[]">
            </tr>
        </tbody>
    </table>
</div>

<!-- Totals Summary -->
<div class="form-group">
    <div class="col-2 ml-auto">
        <label for="taxable">Taxable Amount</label>
        {{ Form::text('taxable', null, ['class' => 'form-control', 'id' => 'taxable', 'readonly']) }}
    </div>
    <div class="col-2 ml-auto">
        <label for="subtotal">Subtotal</label>
        {{ Form::text('subtotal', null, ['class' => 'form-control', 'id' => 'subtotal', 'readonly']) }}
    </div>
    <div class="col-2 ml-auto">
        <label for="totaltax">Total Tax</label>
        {{ Form::text('tax', null, ['class' => 'form-control', 'id' => 'totaltax', 'readonly']) }}
    </div>
    <div class="col-2 ml-auto">
        <label for="grandtotal">Grand Total</label>
        {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total', 'readonly']) }}
    </div>      

    <!-- submit buttons -->                             
    <div class="row form-group">
        <div class="col-sm-3 ml-auto mr-auto">
            <div class="input-group">
                <div class="col-sm-6">
                    <a href="{{ route('biller.invoices.index') }}" class="btn btn-danger block">Cancel</a>    
                </div>
                <div class="col-sm-6">
                    {{ Form::submit('Submit', ['class' => 'btn btn-primary block text-white mr-1']) }}    
                </div>
            </div>
        </div>        
    </div>
</div>

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}

<script>
    // init datepicker
    $('.datepicker')
    .datepicker({format: "{{config('core.user_date_format')}}", autoHide: true})
    .datepicker('setDate', new Date());
    // init select2
    $('.select2').select2({allowClear: true});
    // ajax config
    $.ajaxSetup({headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});


    // add or remove row
    let rowCount = 1;
    const initRow = $('#products_tbl tbody tr:first').html();
    $(document).on('click', '.add-row, .remove-row', function() {
        if ($(this).is('.add-row')) {
            rowCount++;
            $(this).closest('tr').after(`<tr>${initRow}</tr>`);
            const row = $(this).closest('tr').next();
            row.find('.name').autocomplete(autoCompleteConfig());
            // limit line item tax options
            const taxRate = parseFloat($('#tax').val());
            row.find('.taxid').children().each(function() {
                const optVal = parseFloat($(this).attr('value'));
                if (optVal == taxRate || optVal == 0) $(this).removeClass('d-none');
                else $(this).addClass('d-none');
            });
        } else {
            rowCount--;
            const row = $('#products_tbl tbody tr:last');
            if (!row.siblings().length) return;
            row.remove();
        }

        // set numbering
        $('#products_tbl tbody tr').each(function(i) {
            $(this).find('.num').val(i+1)
        });
        calcTotal();
    });

    // on tax change
    $('#tax').change(function() {
        // limit line item tax options
        const taxRate = parseFloat($(this).val());
        $('#products_tbl tbody tr').each(function() {
            $(this).find('.taxid').children().each(function() {
                const optVal = parseFloat($(this).attr('value'));
                if (optVal == taxRate || optVal == 0) $(this).removeClass('d-none');
                else $(this).addClass('d-none');
            });
            $(this).find('.taxid').val(taxRate).change();
        });
    });

    // on change line item
    $(document).on('change', '.qty, .price, .taxid', function() {
        const row = $(this).parents('tr');
        const qty = accounting.unformat(row.find('.qty').val());
        const price = accounting.unformat(row.find('.price').val());
        const taxRate = accounting.unformat(row.find('.taxid').val());

        const tax = qty * price * (taxRate / 100);
        const amount = tax + (qty * price);
        
        row.find('.prodtax').val(accounting.formatNumber(tax));
        row.find('.price').val(accounting.formatNumber(price));
        row.find('.amount').val(accounting.formatNumber(amount));
        calcTotal();
    });

    // compute totals
    function calcTotal() {
        let total = 0;
        let subtotal = 0;
        let taxable = 0;
        $('#products_tbl tbody tr').each(function(i) {
            const amount = accounting.unformat($(this).find('.amount').val());
            const qty = accounting.unformat($(this).find('.qty').val());
            const price = accounting.unformat($(this).find('.price').val());
            const tax = accounting.unformat($(this).find('.prodtax').val());
            if (tax > 0 ) taxable += qty * price;
            total += amount;
            subtotal += qty * price;
        });
        $('#taxable').val(accounting.formatNumber(taxable));
        $('#total').val(accounting.formatNumber(total));
        $('#subtotal').val(accounting.formatNumber(subtotal));
        $('#totaltax').val(accounting.formatNumber((total - subtotal)));
    }

    // on keyup item name set active row
    let activeRow;
    $(document).on('keyup', '.name', function() {
        activeRow = $(this).parents('tr');
    });

    // init autocomplete
    const defaultRow = $('#products_tbl tbody tr:first');
    defaultRow.find('.name').autocomplete(autoCompleteConfig());

    // autocomplete function
    function autoCompleteConfig() {
        return {
            source: function(request, response) {
                // stock product
                let term = request.term;
                let url = "{{ route('biller.products.quote_product_search') }}";
                let data = {
                    keyword: term, 
                    price_customer_id: $('#price_customer').val(),
                };
                // maintenance service product 
                const docType = @json(request('doc_type'));
                if (docType == 'maintenance') {
                    url = "{{ route('biller.taskschedules.quote_product_search') }}";
                    data.customer_id = $('#lead_id option:selected').attr('customer_id');
                } 
                $.ajax({
                    url, data,
                    method: 'POST',
                    success: result => response(result.map(v => ({label: v.name, value: v.name, data: v}))),
                });
            },
            autoFocus: true,
            select: function(event, ui) {
                const {data} = ui.item;

                const row = activeRow;
                row.find('.prod-id').val(data.id);
                row.find('.name').val(data.name);
                row.find('.qty').val(1);

                const currencyRate = $('#currency option:selected').attr('currency_rate');
                if (currencyRate > 1) {
                    data.purchase_price = parseFloat(data.purchase_price) / currencyRate;
                    data.price = parseFloat(data.price) / currencyRate;
                }

                row.find('.price').val(accounting.formatNumber(data.price)).change();  
                if (data.units) {
                    let units = data.units.filter(v => v.unit_type == 'base');
                    if (units.length) row.find('.unit').val(units[0].code);
                }
            }
        };
    } 
</script>
@stop