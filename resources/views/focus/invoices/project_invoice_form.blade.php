<div class="row mb-1">
    <div class="col-4"><label for="payer" class="caption">Customer Name*</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            {{ Form::text('customer_name', $customer->company, ['class' => 'form-control round', 'id' => 'customername', 'readonly']) }}
            <input type="hidden" name="customer_id" value="{{ $customer->id }}" id="customer_id">
        </div>
    </div>
    <div class="col-2">
        <label for="tid" class="caption">Transaction ID*</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            @php
                $label = 'Inv-';
                $tid = isset($last_inv) ? $last_inv->tid+1 : '';
                if (isset($invoice)) $tid = $invoice->tid;
                $label .= sprintf('%04d', $tid);
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
        <label for="invoicedate" class="caption">Invoice Date*</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
            {{ Form::text('invoicedate', null, ['class' => 'form-control round datepicker', 'id' => 'invoicedate']) }}
        </div>
    </div>

    <div class="col-2">
        <label for="tid" class="caption">Select {{ trans('general.tax') }}*</label>
        <div class="input-group">
            <select class="form-control round" name='tax_id' id="tax_id" {{ isset($invoice) ? 'disabled' : '' }}>
                @foreach ([16, 8, 0] as $val)
                    <option value="{{ $val }}" {{ $val == 16 ? 'selected' : '' }}>
                        {{ $val ? $val.'% VAT' : 'Off' }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>   
</div>

<div class="row mb-1">
    <div class="col-3"> 
        <label for="refer_no" class="caption">Bank Account*</label>                                   
        <div class="input-group">
            <select class="form-control required" name="bank_id" id="bank_id" {{ isset($invoice) ? 'disabled' : '' }} required>
                <option value="">-- Select Bank --</option>
                @foreach ($banks as $bank)
                    <option value="{{ $bank->id }}" {{ $bank->id == @$invoice->bank_id ? 'selected' : '' }}>
                        {{ $bank->bank }}
                    </option>
                @endforeach
            </select>
        </div>                                
    </div>
    <div class="col-3">
        <label for="validity" class="caption">Credit Period*</label>
        <div class="input-group">
            <select class="form-control" name="validity" id="validity">
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
        <div class="input-group">
            <select class="form-control" name="account_id" {{ isset($invoice) ? 'disabled' : '' }} required>
                <option value="">-- Select Category --</option>                                        
                @foreach ($accounts as $row)
                    @if ($row->accountType->name == 'Income')
                        <optgroup label="{{ $row->accountType->name }}">
                            <option value="{{ $row->id }}" {{ $row->id == @$invoice->account_id ? 'selected' : '' }}>
                                {{ $row->holder }}
                            </option>
                        </optgroup>
                    @else
                        <optgroup label="{{ $row->accountType->name }}">
                            <option value="{{ $row->id }}" {{ $row->id == @$invoice->account_id ? 'selected' : '' }}>
                                {{ $row->holder }}
                            </option>
                        </optgroup>
                    @endif
                @endforeach                                        
            </select>
        </div>
    </div>
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
            <th width="35%" class="text-center">Description</th>
            <th width="10%" class="text-center">UOM</th>
            <th width="10%" class="text-center">Qty</th>
            <th width="10%" class="text-center">Rate (VAT exc)</th>
            <th width="10%" class="text-center">Amount</th>
        </tr>
    </thead>
    <tbody>
        @if (isset($quotes))
            @foreach($quotes as $k => $val)
                @php
                    // Reference details
                    $tid = sprintf('%04d', $val->tid);
                    $tid = $val->bank_id ? 'PI-'.$tid : 'QT-'.$tid;
                    if ($val->revision) $tid .= $val->revision;
                    $lpo_no = $val->lpo ? 'PO-'.$val->lpo->lpo_no : '';
                    $client_ref = $val->client_ref;
                    $branch_name = $val->branch->name;
                    if ($val->branch->branch_code) $branch_name .=  ' (' . $val->branch->branch_code . ') ';
                    
                    // Description details
                    $title = $val->notes;
                    $jcs = array();
                    foreach($val->verified_jcs as $jc) {
                        if ($jc->type == 2) $jcs[] = 'DN-'.$jc->reference;
                        else $jcs[] = 'JC-'.$jc->reference;
                    }

                    // Table values
                    $reference = '' . implode('; ', [$branch_name, $tid, $lpo_no, $client_ref]);                                        
                    $description = $title . '; ' . implode(', ', $jcs);
                    $price = number_format($val->subtotal, 2);
                    $project_id = $val->project_quote ? $val->project_quote->project_id : '';
                @endphp
                <tr>
                    <td>{{ $k+1 }}</td>                                            
                    <td><textarea class="form-control" name="reference[]" id="reference-{{ $k }}" rows="5" readonly>{{ $reference }}</textarea></td>
                    <td><textarea class="form-control" name="description[]" id="description-{{ $k }}" rows="5">{{ $description }}</textarea></td>
                    <td><input type="text" class="form-control " name="unit[]" id="unit-{{ $k }}" value="Lot" readonly></td>
                    <td><input type="text" class="form-control" name="product_qty[]" id="product_qty-{{ $k }}" value="1" readonly></td>
                    <td><input type="text" class="form-control rate" name="product_price[]" value="{{ $price }}" id="product_price-{{ $k }}" readonly></td>
                    <td><strong><span class='ttlText amount' id="result-{{ $k }}">{{ $price }}</span></strong></td>
                    <input type="hidden" value="{{ $price }}" id="initprice-{{ $k }}" disabled>
                    <input type="hidden" name="quote_id[]" value="{{ $val->id }}" id="quoteid-{{ $k }}">
                    <input type="hidden" name="branch_id[]" value="{{ $val->branch_id }}" id="branchid-{{ $k }}">
                    <input type="hidden" name="project_id[]" value="{{ $project_id }}" id="projectid-{{ $k }}">
                </tr>
            @endforeach
        @else
            @foreach ($invoice->invoice_items as $k => $item)
                <tr>
                    <td>{{ $k+1 }}</td>                                            
                    <td><textarea class="form-control" name="reference[]" id="reference-{{ $k }}" rows="5">{{ $item->reference }}</textarea></td>
                    <td><textarea class="form-control" name="description[]" id="description-{{ $k }}" rows="5">{{ $item->description }}</textarea></td>
                    <td><input type="text" class="form-control " name="unit[]" id="unit-{{ $k }}" value="{{ $item->unit }}" readonly></td>
                    <td><input type="text" class="form-control" name="product_qty[]" id="product_qty-{{ $k }}" value="{{ number_format($item->product_qty) }}" readonly></td>
                    <td><input type="text" class="form-control rate" name="product_price[]" value="{{ $item->product_price }}" id="product_price-{{ $k }}" readonly></td>
                    <td><strong><span class='ttlText amount' id="result-{{ $k }}">{{ $item->product_price }}</span></strong></td>
                    <input type="hidden" value="{{ $item->product_price }}" id="initprice-{{ $k }}" disabled>
                    <input type="hidden" name="quote_id[]" value="{{ $item->quote_id }}" id="quoteid-{{ $k }}">
                    <input type="hidden" name="branch_id[]" value="{{ $item->branch_id }}" id="branchid-{{ $k }}">
                    <input type="hidden" name="project_id[]" value="{{ $item->project_id }}" id="projectid-{{ $k }}">
                    <input type="hidden" name="id[]" value="{{ $item->id }}">
                </tr>
            @endforeach
        @endif

        <tr class="bg-white">
            <td colspan="5"></td>
            <td colspan="2">
                <div class="form-inline">
                    <label for="subtotal">Subtotal</label>
                    <input type="text" class="form-control col-7 mb-1 ml-2" name="subtotal" id="subtotal" readonly>
                </div>
                <div class="form-inline">
                    <label for="totaltax">Total Tax</label>
                    <input type="text" class="form-control col-7 mb-1 ml-2" name="tax" id="tax" readonly>
                </div>
                <div class="form-inline">
                    <label for="grandtotal">Grand Total</label>
                    <input type="text" class="form-control col-7 mb-1 ml-1" name="total" id="total" readonly>
                </div>                                    
                <div class="form-inline">
                    {{ Form::submit('Update Invoice', ['class' => 'btn btn-primary btn-lg ml-auto mr-1']) }}                          
                </div>
            </td>
        </tr>
    </tbody>
</table>