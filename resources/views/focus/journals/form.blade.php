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
                <td><button type="button" class="btn btn-danger d-none remove">Remove</button></td>
            </tr>
        </tbody>
    </table>
</div>
<div class="form-group row">
    <div class="col-2 ml-2">
        <button type="button" class="btn btn-success" id="addLedger">Add Ledger</button>
    </div>
</div>
<div class="row">
    <div class="form-inline col-3 ml-auto">
        <label for="debit_total">Debit Total</label>
        <input type="text" class="form-control ml-2 mb-1" name="debit_ttl" id="debitTtl" readonly>
        <label for="debit_total">Credit Total</label>
        <input type="text" class="form-control ml-2" name="credit_ttl"  id="creditTtl" readonly>
    </div>
</div>
<div class="form-group row">
    <div class="col-2 ml-auto mr-2">
        {{ Form::submit('Create Journal', ['class' => 'btn btn-primary btn-lg block round mt-3']) }}
    </div>
</div>

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    $('.datepicker')
    .datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true,})
    .datepicker('setDate', new Date());

    // fetch unselected manual ledgers
    const accountIds = [];
    function select2Config() {
        return {
            ajax: {
                url: "{{ route('biller.journals.journal_ledgers') }}",
                quietMillis: 50,
                processResults: data => {
                    const results = data
                        .map(v => ({id: v.id, text: v.holder + ' - ' + v.account_type.category}))
                        .filter(v => !accountIds.includes(v.id));
                    return {results}; 
                },
            }
        }
    };

    // on selecting account ledger update accountIds
    $('#ledgerTbl').on('change', '.account', function() {
        accountIds.push($(this).val()*1);
    });

    // on change debit or credit 
    $('#ledgerTbl').on('change', '.debit, .credit', function() {
        const credit = $(this).parents('tr').find('.credit');
        const debit = $(this).parents('tr').find('.debit');
        calcTotals();

        if ($(this).is('.debit') && debit.val()) {
            return credit.val(0).attr('readonly', true);
        } else if ($(this).is('.credit') && credit.val()) {
            return debit.val(0).attr('readonly', true);
        }
        debit.val('').attr('readonly', false);
        credit.val('').attr('readonly', false);
    });

    // remove button
    $('#ledgerTbl').on('click', '.remove', function() {
        const row = $(this).parents('tr');
        const id = row.find('.account').val()*1;
        if (accountIds.includes(id)) 
            accountIds.splice(accountIds.indexOf(id), 1);
        row.remove();
        calcTotals();
    });

    // click add ledger button
    let rowId = 0;
    const rowHtml = $('#ledgerTbl tbody tr:first').html();
    $('#account-0').select2(select2Config());
    $('#addLedger').click(function() {
        rowId++;
        const html = rowHtml.replace(/-0/g, '-'+rowId).replace(/d-none/, '');
        $('#ledgerTbl tbody').append('<tr>'+html+'</tr>');
        $('#account-'+rowId).select2(select2Config());
    });

    // totals
    function calcTotals() {
        let debitTtl = 0;
        let creditTtl = 0;
        $('#ledgerTbl tbody tr').each(function() {
            const credit = $(this).find('.credit').val().replace(/,/g, '');
            const debit = $(this).find('.debit').val().replace(/,/g, '');
            if (credit) creditTtl += credit * 1;
            if (debit) debitTtl += debit * 1;
        });
        $('#debitTtl').val(parseFloat(debitTtl.toFixed(2)).toLocaleString());
        $('#creditTtl').val(parseFloat(creditTtl.toFixed(2)).toLocaleString());
    }
</script>
@endsection