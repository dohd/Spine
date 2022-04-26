@extends ('core.layouts.app')

@section('title', 'Reconciliation Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Reconciliations Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.reconciliations.partials.reconciliations-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.reconciliations.store', 'method' => 'POST', 'id' => 'reconciliation']) }}
                        @include('focus.reconciliations.form')
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
<script>
    $('.datepicker')
    .datepicker({format: "{{config('core.user_date_format')}}", autoHide: true})
    .datepicker('setDate', new Date())

    // on form submit
    $('#reconciliation').submit(function(e) {
        $('#startDate').attr('disabled', false);
        const systemBal = $('#systemBal').val().replace(/,/g, '');
        const closeBal = $('#closeBal').val().replace(/,/g, '');
        if (systemBal == closeBal * 1) return;
        e.preventDefault();
        alert('System balance must be equivalent to Closing balance !');
    });

    // transaction row
    function tranxRow(v) {
        return `
            <tr>
                <td class="text-center">${new Date(v.tr_date).toDateString()}</td>
                <td class="text-center">${v.tid}</td>
                <td class="text-center">${v.note}</td>
                <td class="text-center debit"><b>${parseFloat(v.debit).toLocaleString()}</b></td>
                <td class="text-center credit"><b>${parseFloat(v.credit).toLocaleString()}</b></td>
                <td class="text-center"><input class="form-check-input check" type="checkbox"></td>
                <input type="hidden" name="transaction_id[]" value="${v.id}">
            </tr>
        `;
    }

    // load account ledger transactions
    $('#bank').change(function() {
        $.ajax({
            url: "{{ route('biller.reconciliations.ledger_transactions') }}?id=" + $(this).val(),
            success: data => {
                $('#tranxTbl tbody tr').remove();
                data.forEach(v => $('#tranxTbl tbody').append(tranxRow(v)));
            }
        });
    });

    // On next reconciliation
    const obj = @json($reconciliation);
    if (obj && obj.tid > 0) {
        $('#startDate').datepicker('setDate', new Date(obj.end_date)).attr('disabled', 'true');
        $('#systemBal').val(parseFloat(obj.system_amount).toLocaleString());
        $('#openBal').val(parseFloat(obj.close_amount).toLocaleString()).attr('readonly', true);
    }

    // on checking a checkbox
    let balance = 0;
    let debitTtl = 0;
    let creditTtl = 0;
    $('#tranxTbl').on('change', '.check', function() {
        const row = $(this).parents('tr');
        credit = row.find('.credit').text().replace(/,/g, '') * 1;
        debit = row.find('.debit').text().replace(/,/g, '') * 1;
        if ($(this).is(':checked')) {
            creditTtl += credit;
            debitTtl += debit;
            if (credit) balance += credit;
            else if (debit) balance -= debit;
        } else {
            creditTtl -= credit;
            debitTtl -= debit;
            if (credit) balance -= credit;
            else if (debit) balance += debit;
        }
        $('#debitTtl').val(debitTtl.toLocaleString());
        $('#creditTtl').val(creditTtl.toLocaleString());
        if (!obj) $('#systemBal').val(balance.toLocaleString()); 
        else {
            const prev = parseFloat(obj.system_amount);
            $('#systemBal').val((prev + balance).toLocaleString());  
        }
    });

    // check all transactions
    $('.checkall').change(function() {
        if ($(this).is(':checked')) $('.check').prop('checked', true).change();
        else $('.check').prop('checked', false).change();
    });
</script>
@endsection