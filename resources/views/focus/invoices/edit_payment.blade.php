@extends ('core.layouts.app')

@section('title', 'Edit | Invoice Payment Management')

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
                    {{ Form::model($payment, ['route' => ['biller.invoices.update_payment', $payment], 'method' => 'PATCH']) }}
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
@include('focus/invoices/form_js')
<script>
    // default
    const payment = @json($payment);
    $('#person').attr('disabled', true);
    $('#date').datepicker('setDate', new Date(payment.date)); 
    $('#payment_type').attr('disabled', true);
    const amount = parseFloat($('#amount').val().replace(/,/g, ''));
    $('#amount').val(amount.toLocaleString());
    calcTotal();

    // on amount change
    $('#amount').keyup(function() {
        let dueTotal = 0;
        let allocateTotal = 0;
        let amount = parseFloat($(this).val().replace(/,/g, ''));
        const lastCount = $('#invoiceTbl tbody tr').length - 1;
        $('#invoiceTbl tbody tr').each(function(i) {
            if (i == lastCount) return;
            const invAmount = parseFloat($(this).find('.inv-amount').text().replace(/,/g, ''))
            if (invAmount > amount) $(this).find('.paid').val(amount.toLocaleString());
            else if (amount > invAmount) $(this).find('.paid').val(invAmount.toLocaleString());
            else $(this).find('.paid').val(0);
            const paid = parseFloat($(this).find('.paid').val().replace(/,/g, ''));
            amount -= paid;
            dueTotal += invAmount;
            allocateTotal += paid;
        });
        $('#allocate_ttl').val(parseFloat(allocateTotal.toFixed(2)).toLocaleString());
        $('#balance').val(parseFloat(dueTotal - allocateTotal).toLocaleString());
    }).focusout(function() { 
        if (!$(this).val()) return;
        $(this).val(parseFloat($(this).val().replace(/,/g, '')).toLocaleString());
    }).focus(function() {
        if (!$('#person').val()) $(this).blur();
    });    

    // compute totals
    function calcTotal() {
        let dueTotal = 0;
        let allocateTotal = 0;
        const lastCount = $('#invoiceTbl tbody tr').length - 1;
        $('#invoiceTbl tbody tr').each(function(i) {
            if (i == lastCount) return;
            const invAmount = parseFloat($(this).find('.inv-amount').text().replace(/,/g, '')) || 0;
            const paid = parseFloat($(this).find('.paid').val().replace(/,/g, '')) || 0;
            dueTotal += invAmount;
            allocateTotal += paid;
        });
        $('#allocate_ttl').val(parseFloat(allocateTotal.toFixed(2)).toLocaleString());
        $('#balance').val(parseFloat(dueTotal - allocateTotal).toLocaleString());
    }
</script>
@endsection
