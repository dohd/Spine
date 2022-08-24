<script>
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

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
        },
        allowClear: true
    }).change(function() {
        $.ajax({
            url: "{{ route('biller.invoices.client_invoices') }}?id=" + $(this).val(),
            success: data => loadInvoice(data)
        });
    });

    // load invoice
    function loadInvoice(data = []) {
        $('#amount').val('');
        $('#allocate_ttl').val('');
        $('#balance').val('');
        $('#invoiceTbl tbody tr:not(:eq(-1))').remove();
        data.forEach((v, i) => {
            $('#invoiceTbl tbody tr:eq(-1)').before(invoiceRow(v, i));
        });
    }    

    // on change payment type
    const payments = @json($payments);
    $('#payment_type').change(function() {
        if ($(this).val() == 'per_invoice') {
            $('#payment').val(0).change();
            $('#person').change();
        } else loadInvoice();
        
        $('#payment option:not(:eq(0))').remove();
        payments.forEach(v => {
            if ($('#person').val() == v.customer_id && $(this).val() == v.payment_type) {
                const option = $(document.createElement('option'));
                option.val(v.id).text(
                    `${new Date(v.date).toDateString()} - ${v.payment_mode} - ${v.reference} - 
                    ${accounting.formatNumber(v.amount)} - ${v.payment_type}`
                )
                .attr('amount', v.amount)
                .attr('allocateTotal', v.allocate_ttl)
                .attr('accountId', v.account_id)
                .attr('paymentMode', v.payment_mode)
                .attr('reference', v.reference)
                .attr('date', v.date);

                $('#payment').append(option)
            }
        });
        if (payments.length) $('#payment').attr('disabled', false);
        else $('#payment').attr('disabled', true);
    });  

    // On allocation 
    $('#invoiceTbl').on('change', '.paid', function() {
        const paid = accounting.unformat($(this).val());
        $(this).val(accounting.formatNumber(paid));
        calcTotal();
        const amount = accounting.unformat($('#amount').val());
        const allocateTotal = accounting.unformat($('#allocate_ttl').val());
        if (allocateTotal > amount) {
            alert('Cannot allocate more than payment amount!');
            $(this).val(0);
            calcTotal();
        }
    });

    // on amount change
    $('#amount').keyup(function() {
        let dueTotal = 0;
        let allocateTotal = 0;
        let amount = accounting.unformat($(this).val());
        const lastCount = $('#invoiceTbl tbody tr').length - 1;
        $('#invoiceTbl tbody tr').each(function(i) {
            if (i == lastCount) return;
            const due = accounting.unformat($(this).find('.due').text());
            if (due > amount) $(this).find('.paid').val(accounting.formatNumber(amount));
            else if (amount > due) $(this).find('.paid').val(accounting.formatNumber(due));
            else $(this).find('.paid').val(0);
            const paid = accounting.unformat($(this).find('.paid').val());
            amount -= paid;
            dueTotal += due;
            allocateTotal += paid;
        });
        $('#allocate_ttl').val(accounting.formatNumber(allocateTotal));
        $('#balance').val(accounting.formatNumber(dueTotal - allocateTotal));
    }).focusout(function() { 
        if (!$(this).val()) return;
        $(this).val(parseFloat($(this).val().replace(/,/g, '')).toLocaleString());
    }).focus(function() {
        if (!$('#person').val()) $(this).blur();
    });

    // payments
    $('#payment').select2().change(function() {
        if ($(this).val() == 0) {
            ['amount', 'reference'].forEach(v => $('#'+v).val('').attr('readonly', false));
            ['account', 'payment_mode'].forEach(v => $('#'+v).val('').attr('disabled', false));
            $('#date').datepicker('setDate', new Date()).attr('readonly', false);
            loadInvoice();
        } else {
            $('#person').change();
            const opt = $(this).find(':selected');
            $('#date').datepicker('setDate', new Date(opt.attr('date'))).attr('readonly', true);
            $('#reference').val(opt.attr('reference')).attr('readonly', true);
            $('#account').val(opt.attr('accountId')).attr('disabled', true);
            $('#payment_mode').val(opt.attr('paymentMode')).attr('disabled', true);
            // execute after ajax async call
            const balance = accounting.unformat(opt.attr('amount') - opt.attr('allocateTotal'));
            setTimeout(() => $('#amount').val(balance).keyup().focusout().attr('readonly', true), 100);
        }
    });      

    // invoice row
    function invoiceRow(v, i) {
        const amount = accounting.formatNumber(v.total);
        const paid = accounting.formatNumber(v.amountpaid);
        const balance = accounting.formatNumber(v.total - v.amountpaid);
        return `
            <tr>
                <td class="text-center">${new Date(v.invoiceduedate).toDateString()}</td>
                <td>${v.tid}</td>
                <td class="text-center">${v.notes}</td>
                <td>${v.status}</td>
                <td>${amount}</td>
                <td>${paid}</td>
                <td class="text-center due"><b>${balance}</b></td>
                <td><input type="text" class="form-control paid" name="paid[]"></td>
                <input type="hidden" name="invoice_id[]" value="${v.id}">
            </tr>
        `;
    }

    // compute totals
    function calcTotal() {
        let dueTotal = 0;
        let allocateTotal = 0;
        const lastCount = $('#invoiceTbl tbody tr').length - 1;
        $('#invoiceTbl tbody tr').each(function(i) {
            if (i == lastCount) return;
            const due = accounting.unformat($(this).find('.due').text());
            const paid = accounting.unformat($(this).find('.paid').val());
            dueTotal += due;
            allocateTotal += paid;
        });
        $('#allocate_ttl').val(accounting.formatNumber(allocateTotal));
        $('#balance').val(accounting.formatNumber(dueTotal - allocateTotal));
    }
</script>
