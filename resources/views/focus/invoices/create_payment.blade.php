@extends ('core.layouts.app')

@section('title', 'Invoice Payment | Create')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="content-header-title">Invoice Payment Management</h4>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.invoices.store_payment', 'method' => 'POST', 'id' => 'stoPaymnt']) }}
                        <div class="row mb-1">
                            <div class="col-5">
                                <label for="customer" class="caption">Search Customer</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                    <select id="person" name="customer_id" class="form-control select-box" required>
                                        <option value="">-- Select Customer --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <label for="ledgerAccount" class="caption">Ledger Account</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                    <select class="form-control" name="account_id" id="account_id" required>
                                        <option value="">-- Select Ledger Account --</option>
                                        @foreach ($accounts as $row)
                                            <option value="{{ $row->id }}">{{ $row->holder }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> 
                            <div class="col-2">
                                <label for="date" class="caption">Date</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                    {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
                                </div>
                            </div>
                            
                            <div class="col-2">
                                <label for="tid" class="caption">Transaction ID</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                    {{ Form::text('tid', 1, ['class' => 'form-control round', 'id' => 'tid', 'readonly']) }}
                                    <input type="hidden" name="tid" value=1>
                                </div>
                            </div>                                                       
                        </div> 

                        <div class="row mb-1">
                            <div class="col-2">
                                <label for="deposit" class="caption">Deposit (Ksh.)</label>
                                <div class="input-group">
                                    {{ Form::text('deposit', null, ['class' => 'form-control', 'id' => 'deposit', 'required']) }}
                                </div>
                            </div>  
                        </div>

                        <table class="table-responsive tfr my_stripe_single" id="invoiceTbl">
                            <thead>
                                <tr class="item_header bg-gradient-directional-blue white">
                                    <th width="10%" class="text-center">#</th>
                                    <th width="10%">Invoice Number</th>
                                    <th width="40%" class="text-center">Description</th>
                                    <th width="20%" class="text-center">Amount (VAT Inc)</th>
                                    <th width="20%" class="text-center">Deposit (Ksh.)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-white">
                                    <td colspan="3"></td>
                                    <td colspan="2">
                                        <div class="row no-gutters mb-1">
                                            <div class="col-6 pl-3 pt-1"><b>Total Bill Amount:</b></div>
                                            <div class="col-6">
                                                 {{ Form::text('amount_ttl', 0, ['class' => 'form-control', 'id' => 'amount_ttl', 'readonly']) }}
                                            </div>                          
                                        </div>
                                        <div class="row no-gutters">
                                            <div class="col-6 pl-3 pt-1"><b>Total Deposited:</b></div>
                                            <div class="col-6">
                                            {{ Form::text('deposit_ttl', 0, ['class' => 'form-control', 'id' => 'deposit_ttl', 'readonly']) }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>                
                        </table>                        

                        <div class="row mt-1">                            
                            <div class="col-12"> 
                                {{ Form::submit('Receive Payment', ['class' => 'btn btn-primary btn-lg float-right']) }}                          
                            </div>
                        </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    $('.datepicker')
    .datepicker({format: "{{config('core.user_date_format')}}"})
    .datepicker('setDate', new Date())
    .change(function () { $(this).datepicker('hide') });

    // On searching customer
    $('#person').select2({
        ajax: {
            url: "{{ route('biller.customers.select') }}",
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: function(params) { 
                return { search: params.term }
            },
            processResults: function(data) {
                return {
                    results: $.map(data, function(item) {
                        return {
                            text: `${item.name} - ${item.company}`,
                            id: item.id
                        }
                    })
                };
            },
        }
    });

    // load customer invoices
    $('#person').change(function() {
        $.ajax({
            url: "{{ route('biller.invoices.client_invoices') }}?id=" + $(this).val(),
            success: result => {
                if (!result.length) $('#invoiceTbl tbody tr:not(:eq(-1))').remove();
                result.forEach((v, i) => {
                    $('#invoiceTbl tbody tr:eq(-1)').before(invoiceRow(v, i));
                });
            }
        });
    });

    // On adding paid values
    $('#invoiceTbl').on('change', '.paid', function() {
        const amount = $(this).parents('tr').find('.amount').text().replace(/,/g, '');
        if (paid > amount) $(this).val((amount*1).toLocaleString());
    });

    // invoice rows
    function invoiceRow(v, i) {
        const total = parseFloat(v.total).toLocaleString();
        return `
            <tr>
                <td class="text-center">${i+1}</td>
                <td>${v.tid}</td>
                <td class="text-center">${v.notes}</td>
                <td class="text-center amount"><b>${total}</b></td>
                <td><input type="text" class="form-control paid" name="paid[]"></td>
                <input type="hidden" name="invoice_id[]" value="${v.id}" id="invoiceid-${i}">
            </tr>
        `;
    }

    // On deposit change
    $('#deposit').change(function() {
        const depo = $(this).val().replace(/,/g, '');
        $(this).val(parseFloat(depo).toLocaleString());

        let init = depo*1;
        let amountSum = 0;
        let depoSum = 0;
        const rows = $('#invoiceTbl tbody tr').length;
        $('#invoiceTbl tbody tr').each(function() {
            if ($(this).index() == rows-1) return;

            const amount = $(this).find('.amount').text().replace(/,/g, '');
            $(this).find('.paid').val(0);

            if (init > amount){
                $(this).find('.paid').val((amount*1).toLocaleString());
            } else if (init > 0) $(this).find('.paid').val((init*1).toLocaleString());
            const paid = $(this).find('.paid').val().replace(/,/g, '');
            
            init -= amount;
            amountSum += amount*1;
            depoSum += paid*1;
        });

        $('#amount_ttl').val(amountSum.toLocaleString());
        $('#deposit_ttl').val(depoSum.toLocaleString());
    });
</script>
@endsection
