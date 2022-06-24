<div class="form-group row">
    <div class="col-2">
        <label for="tid">{{ $is_debit? 'Debit' : 'Credit' }} Note No.</label>
        {{ Form::text('tid', @$creditnote->tid? $creditnote->tid: @$last_tid+1, ['class' => 'form-control', 'readonly']) }}
    </div>
    <div class="col-4">
        <label for="customer">Seach Customer</label>
        <select name="customer_id" id="customer" class="form-control" data-placeholder="Seach Customer" required>
            @isset($creditnote)
                <option value="{{ $creditnote->customer_id }}">{{ $creditnote->customer->company }}</option>
            @endisset
        </select>                          
    </div>
    <div class="col-4">
        <label for="invoice">Customer Invoice</label>
        <select name="invoice_id" id="invoice" class="form-control" required>
            <option value="">-- Select Invoice --</option>
            @isset($creditnote)
                <option value="{{ $creditnote->invoice_id }}" selected>{{ $creditnote->invoice->notes }}</option>
            @endisset
        </select>
    </div>
    <div class="col-2">
        <div><label for="date">Date</label></div>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
    </div>
</div>
<div class="form-group row">
    <div class="col-3">
        <label for="tax">Tax</label>
        <select name="tax_id" id="tax_id" class="form-control">
            @foreach ([16, 8, 0] as $val)
                <option value="{{ $val }}">
                    {{ $val ? $val . '% VAT' : 'Off' }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-3">
        <div><label for="amount">Amount</label></div>
        {{ Form::text('subtotal', null, ['class' => 'form-control', 'id' => 'subtotal']) }}
    </div>  
    <div class="col-3">
        <div><label for="tax">Tax Amount</label></div>
        {{ Form::text('tax', null, ['class' => 'form-control', 'id' => 'tax', 'readonly']) }}
    </div>  
    <div class="col-3">
        <div><label for="total">Total Amount</label></div>
        {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total', 'readonly']) }}
    </div> 
</div>
<div class="form-group row">
    <div class="col-12">
        <div><label for="note">Note</label></div>
        {{ Form::text('note', null, ['class' => 'form-control', 'required']) }}
    </div>
</div>
<div class="form-group row">
    <div class="col-2 ml-auto">
        {{ Form::submit(@$creditnote? 'Update' : 'Generate', ['class' => 'btn btn-primary btn-lg']) }}
    </div>
</div>
<input type="hidden" name="is_debit" value="{{ $is_debit ? 1 : 0 }}">