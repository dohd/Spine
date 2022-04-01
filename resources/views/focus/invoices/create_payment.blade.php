@extends ('core.layouts.app')

@section('title', 'Invoice Payment | Create')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="content-header-title">Invoice Payment</h4>
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
                                    <select id="person" name="customer_id" class="form-control select-box" data-placeholder="Search Customer" required>
                                    </select>
                                </div>
                            </div>

                            <div class="col-2">
                                <label for="reference" class="caption">Transaction ID</label>
                                <div class="input-group">
                                    {{ Form::text('tid', 1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
                                </div>
                            </div> 

                            <div class="col-2">
                                <label for="date" class="caption">Date</label>
                                <div class="input-group">
                                    {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
                                </div>
                            </div>

                            <div class="col-2">
                                <label for="date" class="caption">Due Date</label>
                                <div class="input-group">
                                    {{ Form::text('due_date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
                                </div>
                            </div>                                                                            
                        </div> 

                        <div class="row mb-2">  
                            <div class="col-3">
                                <label for="payment_mode">Payment Mode</label>
                                <select name="payment_mode" id="" class="form-control" required>
                                   <option value="">-- Select Mode --</option>
                                    @foreach (['eft', 'rtgs','cash', 'mpesa', 'cheque'] as $val)
                                        <option value="{{ $val }}">{{ strtoupper($val) }}</option>
                                    @endforeach
                                </select>
                            </div>  
                            <div class="col-2">
                                <label for="deposit" class="caption">Deposit (Ksh.)</label>
                                <div class="input-group">
                                    {{ Form::text('deposit', null, ['class' => 'form-control', 'id' => 'deposit', 'required']) }}
                                </div>
                            </div>  
                            <div class="col-2">
                                <label for="paid_from">Paid From</label>
                                <select name="account_id" id="" class="form-control" required>
                                   <option value="">-- Select Bank --</option>
                                    @foreach ($accounts as $row)
                                        <option value="{{ $row->id }}">{{ $row->holder }}</option>
                                    @endforeach
                                </select>
                            </div>  
                            <div class="col-2">
                                <label for="payment_mode">Document Type</label>
                                <select name="doc_ref_type" id="" class="form-control" required>
                                   <option value="">-- Select Type --</option>
                                    @foreach (['receipt', 'dnote', 'voucher'] as $val)
                                        <option value="{{ $val }}">{{ ucfirst($val) }}</option>
                                    @endforeach
                                </select>
                            </div>     
                            <div class="col-2">
                                <label for="reference" class="caption">Document Reference</label>
                                <div class="input-group">
                                    {{ Form::text('doc_ref', null, ['class' => 'form-control', 'required']) }}
                                </div>
                            </div>                                                     
                        </div>

                        <table class="table-responsive tfr my_stripe_single" id="invoiceTbl">
                            <thead>
                                <tr class="item_header bg-gradient-directional-blue white">
                                    <th width="15%" class="text-center">Due date</th>
                                    <th width="5%">Invoice Number</th>
                                    <th width="40%" class="text-center">Note</th>
                                    <th width="10%">Status</th>
                                    <th width="15%" class="text-center">Amount (VAT Inc)</th>
                                    <th width="15%" class="text-center">Paid (Ksh.)</th>
                                </tr>
                            </thead>
                            <tbody>                                
                                <tr class="bg-white">
                                    <td colspan="4"></td>
                                    <td colspan="2">
                                        <div class="row no-gutters mb-1">
                                            <div class="col-6 pl-3 pt-1"><b>Total Bill:</b></div>
                                            <div class="col-6">
                                                 {{ Form::text('amount_ttl', 0, ['class' => 'form-control', 'id' => 'amount_ttl', 'readonly']) }}
                                            </div>                          
                                        </div>
                                        <div class="row no-gutters">
                                            <div class="col-6 pl-3 pt-1"><b>Total Paid:</b></div>
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
            data: ({term}) => ({search: term}),
            processResults: result => {
                return { results: result.map(v => ({text: `${v.name} - ${v.company}`, id: v.id }))};
            }      
        }
    });

    // On adding paid values
    $('#invoiceTbl').on('change', '.paid', function() {
        const amount = $(this).parents('tr').find('.amount').text().replace(/,/g, '') * 1;
        const paid = $(this).val().replace(/,/g, '') * 1;
        if (paid > amount) $(this).val(amount.toLocaleString());
        calcTotal();
    });

    // invoice row
    function invoiceRow(v, i) {
        const amount = parseFloat(v.total - v.amountpaid).toLocaleString();
        return `
            <tr>
                <td class="text-center">${new Date(v.invoiceduedate).toDateString()}</td>
                <td>${v.tid}</td>
                <td class="text-center">${v.notes}</td>
                <td>${v.status}</td>
                <td class="text-center amount"><b>${amount}</b></td>
                <td><input type="text" class="form-control paid" name="paid[]"></td>
                <input type="hidden" name="invoice_id[]" value="${v.id}">
            </tr>
        `;
    }

    // load client invoices
    $('#person').change(function() {
        $.ajax({
            url: "{{ route('biller.invoices.client_invoices') }}?id=" + $(this).val(),
            success: result => {
                $('#invoiceTbl tbody tr:not(:eq(-1))').remove();
                if (!result.length) return;
                result.forEach((v, i) => {
                    $('#invoiceTbl tbody tr:eq(-1)').before(invoiceRow(v, i));
                });
            }
        });
    });

    // On deposit change
    $('#deposit').change(function() {
        let amountSum = 0;
        let depoSum = 0;
        let depo = $(this).val().replace(/,/g, '') * 1;
        $(this).val(parseFloat(depo).toLocaleString());
        const rows = $('#invoiceTbl tbody tr').length;
        $('#invoiceTbl tbody tr').each(function() {
            if ($(this).index() == rows-1) return;
            const amount = $(this).find('.amount').text().replace(/,/g, '') * 1;
            if (depo > amount) $(this).find('.paid').val(amount.toLocaleString());
            else if (depo > 0) $(this).find('.paid').val(depo.toLocaleString());
            else $(this).find('.paid').val(0);
            const paid = $(this).find('.paid').val().replace(/,/g, '') * 1;
            depo -= amount;
            amountSum += amount;
            depoSum += paid;
        });
        $('#amount_ttl').val(amountSum.toLocaleString());
        $('#deposit_ttl').val(depoSum.toLocaleString());
    });

    function calcTotal() {
        let amountSum = 0;
        let depoSum = 0;
        const rows = $('#invoiceTbl tbody tr').length;
        $('#invoiceTbl tbody tr').each(function() {
            if ($(this).index() == rows-1) return;
            const amount = $(this).find('.amount').text().replace(/,/g, '') * 1;
            const paid = $(this).find('.paid').val().replace(/,/g, '') * 1;
            amountSum += amount;
            depoSum += paid;
        });
        $('#amount_ttl').val(amountSum.toLocaleString());
        $('#deposit_ttl').val(depoSum.toLocaleString());
    }
</script>
@endsection
