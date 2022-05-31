@extends ('core.layouts.app')

@section('title', 'Create | Reconciliation Management')

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
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });

    $('.datepicker')
    .datepicker({format: "{{config('core.user_date_format')}}", autoHide: true})
    .datepicker('setDate', new Date());

    // submit form
    $('#reconciliation').submit(function(e) {
        $('#startDate').attr('disabled', false);
        const systemBal = $('#systemBal').val().replace(/,/g, '');
        const closeBal = $('#closeBal').val().replace(/,/g, '');
        if (systemBal != closeBal * 1) {
            e.preventDefault();
            alert('System balance must be equivalent to Closing balance !');
        } 
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
                <input type="hidden" name="id[]" value="${v.id}">
                <input type="hidden" name="is_reconciled[]" value="0" class="is_reconciled">
            </tr>
        `;
    }

    // on selecting bank
    $('#bank').change(function() {
        // fetch transactions
        $.ajax({
            url: "{{ route('biller.reconciliations.ledger_transactions') }}?id=" + $(this).val(),
            success: data => {
                $('#tranxTbl tbody tr').remove();
                data.forEach(v => $('#tranxTbl tbody').append(tranxRow(v)));
            }
        });
        // set opening balance
        const balance = $(this).children('option:selected').attr('openingBalance')
        $('#openBal').val(parseFloat(balance).toLocaleString());
        $.ajax({
            url: "{{ route('biller.reconciliations.last_reconciliation') }}?id=" + $(this).val(),
            success: data => {
                $('#systemBal').val('0.00');
                $('#openBal').attr('readonly', false);
                $('#startDate').datepicker('setDate', new Date()).attr('disabled', false);
                if (data.hasOwnProperty('id')) {
                    $('#systemBal').val(parseFloat(data.system_amount).toLocaleString());
                    $('#openBal').val(parseFloat(data.close_amount).toLocaleString()).attr('readonly', true);
                    $('#startDate').datepicker('setDate', new Date(data.end_date)).attr('disabled', true);
                }
            }
        });
    });

    // on checking a checkbox update system account balance
    $('#tranxTbl').on('change', '.check', function() {
        const row = $(this).parents('tr');
        const credit = row.find('.credit').text().replace(/,/g, '')*1;
        const debit = row.find('.debit').text().replace(/,/g, '')*1;
        let balance = $('#systemBal').val().replace(/,/g, '')*1;
        if ($(this).is(':checked')) {
            if (credit > 0) balance -= credit;
            else if (debit > 0) balance += debit;
            row.find('.is_reconciled').val(1);
        } else {
            if (credit > 0) balance += credit;
            else if (debit > 0) balance -= debit;
            row.find('.is_reconciled').val(0);
        }
        $('#systemBal').val(parseFloat(balance.toFixed(2)).toLocaleString());
        calcTotals();
    });

    // check or uncheck all transactions
    $('.checkall').change(function() {
        if ($(this).is(':checked')) {
            return $('#tranxTbl tbody tr').each(function() {
                const checkbox = $(this).find(':checkbox');
                if (!checkbox.is(':checked')) checkbox.prop('checked', true).change();
            });
        } 
        $('#tranxTbl tbody tr').each(function() {
            $('#tranxTbl tbody tr').each(function() {
                const checkbox = $(this).find(':checkbox');
                if (checkbox.is(':checked')) checkbox.prop('checked', false).change();
            });
        });
    });

    function calcTotals() {
        let debitBal = 0;
        let creditBal = 0;
        $('#tranxTbl tbody tr').each(function() {
            const debit = $(this).find('.debit').text().replace(/,/g, '')*1;
            const credit = $(this).find('.credit').text().replace(/,/g, '')*1;
            if ($(this).find(':checkbox').is(':checked')){
                debitBal += debit;
                creditBal += credit;
            }
        });
        $('#debitTtl').val(parseFloat(debitBal.toFixed(2)).toLocaleString());
        $('#creditTtl').val(parseFloat(creditBal.toFixed(2)).toLocaleString());
    }
</script>
@endsection