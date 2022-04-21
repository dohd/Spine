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
                <td>
                    <select name="account_id[]" id="account-0" class="form-control account">
                        <option value="1">KCB Loan - Expense</option>
                    </select>
                </td>
                <td><input type="text" name="debit[]" value="20,000" class="form-control"></td>
                <td><input type="text" name="credit[]" value="20,000" class="form-control"></td>
                <td><button type="button" class="btn btn-danger">Remove</button></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="form-group row">
    <div class="col-2 ml-2">
        <button type="button" class="btn btn-success">Add Ledger</button>
    </div>
    <div class="col-2 ml-auto mr-5">
        {{ Form::submit('Create Journal', ['class' => 'btn btn-primary btn-lg block round mt-5']) }}
    </div>
</div>

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    $('.datepicker')
    .datepicker({
        autoHide: true,
        format: "{{ config('core.user_date_format') }}"
    })
    .datepicker('setDate', new Date());

    $('.account').select2();
</script>
@endsection