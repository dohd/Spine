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
                    ${parseFloat(v.amount).toLocaleString()} - ${v.payment_type}`
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
        const paid = parseFloat($(this).val().replace(/,/g, ''));
        $(this).val(paid.toLocaleString());
        calcTotal();
        const amount = parseFloat($('#amount').val().replace(/,/g, ''));
        const allocateTotal = parseFloat($('#allocate_ttl').val().replace(/,/g, ''));
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
        let amount = parseFloat($(this).val().replace(/,/g, ''));
        const lastCount = $('#invoiceTbl tbody tr').length - 1;
        $('#invoiceTbl tbody tr').each(function(i) {
            if (i == lastCount) return;
            const due = parseFloat($(this).find('.due').text().replace(/,/g, ''));
            if (due > amount) $(this).find('.paid').val(amount.toLocaleString());
            else if (amount > due) $(this).find('.paid').val(due.toLocaleString());
            else $(this).find('.paid').val(0);
            const paid = parseFloat($(this).find('.paid').val().replace(/,/g, ''));
            amount -= paid;
            dueTotal += due;
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
            const balance = parseFloat(opt.attr('amount') - opt.attr('allocateTotal'));
            setTimeout(() => $('#amount').val(balance).keyup().focusout().attr('readonly', true), 100);
        }
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
            const due = parseFloat($(this).find('.due').text().replace(/,/g, '')) || 0;
            const paid = parseFloat($(this).find('.paid').val().replace(/,/g, '')) || 0;
            dueTotal += due;
            allocateTotal += paid;
        });
        $('#allocate_ttl').val(parseFloat(allocateTotal.toFixed(2)).toLocaleString());
        $('#balance').val(parseFloat(dueTotal - allocateTotal).toLocaleString());
    }
</script>
