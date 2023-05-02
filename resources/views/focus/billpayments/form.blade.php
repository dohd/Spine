<div class="form-group row">
    <div class="col-4">
        <label for="supplier">Supplier</label>
        <select name="supplier_id" id="supplier" class="form-control select2" data-placeholder="Choose supplier" required>
            <option value=""></option>
            @foreach ($suppliers as $row)
                <option value="{{ $row->id }}" {{ @$billpayment && $billpayment->supplier_id == $row->id? 'selected' : '' }}>
                    {{ $row->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-4">
        <label for="employee">Employee</label>
        <select name="employee_id" id="employee" class="form-control select2" data-placeholder="Choose Employee" required>
            <option value=""></option>
            @foreach ($employees as $row)
                <option value="{{ $row->id }}" {{ @$billpayment && $billpayment->employee_id == $row->id? 'selected' : '' }}>
                    {{ $row->fullname }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-2">
        <label for="tid" class="caption">Remittance No.</label>
        {{ Form::text('tid', @$billpayment ? $billpayment->tid : $tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
    </div>  
    <div class="col-2">
        <label for="type">Payment Type</label>
        <select name="payment_type" id="payment_type" class="custom-select" {{ @$billpayment? 'disabled' : '' }}>
            @foreach (['per_invoice', 'on_account', 'advance_payment'] as $val)
                <option value="{{ $val }}" {{ $val == @$billpayment->payment_type? 'selected' : '' }}>
                    {{ ucwords(str_replace('_', ' ', $val)) }}
                </option>
            @endforeach
        </select>
    </div>  
</div> 

<div class="form-group row">
    <div class="col-2">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
    </div> 

    <div class="col-2">
        <label for="payment_mode">Payment Mode</label>
        <select name="payment_mode" id="payment_mode" class="custom-select">
            @foreach (['eft', 'rtgs','cash', 'mpesa', 'cheque'] as $val)
                <option value="{{ $val }}" {{ @$billpayment->payment_mode == $val? 'selected' : '' }}>{{ strtoupper($val) }}</option>
            @endforeach
        </select>
    </div>  
    <div class="col-2">
        <label for="reference">Reference</label>
        {{ Form::text('reference', null, ['class' => 'form-control', 'id' => 'reference', 'required']) }}
    </div>  
    
    <div class="col-2">
        <label for="account">Pay From (Ledger Account)</label>
        <select name="account_id" id="account" class="custom-select" required>  
            <option value="">-- select account --</option>                                 
            @foreach ($accounts as $row)
                <option value="{{ $row->id }}" {{ $row->id == @$billpayment->account_id? 'selected' : '' }}>
                    {{ $row->holder }}
                </option>
            @endforeach
        </select>
    </div>  
    <div class="col-2">
        @php
            $disabled = ((@$is_allocated_pmt) || (@$is_next_allocation))? 'disabled' : '';
        @endphp
        <label for="amount" class="caption">Amount</label>
        {{ Form::text('amount', null, ['class' => 'form-control', 'id' => 'amount', 'required', $disabled]) }}
    </div>     
</div>

<div class="row form-group">
    <div class="col-6">
        <label for="payment">Allocate Payment</label>
        <select id="rel_payment" 
            name="rel_payment_id" 
            class="custom-select" 
            data-placeholder="Search Payment" 
            {{ $unallocated_pmts->count() ? '' : 'disabled' }}
            {{ @$billpayment? 'disabled' : '' }}
        >
            <option value="">None</option>
            @foreach ($unallocated_pmts as $pmt)
                @php
                    $balance = numberFormat($pmt->amount - $pmt->allocate_ttl);
                    $payment_type = ucfirst(str_replace('_', ' ', $pmt->payment_type));
                    $note = $pmt->note;
                    $date = $pmt->date;
                @endphp
                <option 
                    value="{{ $pmt->id }}" 
                    supplier_id="{{ $pmt->supplier_id }}"
                    data="{{ json_encode($pmt) }}"
                >
                    ({{ $balance }} - {{ $payment_type }} : {{ $date }}) - {{ $note }}
                    
                </option>
            @endforeach
        </select>
    </div>  
    <div class="col-6">
        <label for="note">Note</label>    
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note', 'required']) }}
    </div>  
</div>

<div class="table-responsive">
    <table class="table tfr my_stripe_single text-center" id="billsTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>Due Date</th>
                <th>Bill No</th>
                <th>Supplier Name</th>
                <th>Note</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Paid</th>
                <th>Outstanding</th>
                <th>Allocate</th>
            </tr>
        </thead>
        <tbody>   
            @isset ($billpayment)
                @foreach ($billpayment->items as $item)
                    @php
                        $bill = $item->supplier_bill;
                        if (!$bill) continue;
                    @endphp
                    <tr>
                        <td class="text-center">{{ dateFormat($bill->due_date) }}</td>
                        <td>{{ $bill->tid }}</td>
                        <td>{{ ($bill->purchase? $bill->purchase->suppliername : $bill->supplier)? $bill->supplier->name : '' }}</td>
                        <td class="text-center">{{ $bill->name }}</td>
                        <td>{{ $bill->status }}</td>
                        <td class="amount">{{ numberFormat($bill->total) }}</td>
                        <td>{{ numberFormat($bill->amount_paid) }}</td>
                        <td class="text-center due"><b>{{ numberFormat($bill->total - $bill->amount_paid) }}</b></td>
                        <td><input type="text" class="form-control paid" name="paid[]" value="{{ numberFormat($item->paid) }}" required></td>
                        <input type="hidden" name="bill_id[]" value="{{ $bill->id }}">
                        <input type="hidden" name="id[]" value="{{ $item->id }}">
                    </tr>
                @endforeach
            @endisset      
        </tbody>                
    </table>
</div>
<div class="row">  
    <div class="col-2 ml-auto">
        <label for="balance">Total Balance</label>    
        {{ Form::text('balance', null, ['class' => 'form-control', 'id' => 'balance', 'readonly']) }}
    </div>                          
</div>
<div class="row">  
    <div class="col-2 ml-auto">
        <label for="allocate_ttl">Total Allocated Amount</label>    
        {{ Form::text('allocate_ttl', null, ['class' => 'form-control', 'id' => 'allocate_ttl', 'readonly']) }}
    </div>                          
</div>
<div class="row mt-1">                            
    <div class="col-2 ml-auto">  
        {{ Form::submit(@$billpayment? 'Update Payment' : 'Make Payment', ['class' =>'btn btn-primary btn-lg']) }}
    </div>
</div>

@section('after-scripts')
@include('focus.billpayments.form_js')
@endsection
