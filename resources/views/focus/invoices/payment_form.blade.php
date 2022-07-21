<div class="row mb-1">
    <div class="col-6">
        <label for="customer" class="caption">Search Customer</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <select id="person" name="customer_id" class="form-control select-box" data-placeholder="Search Customer" required>
                @isset ($payment)
                    <option value="{{ $payment->customer_id }}">{{ $payment->customer->company }}</option>
                @endisset
            </select>
        </div>
    </div>

    <div class="col-2">
        <label for="reference" class="caption">Transaction ID</label>
        <div class="input-group">
            {{ Form::text('tid', @$payment ? $payment->tid : $tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
        </div>
    </div> 

    <div class="col-2">
        <label for="date" class="caption">Date</label>
        <div class="input-group">
            {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
        </div>
    </div>     
    
    <div class="col-2">
        <label for="type">Advance Payment Account</label>
        <select name="advance_account_id" id="advanced" class="form-control" required disabled>
            <option value="">-- Select Account --</option>
            @foreach ($accounts as $row)
                @if ($row->account_type_id == 10 && $row->system == 'adv_pmt')                    
                    <option value="{{ $row->id }}" balance="{{ $row->credit - $row->debit }}">
                        {{ $row->holder }}
                    </option>
                @endif                
            @endforeach
        </select>
    </div>   
</div> 

<div class="form-group row">  
    <div class="col-2">
        <label for="type">Allocation Type</label>
        <select name="is_allocated" id="allocated" class="form-control" required>
            <option value="">-- Select Type --</option>
                @foreach (['On Account', 'Per Invoice',] as $k => $val)
                    <option value="{{ $k }}" {{ ($k == @$payment->is_allocated ? 'selected' : $k)? 'selected' : '' }}>
                        {{ $val }}
                    </option>
                @endforeach
        </select>
    </div>   
    <div class="col-2">
        <label for="type">Allocation Source</label>
        <select name="source" id="source" class="form-control" required {{ @$payment->is_allocated? '' : 'disabled' }}>
            <option value="">-- Select Source --</option>
            @foreach (['direct' => 'Direct Payment', 'advance' => 'Advance Payment',] as $k => $val)
                <option value="{{ $k }}" {{ $k == @$payment->source? 'selected' : '' }}>{{ $val }}</option>
            @endforeach
        </select>
    </div>  
    <div class="col-2">
        <label for="deposit" class="caption">Amount (Ksh.)</label>
        {{ Form::text('deposit', numberFormat(@$payment->deposit), ['class' => 'form-control', 'id' => 'deposit', 'required']) }}
    </div>  
    <div class="col-2">
        <label for="payment_mode">Payment Mode</label>
        <select name="payment_mode" id="paymentMode" class="form-control" required>
            <option value="">-- Select Mode --</option>
            @foreach (['eft', 'rtgs','cash', 'mpesa', 'cheque'] as $val)
                <option value="{{ $val }}" {{ $val == @$payment->payment_mode? 'selected' : '' }}>
                    {{ strtoupper($val) }}
                </option>
            @endforeach
        </select>
    </div>  
    <div class="col-2">
        <label for="reference" class="caption">Reference</label>
        {{ Form::text('reference', null, ['class' => 'form-control', 'id' => 'reference', 'required']) }}
    </div>      
   
    <div class="col-2">
        <label for="type">Receive on Account</label>
        <select name="account_id" id="account" class="form-control" required>
            <option value="">-- Select Account --</option>
            @foreach ($accounts as $row)
                @if ($row->account_type_id == 6)
                    <option value="{{ $row->id }}" {{ $row->id == @$payment->account_id? 'selected' : '' }}>
                        {{ $row->holder }}                        
                    </option>
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
                <th>Invoice No</th>
                <th>Note</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Paid</th>
                <th>Outstanding</th>
                <th>Allocate (Ksh.)</th>
            </tr>
        </thead>
        <tbody>   
            @isset ($payment)
                @foreach ($payment->items as $row)
                    @php
                        $invoice = $row->invoice;
                    @endphp
                    @if ($invoice)
                        <tr>
                            <td>{{ dateFormat($invoice->invoiceduedate) }}</td>
                            <td>{{ gen4tid('Inv-', $invoice->tid) }}</td>
                            <td>{{ $invoice->notes }}</td>
                            <td>{{ $invoice->status }}</td>
                            <td>{{ numberFormat($invoice->total) }}</td>
                            <td>{{ numberFormat($invoice->amountpaid) }}</td>
                            <td class="amount"><b>{{ numberFormat($invoice->total - $invoice->amountpaid) }}<b></td>
                            <td><input type="text" class="form-control paid" name="paid[]" value="{{ numberFormat($row->paid) }}"></td>
                            <input type="hidden" name="id[]" value="{{ $row->id }}">
                        </tr>
                    @endif
                @endforeach
            @endisset                             
            <tr class="bg-white">
                <td colspan="6"></td>
                <td colspan="2">
                    <div class="col-6 float-right">
                        <label for="total_paid">Total Allocated</label>
                        {{ Form::text('deposit_ttl', 0, ['class' => 'form-control ml-1', 'id' => 'deposit_ttl', 'readonly']) }}
                    </div>                                         
                    <div class="col-6 float-right">
                        <label for="total_bill">Total Balance</label>
                        {{ Form::text('amount_ttl', 0, ['class' => 'form-control ml-1', 'id' => 'amount_ttl', 'readonly']) }}
                    </div>
                </td>
            </tr>
        </tbody>                
    </table>
</div>
<div class="form-group row">                            
    <div class="col-12">  
        {{ Form::submit(@$payment? 'Update Payment' : 'Receive Payment', ['class' =>'btn btn-primary btn-lg float-right mr-3']) }}
    </div>
</div>