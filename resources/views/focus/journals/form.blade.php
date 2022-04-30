<div class='form-group row'>
    <div class='col-2'>
        <div><label for="tid">Journal ID</label></div>
        {{ Form::text('tid', @$last_journal->tid+1, ['class' => 'form-control round', 'readonly']) }}
    </div>
    <div class='col-2'>
        <div><label for="date">Date</label></div>
        <input type="text" name="date" class="form-control datepicker round">
    </div>
    <div class='col-8'>
        <div><label for="note">Note</label></div>
        {{ Form::text('note', null, ['class' => 'form-control round', 'required']) }}
    </div>
</div>
<div class="table-responsive">        
    <table id="ledgerTbl" class="table">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th width="40%">Ledger Account Name</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><select name="account_id[]" id="account-0" class="form-control account" data-placeholder="Search Ledger"></select></td>
                <td><input type="text" class="form-control debit" name="debit[]" placeholder="0.00" id="debit-0"></td>
                <td><input type="text" class="form-control credit" name="credit[]" placeholder="0.00" id="credit-0"></td>
                <td><button type="button" class="btn btn-danger d-none remove"><i class="fa fa-trash"></i></button></td>
            </tr>
        </tbody>
    </table>
</div>
<div class="form-group row">
    <div class="col-2 ml-2">
        <button type="button" class="btn btn-success" id="addLedger">Add Ledger</button>
    </div>
</div>
<div class="form-group row">
    <div class="form-inline col-3 ml-auto">
        <label for="debit_total">Debit Total:</label>
        <input type="text" class="form-control ml-2 mb-1" name="debit_ttl" id="debitTtl" readonly>
        <label for="debit_total">Credit Total:</label>
        <input type="text" class="form-control ml-2" name="credit_ttl"  id="creditTtl" readonly>
    </div>
</div>
<div class="form-group row">
    <div class="col-2 ml-auto mr-2">
        {{ Form::submit('Create Journal', ['class' => 'btn btn-primary btn-lg block']) }}
    </div>
</div>