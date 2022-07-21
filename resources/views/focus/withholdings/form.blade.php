<div class="row form-group">
    <div class="col-6">
        <label for="customer">Search Customer</label>
        <select id="person" name="customer_id" class="form-control" data-placeholder="Search Customer" required>
        </select>
    </div>                            
    <div class="col-2">
        <label for="reference" class="caption">System ID</label>
        {{ Form::text('tid', @$last_tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
    </div> 
    
    <div class="col-2">
        <label for="certificate" class="caption">Withholding Certificate</label>
        <select name="certificate" id="certificate" class="custom-select">
            @foreach (['vat', 'tax'] as $val)
                <option value="{{ $val }}">{{ strtoupper($val) }}</option>
            @endforeach                                    
        </select>
    </div>  
    <div class="col-2">
        <label for="date" class="caption">Certificate Date</label>
        {{ Form::text('cert_date', null, ['class' => 'form-control datepicker', 'id' => 'cert_date', 'required']) }}
    </div>                                                                                                     
</div> 
<div class="row form-group">                         
    <div class="col-2">
        <label for="amount" class="caption">Tax Amount Withheld (Ksh.)</label>
        {{ Form::text('amount', null, ['class' => 'form-control', 'id' => 'amount', 'required']) }}
    </div>                              
    <div class="col-2">
        <label for="reference" class="caption">Certificate Serial No.</label>
        {{ Form::text('reference', null, ['class' => 'form-control', 'id' => 'reference', 'required']) }}
    </div>    
    <div class="col-2">
        <label for="date" class="caption">Payment / Transaction Date</label>
        {{ Form::text('tr_date', null, ['class' => 'form-control datepicker', 'id' => 'tr_date', 'required']) }}
    </div>     
    <div class="col-6">
        <label for="note" class="caption">Note</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'placeholder' => 'e.g Gross Amount & Tax Rate', 'id' => 'note']) }}
    </div>                                                  
</div>
<div class="row form-group">
    <div class="col-6">
        <label for="withholding">Allocate Withholding Tax (Income)</label>
        <select id="withholding_cert" name="withholding_tax_id" class="form-control" data-placeholder="Search Certificate" disabled>
        </select>
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
                <th>Outstanding</th>
                <th>Allocate (Ksh.)</th>
            </tr>
        </thead>
        <tbody>                                
            <tr class="bg-white">
                <td colspan="5"></td>
                <td colspan="3">
                    <div class="form-inline mb-1 float-right">
                        <label for="total_bill">Total Balance</label>
                        {{ Form::text('balance', 0, ['class' => 'form-control col-7 ml-1', 'id' => 'balance', 'disabled']) }}
                    </div>  
                    <div class="form-inline float-right">
                        <label for="total_paid">Total Allocated</label>
                        {{ Form::text('allocate_ttl', 0, ['class' => 'form-control col-7 ml-1', 'id' => 'allocate_ttl', 'readonly']) }}
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