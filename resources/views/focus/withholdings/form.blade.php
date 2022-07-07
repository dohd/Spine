<div class="row mb-1">
    <div class="col-6">
        <label for="customer" class="caption">Search Customer</label>
        <select id="person" name="customer_id" class="form-control select-box" data-placeholder="Search Customer" required>
        </select>
    </div>                            
    <div class="col-2">
        <label for="reference" class="caption">System ID</label>
        {{ Form::text('tid', @$last_tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
    </div> 
    
    <div class="col-2">
        <label for="certificate" class="caption">Withholding Certificate</label>
        <select name="certificate" id="" class="form-control" required>
            <option value="">-- Select Type--</option>
            @foreach (['vat', 'tax'] as $val)
                <option value="{{ $val }}">{{ strtoupper($val) }}</option>
            @endforeach                                    
        </select>                            
    </div>  
    <div class="col-2">
        <label for="date" class="caption">Certificate Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
    </div>                                                                                                     
</div> 

<div class="row mb-2">                              
    <div class="col-2">
        <label for="amount" class="caption">Tax Amount Withheld</label>
        {{ Form::text('amount', null, ['class' => 'form-control', 'id' => 'deposit', 'required']) }}
    </div>                              
    <div class="col-2">
        <label for="reference" class="caption">Certificate Serial No.</label>
        {{ Form::text('doc_ref', null, ['class' => 'form-control', 'required']) }}
    </div>    
    <div class="col-2">
        <label for="date" class="caption">Payment / Transaction Date</label>
        {{ Form::text('due_date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
    </div>     
    <div class="col-6">
        <label for="date" class="caption">Note</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'placeholder' => 'e.g Gross Amount & Tax Rate', 'id' => 'note']) }}
    </div>                                                  
</div>
<div class="table-responsive">
    <table class="table tfr my_stripe_single text-center" id="invoiceTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>Due Date</th>
                <th>Invoice No</th>
                <th width="40%">Note</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Paid</th>
                <th>Balance</th>
                <th>Allocate (Ksh.)</th>
            </tr>
        </thead>
        <tbody>                                
            <tr class="bg-white">
                <td colspan="5"></td>
                <td colspan="3">
                    <div class="form-inline mb-1 float-right">
                        <label for="total_bill">Total Balance</label>
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
    <div class="col-2 ml-auto"> 
        {{ Form::submit('Generate', ['class' => 'btn btn-primary btn-lg']) }}
    </div>
</div>