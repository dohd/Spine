<div class="row mb-1">
    <div class="col-4"><label for="payer" class="caption">Customer Name</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            {{ Form::text('customer_name', $customer->company, ['class' => 'form-control round', 'id' => 'customername', 'readonly']) }}
            <input type="hidden" name="customer_id" value="{{ $customer->id }}" id="customer_id">
        </div>
    </div>
    <div class="col-2">
        <label for="tid" class="caption">Invoice No.</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            @php
                $label = gen4tid("{$prefixes[0]}-", @$last_tid+1);
                $tid = @$last_tid+1; 
                if (isset($invoice)) {
                    $label = gen4tid("{$prefixes[0]}-", $invoice->tid);
                    $tid = $invoice->tid;
                }
            @endphp
            {{ Form::text('tid', $label, ['class' => 'form-control round', 'disabled']) }}
            <input type="hidden" name="tid" value={{ $tid }}>
        </div>
    </div>
    <div class="col-2">
        <label for="taxid" class="caption">KRA PIN</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
            {{ Form::text('taxid', $customer->taxid, ['class' => 'form-control round', 'required', isset($invoice) ? 'readonly' : '']) }}
        </div>
    </div>
    <div class="col-2">
        <label for="invoicedate" class="caption">Invoice Date</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
            {{ Form::text('invoicedate', null, ['class' => 'form-control round datepicker', 'id' => 'invoicedate']) }}
        </div>
    </div>

    <div class="col-2">
        <label for="tid" class="caption">Select {{ trans('general.tax') }}*</label>
        <div class="input-group">
            <select class="custom-select round" name='tax_id' id="tax_id" required>
                <option value="">-- select tax rate --</option>
                @foreach ($additionals as $row)
                    <option value="{{ $row->value }}" {{ @$invoice && $invoice->tax_id == $row->value? 'selected' : '' }}>
                        {{ $row->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>   
</div>

<div class="form-group row">
    <div class="col-2"> 
        <label for="refer_no" class="caption">Payment Account*</label>                                   
        <div class="input-group">
            <select class="custom-select" name="bank_id" id="bank_id" required>
                <option value="">-- Select Bank --</option>
                @foreach ($banks as $bank)
                    <option value="{{ $bank->id }}" {{ $bank->id == @$invoice->bank_id ? 'selected' : '' }}>
                        {{ $bank->bank }}
                    </option>
                @endforeach
            </select>
        </div>                                
    </div>
    <div class="col-2">
        <label for="validity" class="caption">Credit Period</label>
        <div class="input-group">
            <select class="custom-select" name="validity" id="validity">
                @foreach ([0, 14, 30, 45, 60, 90] as $val)
                <option value="{{ $val }}" {{ !$val ? 'selected' : ''}} {{ @$invoice->validity == $val ? 'selected' : '' }}>
                    {{ $val ? 'Valid For ' . $val . ' Days' : 'On Receipt' }}
                </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-3">
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

    <div class="col-3">
        <label for="terms">Terms</label>
        <select name="term_id" class="custom-select">
            @foreach ($terms as $term)
            <option value="{{ $term->id }}" {{ $term->id == @$invoice->term_id ? 'selected' : ''}}>
                {{ $term->title }}
            </option>
            @endforeach
        </select>
    </div>

    @if (isset($quote_ids) && count($quote_ids) == 1)
        <div class="col-2">
            <label for="invoice_category">Invoice Type</label>
            <select name="invoice_type" class="custom-select" id="invoice_type" required>
                @foreach (['standard', 'collective'] as $val)
                    <option value="{{ $val }}" {{ $val == @$invoice->invoice_type ? 'selected' : ''}}>
                        {{ ucfirst($val) }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif
</div>

<div class="row mb-1">
    <div class="col-12">
        <div class="input-group"><label for="title" class="caption">Note</label></div>
        {{ Form::text('notes', null, ['class' => 'form-control']) }}
    </div>
</div>

<table id="quoteTbl" class="table-responsive tfr my_stripe_single pb-1">
    <thead>
        <tr class="item_header bg-gradient-directional-blue white">
            <th class="text-center">#</th>
            <th width="25%" class="text-center">Reference</th>
            <th width="35%" class="text-center">Item Description</th>
            <th width="10%" class="text-center">UoM</th>
            <th width="10%" class="text-center">Qty</th>
            <th width="10%" class="text-center">Rate (VAT Exc)</th>
            <th width="10%" class="text-center">Amount</th>
        </tr>
    </thead>
    <tbody>
        @if (isset($quotes))
            {{-- create invoice items --}}
            @foreach($quotes as $k => $val)
                @php
                    // Reference details
                    $tid = gen4tid($val->bank_id? "{$prefixes[2]}-" : "{$prefixes[1]}-", $val->tid);
                    if ($val->revision) $tid .= $val->revision;
                    $lpo_no = $val->lpo ? "{$prefixes[3]}-{$val->lpo->lpo_no}" : '';
                    $client_ref = $val->client_ref;
                    $branch_name = $val->branch? "{$val->branch->name} ({$val->branch->branch_code})" : '';
                    
                    // Description details
                    $title = $val->notes;
                    $jcs = [];
                    foreach($val->verified_jcs as $jc) {
                        if ($jc->type == 2) $jcs[] = "{$prefixes[4]}-{$jc->reference}";
                        else $jcs[] = "{$prefixes[5]}-{$jc->reference}";
                    }

                    // Table values
                    $reference = '' . implode('; ', [$branch_name, $tid, $lpo_no, $client_ref]);                                        
                    $description = $title . '; ' . implode(', ', $jcs);
                    $price = numberFormat($val->subtotal);
                    $project_id = $val->project_quote ? $val->project_quote->project_id : '';
                @endphp
                <tr>
                    <td class="num">{{ $k+1 }}</td>                                            
                    <td><textarea class="form-control ref" name="reference[]" id="reference-{{ $k }}" rows="5" readonly>{{ $reference }}</textarea></td>
                    <td><textarea class="form-control descr" name="description[]" id="description-{{ $k }}" rows="5">{{ $description }}</textarea></td>
                    <td><input type="text" class="form-control unit" name="unit[]" id="unit-{{ $k }}" value="Lot" readonly></td>
                    <td><input type="text" class="form-control qty" name="product_qty[]" id="product_qty-{{ $k }}" value="1" readonly></td>
                    <td><input type="text" class="form-control rate" name="product_price[]" value="{{ $price }}" id="product_price-{{ $k }}" readonly></td>
                    <td><strong><span class='ttlText amount' id="result-{{ $k }}">{{ $price }}</span></strong></td>
                    <input type="hidden" class="subtotal" value="{{ $price }}" id="initprice-{{ $k }}" disabled>
                    <input type="hidden" class="num-val" name="numbering[]" id="num-{{ $k }}">
                    <input type="hidden" class="row-index" name="row_index[]" id="rowindex-{{ $k }}">
                    <input type="hidden" class="quote-id" name="quote_id[]" value="{{ $val->id }}" id="quoteid-{{ $k }}">
                    <input type="hidden" class="branch-id" name="branch_id[]" value="{{ $val->branch_id }}" id="branchid-{{ $k }}">
                    <input type="hidden" class="project-id" name="project_id[]" value="{{ $project_id }}" id="projectid-{{ $k }}">
                </tr>
            @endforeach
        @else        
            {{-- edit invoice items --}}
            @foreach ($invoice->products as $k => $item)
                <tr>
                    <td class="num">{{ $k+1 }}</td>                                            
                    <td><textarea class="form-control ref" name="reference[]" id="reference-{{ $k }}" rows="5">{{ $item->reference }}</textarea></td>
                    <td><textarea class="form-control descr" name="description[]" id="description-{{ $k }}" rows="5">{{ $item->description }}</textarea></td>
                    <td><input type="text" class="form-control unit" name="unit[]" id="unit-{{ $k }}" value="{{ $item->unit }}" readonly></td>
                    <td><input type="text" class="form-control qty" name="product_qty[]" id="product_qty-{{ $k }}" value="{{ +$item->product_qty }}" readonly></td>
                    <td><input type="text" class="form-control rate" name="product_price[]" value="{{ numberFormat($item->product_price) }}" id="product_price-{{ $k }}" readonly></td>
                    <td><strong><span class='ttlText amount' id="result-{{ $k }}">{{ numberFormat($item->product_price * $item->product_qty) }}</span></strong></td>
                    <input type="hidden"  class="subtotal" value="{{ $item->product_price }}" id="initprice-{{ $k }}" disabled>
                    <input type="hidden" class="num-val" name="numbering[]" value="{{ $item->numbering }}" id="num-{{ $k }}">
                    <input type="hidden" class="row-index" name="row_index[]" value="{{ $item->row_index }}" id="rowindex-{{ $k }}">
                    <input type="hidden" class="quote-id" name="quote_id[]" value="{{ $item->quote_id }}" id="quoteid-{{ $k }}">
                    <input type="hidden" class="branch-id" name="branch_id[]" value="{{ $item->branch_id }}" id="branchid-{{ $k }}">
                    <input type="hidden" class="project-id" name="project_id[]" value="{{ $item->project_id }}" id="projectid-{{ $k }}">
                    <input type="hidden" name="id[]" value="{{ $item->id }}">
                </tr>
            @endforeach
        @endif
    </tbody>
</table>

<div class="form-group">
    <div class="col-2 ml-auto">
        <label for="subtotal">Subtotal</label>
        {{ Form::text('subtotal', null, ['class' => 'form-control', 'id' => 'subtotal', 'readonly']) }}
    </div>
    <div class="col-2 ml-auto">
        <label for="totaltax">Total Tax</label>
        {{ Form::text('tax', null, ['class' => 'form-control', 'id' => 'tax', 'readonly']) }}
    </div>
    <div class="col-2 ml-auto">
        <label for="grandtotal">Grand Total</label>
        {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total', 'readonly']) }}
    </div>                                    
    <div class="row no-gutters mt-1">
        <div class="col-1 ml-auto pl-1">
            <a href="{{ route('biller.invoices.uninvoiced_quote') }}" class="btn btn-danger block">Cancel</a>    
        </div>
        <div class="col-1 ml-1">
            {{ Form::submit(@$invoice? 'Update' : 'Generate', ['class' => 'btn btn-primary block text-white mr-1']) }}    
        </div>
    </div>
</div>