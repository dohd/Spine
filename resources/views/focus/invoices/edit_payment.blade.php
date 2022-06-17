@extends ('core.layouts.app')

@section('title', 'Receive Payment | Invoice Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Invoice Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.invoices.partials.payments-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::model($payment, ['route' => ['biller.invoices.update_payment', $payment->id], 'method' => 'PATCH']) }}
                        @include('focus.invoices.payment_form')
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
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    // form submmit
    $('form').submit(function(e) {
        // enable disabled attributes
        ['#paymentMode', '#allocated', '#account', '#date', '#person'].forEach(v => $(v).attr('disabled', false));
        // on submit conditions
        const rowNum = $('#invoiceTbl tbody tr').length
        if ($('#deposit_ttl').val() == 0 && rowNum > 1) {
            e.preventDefault();
            return alert('Allocate payment amount on at least one invoice!');
        } else if ($('#deposit').val() == 0) {
            e.preventDefault();
            return alert('Enter payment amount!'); 
        } else {
            const amountTotal = $('#deposit').val().replace(/,/g, '') * 1;
            const allocatedTotal = $('#deposit_ttl').val().replace(/,/g, '') * 1;
            const source = $('#source').val();
            if (amountTotal != allocatedTotal && rowNum > 1 && source == 'direct') {
                e.preventDefault();
                alert('Payment Amount should be equal to Total Allocated Amount');
            }
        } 
    });

    // default
    $('#person').attr('disabled', true);
    
    calcTotal();

    // datepicker
    $('.datepicker')
    .datepicker({format: "{{config('core.user_date_format')}}", autoHide: true})
    .datepicker('setDate', new Date());

    // 
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

    // On deposit change
    $('#deposit').on('focus', function(e) {
        if (!$('#person').val()) $(this).blur();
    });
    $('#deposit').change(function() {
        let totalAmount = 0;
        let totalAllocated = 0;
        let depo = $(this).val().replace(/,/g, '') * 1;
        $(this).val(parseFloat(depo).toLocaleString());
        const rows = $('#invoiceTbl tbody tr').length;
        $('#invoiceTbl tbody tr').each(function(i) {
            if (i == rows-1) return;
            const amount = $(this).find('.amount').text().replace(/,/g, '') * 1;
            if (depo > amount) $(this).find('.paid').val(amount.toLocaleString());
            else if (depo > 0) $(this).find('.paid').val(depo.toLocaleString());
            else $(this).find('.paid').val(0);
            const paid = $(this).find('.paid').val().replace(/,/g, '') * 1;
            depo -= amount;
            totalAmount += amount;
            totalAllocated += paid;
        });
        $('#amount_ttl').val(totalAmount.toLocaleString());
        $('#deposit_ttl').val(totalAllocated.toLocaleString());
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
                <td class="amount"><b>${amount}</b></td>
                <td><input type="text" class="form-control paid" name="paid[]"></td>
                <input type="hidden" name="invoice_id[]" value="${v.id}">
            </tr>
        `;
    }

    // on change customer
    $('#person').change(function() {
        $('#deposit').val('');
        // load client invoices
        $.ajax({
            url: "{{ route('biller.invoices.client_invoices') }}?id=" + $(this).val(),
            success: result => {
                $('#invoiceTbl tbody tr:not(:eq(-1))').remove();
                if (!result.length) return;
                result.forEach((v, i) => {
                    $('#invoiceTbl tbody tr:eq(-1)').before(invoiceRow(v, i));
                });
                calcTotal();
            }
        });
    });

    // on change allocation type
    $('#allocated').change(function() {
        // on account
        if ($(this).val() == 0) {
            $('#invoiceTbl tbody tr').each(function() {
                $(this).find('.paid').val('').change();
            });
            $('#source').attr('disabled', true);
            $('#advanced').attr('disabled', false);
            $('#invoiceTbl tbody tr:not(:eq(-1))').remove();
        } else {
            $('#deposit').change();
            $('#source').attr('disabled', false);
            $('#advanced').attr('disabled', true);
        }
    });

    // on change allocation source
    $('#source').change(function() {
        // advance payment
        if ($(this).val() == 'advance') {
            $('#deposit').attr('readonly', true);
            $('#paymentMode').attr('disabled', true);
            $('#reference').attr('readonly', true);
            $('#date').attr('disabled', true);
            $('#account').attr('disabled', true);
            $('#advanced').attr('disabled', false);
        } else {
            ['#paymentMode', '#date', '#account'].forEach(v => $(v).attr('disabled', false));
            ['#deposit','#reference'].forEach(v => $(v).attr('readonly', false));
            $('#advanced').attr('disabled', true);
        }
    });

    // on change advanced payment
    $('#advanced').change(function() {
        let balance = $(this).find(':selected').attr('balance');
        balance = balance.replace(/,/g, '') * 1;
        balance = parseFloat(balance.toFixed(2)).toLocaleString();
        if ($('#allocated').val() == 0) return;
        $('#deposit').val(balance).change();
    });

    // compute totals
    function calcTotal() {
        let totalAmount = 0;
        let totalAllocated = 0;
        const rowNum = $('#invoiceTbl tbody tr').length;
        $('#invoiceTbl tbody tr').each(function(i) {
            if (i == rowNum - 1) return;
            const amount = $(this).find('.amount').text().replace(/,/g, '');
            const paid = $(this).find('.paid').val().replace(/,/g, '');
            totalAmount += amount*1;
            totalAllocated += paid*1;
        });
        $('#amount_ttl').val(totalAmount.toLocaleString());
        $('#deposit_ttl').val(totalAllocated.toLocaleString());
    }
</script>
@endsection