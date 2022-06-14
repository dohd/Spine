<div class="row mb-1">
    <div class="col-6">
        <label for="customer" class="caption">Search Customer</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <select id="person" name="customer_id" class="form-control select-box" data-placeholder="Search Customer" required>
            </select>
        </div>
    </div>

    <div class="col-2">
        <label for="reference" class="caption">Transaction ID</label>
        <div class="input-group">
            {{ Form::text('tid', $tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
        </div>
    </div> 

    <div class="col-2">
        <label for="date" class="caption">Payment Date</label>
        <div class="input-group">
            {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
        </div>
    </div>     
    
    <div class="col-2">
        <label for="type">Advance Payment Account</label>
        <select name="advance_account_id" id="advanced" class="form-control" required disabled>
            <option value="">-- Select Account --</option>
            @foreach ($accounts as $row)
                @if ($row->account_type_id == 10)                    
                    <option value="{{ $row->id }}" balance="{{ $row->credit - $row->debit }}" >
                        {{ $row->holder }}
                    </option>
                @endif
            @endforeach
        </select>
    </div>   
</div> 

<div class="form-group row">  
    <div class="col-2">
        <label for="deposit" class="caption">Amount (Ksh.)</label>
        {{ Form::text('deposit', null, ['class' => 'form-control', 'id' => 'deposit', 'required']) }}
    </div>  
    <div class="col-2">
        <label for="payment_mode">Payment Mode</label>
        <select name="payment_mode" id="paymentMode" class="form-control" required>
            <option value="">-- Select Mode --</option>
            @foreach (['eft', 'rtgs','cash', 'mpesa', 'cheque'] as $val)
                <option value="{{ $val }}">{{ strtoupper($val) }}</option>
            @endforeach
        </select>
    </div>  
    <div class="col-2">
        <label for="reference" class="caption">Reference</label>
        {{ Form::text('reference', null, ['class' => 'form-control', 'id' => 'reference', 'required']) }}
    </div>      
    <div class="col-2">
        <label for="type">Allocation Type</label>
        <select name="is_allocated" id="allocated" class="form-control" required>
            <option value="">-- Select Type --</option>
            @foreach (['On Account', 'Per Invoice',] as $k => $val)
                <option value="{{ $k }}">{{ $val }}</option>
            @endforeach
        </select>
    </div>   
    <div class="col-2">
        <label for="type">Allocation Source</label>
        <select name="source" id="source" class="form-control" required>
            <option value="">-- Select Source --</option>
            @foreach (['Default', 'Advance Payment',] as $k => $val)
                <option value="{{ $k }}">{{ $val }}</option>
            @endforeach
        </select>
    </div>  
    <div class="col-2">
        <label for="type">Receive on Account</label>
        <select name="account_id" id="account" class="form-control" required>
            <option value="">-- Select Account --</option>
            @foreach ($accounts as $row)
                @if ($row->account_type_id == 6)
                    <option value="{{ $row->id }}">{{ $row->holder }}</option>
                @endif
            @endforeach
        </select>
    </div>                                                
</div>

<div class="table-responsive">
    <table class="table tfr my_stripe_single text-center" id="invoiceTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>Due Date</th>
                <th> Invoice Number</th>
                <th>Note</th>
                <th>Status</th>
                <th>Amount (VAT Inc)</th>
                <th>Allocate (Ksh.)</th>
            </tr>
        </thead>
        <tbody>                                
            <tr class="bg-white">
                <td colspan="4"></td>
                <td colspan="2">
                    <div class="form-inline mb-1 float-right">
                        <label for="total_bill">Total Amount</label>
                        {{ Form::text('amount_ttl', 0, ['class' => 'form-control col-7 ml-1', 'id' => 'amount_ttl', 'readonly']) }}
                    </div>
                    <div class="form-inline float-right">
                        <label for="total_paid">Total Allocated</label>
                        {{ Form::text('deposit_ttl', 0, ['class' => 'form-control col-7 ml-1', 'id' => 'deposit_ttl', 'readonly']) }}
                    </div>                                         
                </td>
            </tr>
        </tbody>                
    </table>
</div>
<div class="form-group row">                            
    <div class="col-12">  
        {{ Form::submit('Receive Payment', ['class' =>'btn btn-primary btn-lg float-right mr-3']) }}
    </div>
</div>