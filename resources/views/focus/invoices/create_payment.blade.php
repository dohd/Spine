@extends ('core.layouts.app')

@section('title', 'Receive Payment | Invoice Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Invoice Payment Management</h4>
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
                    {{ Form::open(['route' => 'biller.invoices.store_payment', 'method' => 'POST', 'id' => 'invoicePay']) }}
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

    $('form').submit(function(e) {
        // enable disabled attributes
        ['#paymentMode', '#allocated', '#account', '#date'].forEach(v => $(v).attr('disabled', false));
        // 
        if ($('#deposit').val() == 0) {
            e.preventDefault();
            return alert('Enter payment amount!');
        } else if ($('#allocated').val() == 1 && $('#deposit_ttl').val() == 0) {
            e.preventDefault();
            return alert('Allocate payment amount on at least one invoice!');
        }
    });

    // datepicker
    $('.datepicker')
    .datepicker({format: "{{config('core.user_date_format')}}", autoHide: true})
    .datepicker('setDate', new Date());

    // customer select2 config
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
        const balance = $(this).parents('tr').find('.amount').text().replace(/,/g, '') * 1;
        const paid = $(this).val().replace(/,/g, '') * 1;
        if (balance > 0 && balance < paid) $(this).val(balance.toLocaleString());
        calcTotal();
    });

    // On deposit change
    $('#deposit').on('focus', function(e) {
        if (!$('#person').val()) $(this).blur();
    });
    $('#deposit').keyup(function() {
        if ($('#allocated').val() == 0) return;
        let amountSum = 0;
        let depoSum = 0;
        let depo = parseFloat($(this).val().replace(/,/g, ''));
        const rows = $('#invoiceTbl tbody tr').length;
        $('#invoiceTbl tbody tr').each(function() {
            if ($(this).index() == rows-1) return;
            const amount = parseFloat($(this).find('.amount').text().replace(/,/g, ''));
            if (depo > amount) $(this).find('.paid').val(amount.toLocaleString());
            else if (depo > 0) $(this).find('.paid').val(depo.toLocaleString());
            else $(this).find('.paid').val(0);
            const paid = parseFloat($(this).find('.paid').val().replace(/,/g, ''));
            depo -= amount;
            amountSum += amount;
            depoSum += paid;
        });
        $('#amount_ttl').val(amountSum.toLocaleString());
        $('#deposit_ttl').val(depoSum.toLocaleString());
    }).focusout(function() { 
        $(this).val(parseFloat($(this).val().replace(/,/g, '')).toLocaleString());
    });

    // invoice row
    function invoiceRow(v, i) {
        const amount = parseFloat(v.total).toLocaleString();
        const paid = parseFloat(v.amountpaid).toLocaleString();
        const balance = parseFloat(v.total - v.amountpaid).toLocaleString();
        return `
            <tr>
                <td class="text-center">${new Date(v.invoiceduedate).toDateString()}</td>
                <td>${v.tid}</td>
                <td class="text-center">${v.notes}</td>
                <td>${v.status}</td>
                <td>${amount}</td>
                <td>${paid}</td>
                <td class="text-center amount"><b>${balance}</b></td>
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
            $('#deposit').keyup();
            $('#source').attr('disabled', false);
            $('#advanced').attr('disabled', true);
        }
    });

    // on change allocation source
    $('#source').change(function() {
        // advance payment
        if ($(this).val() == 'advance') {
            $('#deposit').attr('readonly', true);
            $('#account').attr('disabled', true);
            $('#advanced').attr('disabled', false);
        } else {
            $('#deposit').attr('readonly', false);
            $('#account').attr('disabled', false);
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
