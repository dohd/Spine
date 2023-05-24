<div class="form-group row">
    <div class="col-6">
        <label for="customer">Search Customer</label>
        <select name="customer_id" id="customer" class="form-control" data-placeholder="Seach Customer" required>
            @isset($creditnote)
                <option value="{{ $creditnote->customer_id }}">{{ $creditnote->customer->company }}</option>
            @endisset
        </select>                          
    </div>
    <div class="col-2">
        <label for="tid">{{ $is_debit? 'Debit' : 'Credit' }} Note No.</label>
        {{ Form::text('tid', @$creditnote->tid? $creditnote->tid: @$last_tid+1, ['class' => 'form-control', 'readonly']) }}
    </div>
    <div class="col-2">
        <div><label for="date">Date</label></div>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
    </div>
    <div class="col-2">
        <label for="is_tax_exc">VAT on Amount</label>
        <select name="is_tax_exc" class="custom-select" id="is_tax_exc">
            @foreach ([ 1 => 'Exclusive', 0 => 'Inclusive'] as $k => $val)
                <option value="{{ $k }}" {{ @$creditnote && $k == @$creditnote->is_tax_exc? 'selected' : '' }}>
                    {{ $val }}
                </option>
            @endforeach
        </select>
    </div>  
</div>
<div class="form-group row">
    <div class="col-6">
        <label for="invoice">Invoice</label>
        <select name="invoice_id" id="invoice" class="form-control" data-placeholder="Choose Invoice" required>
            <option value=""></option>
            @isset($creditnote->invoice)
                <option value="{{ $creditnote->invoice_id }}" selected>{{ gen4tid('Inv-', $creditnote->invoice->tid) }} - {{ $creditnote->invoice->notes }}</option>
            @endisset
        </select>
    </div>
    <div class="col-2">
        <label for="tax">Tax</label>
        <select name="tax_id" id="tax_id" class="custom-select">
            @foreach ([16, 8, 0] as $val)
                <option value="{{ $val }}">
                    {{ $val ? $val . '% VAT' : 'Off' }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-2">
        <label for="amount">Amount</label>
        {{ Form::text('amount', null, ['class' => 'form-control', 'id' => 'amount']) }}
    </div>  
</div>
<div class="form-group row">
    <div class="col-12">
        <label for="note">Note</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'required']) }}
    </div>  
</div>
<div class="row">
    <div class="col-3 ml-auto">
        <label for="subtotal">Subtotal</label>
        {{ Form::text('subtotal', null, ['class' => 'form-control', 'id' => 'subtotal', 'readonly']) }}
    </div>  
</div>
<div class="row">
    <div class="col-3 ml-auto">
        <label for="tax">Tax</label>
        {{ Form::text('tax', null, ['class' => 'form-control', 'id' => 'tax', 'readonly']) }}
    </div>  
</div>
<div class="row mb-1">
    <div class="col-3 ml-auto">
        <label for="total">Grand Total</label>
        {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total', 'readonly']) }}
    </div> 
</div>
<div class="form-group row">
    <div class="col-3 ml-auto">
        {{ Form::submit('Generate', ['class' => 'btn btn-primary btn-lg']) }}
    </div>
</div>
<input type="hidden" name="is_debit" value="{{ $is_debit ? 1 : 0 }}">