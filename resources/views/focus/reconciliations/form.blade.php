<div class="form-group row">
    <div class="col-5">
        <label for="payer" class="caption">Bank</label>                                       
        <select class="form-control" id="bank" name="account_id" required>
            <option value="">-- Select Bank --</option>
            @foreach ($accounts as $bank)
                <option value="{{ $bank->id }}" openingBalance="{{ $bank->opening_balance }}">
                    {{ $bank->holder }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-2">
        <label for="recon_id" class="caption">Reconciliation ID</label>
        <div class="input-group">
            {{ Form::text('tid', $last_tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
        </div>
    </div> 
    <div class="col-2">
        <label for="date" class="caption">Start Date</label>
        {{ Form::text('start_date', null, ['class' => 'form-control datepicker', 'id' => 'startDate', 'required']) }}
    </div> 
    <div class="col-2">
        <label for="date" class="caption">End Date</label>
        {{ Form::text('end_date', null, ['class' => 'form-control datepicker', 'id' => 'EndDate', 'required']) }}
    </div>                                                                                                                     
</div> 

<div class="form-group row">                          
    <div class="col-3">
        <label for="amount" class="caption">System Account Balance</label>
        {{ Form::text('system_amount', '0.0', ['class' => 'form-control', 'id' => 'systemBal', 'readonly']) }}
    </div>    
    <div class="col-2">
        <label for="amount" class="caption">Opening Balance</label>
        {{ Form::text('open_amount', '0.0', ['class' => 'form-control', 'id' => 'openBal', 'required']) }}
    </div> 
    <div class="col-2">
        <label for="amount" class="caption">Closing Balance</label>
        {{ Form::text('close_amount', '0.0', ['class' => 'form-control', 'id' => 'closeBal', 'required']) }}
    </div>                                                                        
</div>

<div class="table-responsive">
    <table class="table tfr" id="tranxTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th class="text-center">Date</th>
                <th>Transaction Id</th>
                <th width="40%" class="text-center">Note</th>
                <th width="12%" class="text-center">Debit</th>
                <th width="12%" class="text-center">Credit</th>
                <th width="5%">Action <input class="form-check-input checkall" type="checkbox" style="margin-left: .3em;"></th>
            </tr>
        </thead>
        <tbody>       
        </tbody>                
    </table>
</div>
<div class="form-group row">
    <div class="col-2 ml-auto">
        <label for="debit">Total Debit:</label>
        <input type="text" class="form-control" id="debitTtl" readonly>
        <label for="debit">Total Credit:</label>
        <input type="text" class="form-control" id="creditTtl" readonly>
    </div>
</div>
<div class="form-group row">                            
    <div class="col-12"> 
        {{ Form::submit('Reconcile', ['class' => 'btn btn-primary btn-lg float-right']) }}                          
    </div>
</div>