{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajax: { headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} },
        date: {format: "{{config('core.user_date_format')}}", autoHide: true}, 
        select2: {
            allowClear: true,
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
        },
    };

    const Form = {
        invoicePayment: @json(@$invoice_payment),

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#person').select2(config.select2);

            $('#person').change(this.customerChange);
            $('#payment_type').change(this.paymentTypeChange);
            $('#rel_payment').change(this.relatedPaymentChange);

            $('#invoiceTbl').on('change', '.paid', this.allocationChange);
            $('#amount').keyup(this.amountChange)
                .focusout(this.amountFocusOut)
                .focus(this.amountFocus);

            $('form').submit(this.formSubmit);
            
            // edit mode
            if (this.invoicePayment) {
                const pmt = this.invoicePayment;
                if (pmt.date) $('#date').datepicker('setDate', new Date(pmt.date));
                $('#person').attr('disabled', true);
                $('#payment_type').attr('disabled', true);
                $('#amount').val(accounting.formatNumber(pmt.amount*1));
                $('#account').val(pmt.account_id);
                $('#payment_mode').val(pmt.payment_mode);
                $('#reference').val(pmt.reference);
                $('#rel_payment').attr('disabled', true);
                this.calcTotal();
            } else {
                this.loadUnallocatedPayments();
            }
        },

        formSubmit() {
            // filter unallocated inputs
            $('#invoiceTbl tbody tr').each(function() {
                let allocatedAmount = $(this).find('.paid').val();
                if (accounting.unformat(allocatedAmount) == 0) {
                    $(this).remove();
                } 
            });
            if (Form.invoicePayment && $('#payment_type').val() == 'per_invoice' && !$('#invoiceTbl tbody tr').length) {
                if (!confirm('Allocating zero on line items will reset this payment! Are you sure?')) {
                    event.preventDefault();
                    location.reload();
                }
            }
            // check if payment amount >= allocated amount
            let amount = accounting.unformat($('#amount').val());
            let allocatedTotal = accounting.unformat($('#allocate_ttl').val());
            if (allocatedTotal > amount) {
                event.preventDefault();
                alert('Total Allocated Amount must be less or equal to payment Amount!');
            }
            // enable all disabled elements
            $(this).find('select:disabled').attr('disabled', false);
        },

        invoiceRow(v, i) {
            return `
                <tr>
                    <td class="text-center">${v.invoiceduedate.split('-').reverse().join('-')}</td>
                    <td>${v.tid}</td>
                    <td class="text-center">${v.notes}</td>
                    <td>${v.status}</td>
                    <td>${accounting.formatNumber(v.total)}</td>
                    <td>${accounting.formatNumber(v.amountpaid)}</td>
                    <td class="text-center due"><b>${accounting.formatNumber(v.total - v.amountpaid)}</b></td>
                    <td><input type="text" class="form-control paid" name="paid[]"></td>
                    <input type="hidden" name="invoice_id[]" value="${v.id}">
                </tr>
            `;
        },

        customerChange() {
            $('#amount').val('');
            $('#allocate_ttl').val('');
            $('#balance').val('');
            $('#invoiceTbl tbody tr').remove();
            if ($(this).val()) {
                // fetch invoices
                const url = "{{ route('biller.invoices.client_invoices') }}?customer_id=" + $(this).val();
                $.get(url, data => {
                    data.forEach((v, i) => {
                        $('#invoiceTbl tbody').append(Form.invoiceRow(v, i));
                    });
                });
            }
        },

        loadUnallocatedPayments() {
            $('#rel_payment').attr('disabled', false).change();
            const payments = @json($unallocated_pmts);
            payments.forEach(v => {
                const str = `
                    ${v.date.split('-').reverse().join('-')}: 
                    (${v.payment_type} ${accounting.formatNumber(v.amount - v.allocate_ttl)})
                    ${v.payment_mode} - ${v.reference}
                `;
                
                const option = `
                    <option
                        value=${v.id}
                        amount=${v.amount}
                        allocateTotal=${v.allocate_ttl}
                        accountId=${v.account_id}
                        paymentMode=${v.payment_mode}
                        reference=${v.reference}
                        date=${v.date}
                        >
                        ${str}
                    </option>
                    `;
                $('#rel_payment').append(option);
            });
        },

        paymentTypeChange() {
            $('#amount').val('');
            $('#allocate_ttl').val('');
            $('#balance').val('');
            if ($(this).val() == 'per_invoice') {
                $('#rel_payment').val(0).attr('disabled', false).change();
                $('#person').change();
                const payments = @json($unallocated_pmts);
                if (payments.length) Form.loadUnallocatedPayments();
            } else {
                $('#invoiceTbl tbody tr').remove();
                $('#rel_payment').val(0).attr('disabled', true);
                $('#rel_payment option:not(:first)').remove();
                $('#amount').val('').attr('readonly', false);
                $('#account').val('').attr('disabled', false);
                $('#payment_mode').val('').attr('disabled', false);
                $('#reference').val('').attr('readonly', false);
                $('#allocate_ttl').val('');
                $('#balance').val('');
            }
        },

        allocationChange() {
            const paid = accounting.unformat($(this).val());
            $(this).val(accounting.formatNumber(paid));
            Form.calcTotal();
        },

        amountChange() {
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
        },

        amountFocus() {
            if (!$('#person').val()) $(this).blur();
        },

        amountFocusOut() {
            const amount = accounting.unformat($(this).val());
            if (amount) $(this).val(accounting.formatNumber(amount));
        },

        relatedPaymentChange() {
            if ($(this).val()*1) {
                const opt = $(this).find(':selected');
                $('#date').datepicker('setDate', new Date(opt.attr('date'))).attr('readonly', true);
                $('#reference').val(opt.attr('reference')).attr('readonly', true);
                $('#account').val(opt.attr('accountId')).attr('disabled', true);
                $('#payment_mode').val(opt.attr('paymentMode')).attr('disabled', true);

                
                const unallocated = accounting.unformat(opt.attr('amount') - opt.attr('allocateTotal'));
                $('#amount').val(unallocated).keyup().focusout().attr('readonly', true);
            } else {
                ['amount', 'reference'].forEach(v => $('#'+v).val('').attr('readonly', false).keyup());
                ['account', 'payment_mode'].forEach(v => $('#'+v).val('').attr('disabled', false));
                $('#date').datepicker('setDate', new Date()).attr('readonly', false);
            }
        },

        calcTotal() {
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
        },
    };    

    $(() => Form.init());
</script>
