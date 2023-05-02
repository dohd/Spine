@extends('core.layouts.app')

@section('title',  'Journals Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Journals Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.journals.partials.journals-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card round">
                    <div class="card-content">
                        <div class="card-body ">
                            {{ Form::open(['route' => 'biller.journals.store', 'method' => 'post', 'id' => 'journal']) }}
                                @include("focus.journals.form")
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    $.ajaxSetup({headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});

    $('#journal').submit(function(e) {
        if ($('#debitTtl').val() != $('#creditTtl').val()) {
            e.preventDefault();
            alert('Total Debit must be equal to Total Credit !');
        }
    });

    $('.datepicker')
    .datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true,})
    .datepicker('setDate', new Date());

    // fetch unselected manual ledgers
    const accountIds = [];
    function select2Config() {
        return {
            ajax: {
                url: "{{ route('biller.journals.journal_accounts') }}",
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({keyword: term}),
                processResults: data => {
                    const results = data.map(v => ({id: v.id, text: v.holder + ' - ' + v.account_type?.category}))
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