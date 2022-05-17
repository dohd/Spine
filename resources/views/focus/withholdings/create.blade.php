@extends ('core.layouts.app')

@section('title', 'Withholding | Create')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Withholdings  Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.withholdings.partials.withholdings-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.withholdings.store', 'method' => 'POST', 'id' => 'withholding']) }}
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
                                {{ Form::text('tid', @$last_tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
                            </div> 

                            <div class="col-2">
                                <label for="date" class="caption">Date</label>
                                {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
                            </div>

                            <div class="col-2">
                                <label for="date" class="caption">Due Date</label>
                                {{ Form::text('due_date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
                            </div>                                                                            
                        </div> 

                        <div class="row mb-2">  
                            <div class="col-3">
                                <label for="certificate" class="caption">Certificate</label>
                                <select name="certificate" id="" class="form-control" required>
                                    <option value="">-- Select Type--</option>
                                    <option value="vat">VAT</option>
                                    <option value="vat">Income</option>
                                </select>
                            
                            </div>  
                            <div class="col-2">
                                <label for="amount" class="caption">Amount (Ksh.)</label>
                                {{ Form::text('amount', null, ['class' => 'form-control', 'id' => 'deposit', 'required']) }}
                            </div>                              
                            <div class="col-2">
                                <label for="reference" class="caption">Reference</label>
                                {{ Form::text('doc_ref', null, ['class' => 'form-control', 'required']) }}
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
                                        <div class="form-inline mb-1 float-right">
                                            <label for="total_bill">Total Bill</label>
                                            {{ Form::text('amount_ttl', 0, ['class' => 'form-control col-7 ml-1', 'id' => 'amount_ttl', 'readonly']) }}
                                        </div>
                                        <div class="form-inline float-right">
                                            <label for="total_paid">Total Paid</label>
                                            {{ Form::text('deposit_ttl', 0, ['class' => 'form-control col-7 ml-1', 'id' => 'deposit_ttl', 'readonly']) }}
                                        </div>                                         
                                    </td>
                                </tr>
                            </tbody>                
                        </table>
                        <div class="form-group row">                            
                            <div class="col-12"> 
                                <button type="button" class="btn btn-primary btn-lg float-right mr-3" id="receivePay">
                                    Generate
                                </button>
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
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    // form submit
    $('#receivePay').click(function() {
        swal({
            title: 'Are You  Sure?',
            icon: "warning",
            buttons: true,
            dangerMode: true,
            showCancelButton: true,
        }, () => $('form#withholding').submit());   
    });

    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    $('.datepicker')
    .datepicker({format: "{{config('core.user_date_format')}}", autoHide: true})
    .datepicker('setDate', new Date())

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
        $('#deposit').val('');
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
    $('#deposit').on('focus', function(e) {
        if (!$('#person').val()) $(this).blur();
    });
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