@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajaxSetup: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        datepicker: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Form = {
        billPayment: @json(@$billpayment),
        directBill: @json(@$direct_bill),
        unallocatedPmts: @json(@$unallocated_pmts),

        init() {
            $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());
            $('#supplier').select2({allowClear: true}); 
            $('#employee').select2({allowClear: true});

            $('#amount').keyup(this.allocateAmount).focusout(this.amountFocusOut).trigger('focusout');
            $('#documentsTbl').on('focusout', '.paid', this.tablePaidChange);
            this.columnTotals();

            if (this.billPayment) {
                // edit mode
                $('#supplier').attr('disabled', true);
                if (!this.billPayment.employee_id)
                    $('#employee').val('').change().attr('disabled', true);
                if (!this.billPayment.supplier_id)
                    $('#supplier').val('').change();
            } else {
                // create mode
                $('#supplier').val('').change();  
                $('#employee').val('').change();  
            }
            $('#supplier').change(this.supplierChange);  
            $('#employee').change(this.employeeChange);     
            $('#payment_type').change(this.paymentTypeChange);
            $('#rel_payment').change(this.relatedPaymentChange);         
            this.handleDirectPayment();

            $('form').submit(this.formSubmit);
        },

        formSubmit() {
            // filter unallocated inputs
            $('#documentsTbl tbody tr').each(function() {
                let payment = $(this).find('.paid');
                let bill = $(this).find('.bill-id');
                if (accounting.unformat(payment.val()) == 0) {
                    payment.attr('disabled', true);
                    bill.attr('disabled', true);
                } 
            });
        },

        relatedPaymentChange() {
            if ($(this).val()) {
                let data = $(this).children(':selected').attr('data');
                data = JSON.parse(data);
                $('#reference').prop('readonly', true).val(data.reference);
                $('#note').prop('readonly', true).val(data.note);

                const balance = parseFloat(data.amount) - parseFloat(data.allocate_ttl);
                $('#amount').prop('readonly', true).val(accounting.formatNumber(balance)).keyup();

                $('#payment_type').prop('disabled', true)
                    .after(`<input type="hidden" name="payment_type" value="per_invoice" class="pmt-type" />`);
                $('#account').prop({disabled: true, required: false}).val(data.account_id)
                 .after(`<input type="hidden" name="account_id" value="${data.account_id}" class="account-id" />`);
                $('#payment_mode').prop({disabled: true}).val(data.payment_mode)
                 .after(`<input type="hidden" name="payment_mode" value="${data.payment_mode}" class="pmt-mode"/>`);
            } else {
                $('#reference').prop('readonly', false).val('');
                $('#amount').prop('readonly', false).val('').keyup();
                $('#note').prop('readonly', false).val('');

                $('#payment_type').prop('disabled', false);
                $('.pmt-type').remove();

                $('#account').prop({disabled: false, required: true}).val('');
                $('.account-id').remove();
                $('#payment_mode').prop({disabled: false});
                $('.pmt-mode').remove();
            }
        },

        paymentTypeChange() {
            const type = $(this).val();
            if (type == 'per_invoice') {
                if ($('#supplier').val()) $('#supplier').change();
                else if ($('#employee').val()) $('#employee').change();
            } else if (type == 'on_account') {
                $('#documentsTbl tbody').html('');
            } else if (type == 'advance_payment') {
                $('#documentsTbl tbody').html('');
            }
            Form.columnTotals();
        },

        handleDirectPayment() {
            const bill = this.directBill;
            if (!bill) return;
            const amount = parseFloat(bill.amount);
            $('#amount').val(accounting.formatNumber(amount));
            $('#supplier').val(bill.supplier_id).change();
            setTimeout(() => {
                $('#documentsTbl tbody tr').each(function() {
                    const billNum = $(this).find('.bill-no').text();
                    if (billNum == bill.tid) {
                        $(this).find('.paid').val(amount).focusout();
                    }
                });
            }, 500);
        },

        amountFocusOut() {
            $(this).val(accounting.formatNumber($(this).val()));
        },

        tablePaidChange() {
            const tr = $(this).parents('tr:first');
            const paid = accounting.unformat($(this).val());
            const due = accounting.unformat(tr.find('.due').text());
            if (paid > due) $(this).val(due);
            Form.columnTotals();
        },

        supplierChange() {
            const supplier_id = $(this).val();
            $('#documentsTbl tbody').html('');
            $('#employee').attr({required: true, disabled: false});

            // filter supplier unallocated payments
            $('#rel_payment option:not(:first)').each(function() {
                if ($(this).attr('supplier_id') == supplier_id) {
                    $(this).removeClass('d-none');
                } else  $(this).addClass('d-none');
            });
            
            // fetch bills
            if (supplier_id) {
                $('#employee').attr({required: false, disabled: true});
                $.post("{{ route('biller.suppliers.bills') }}", {supplier_id}, data => {
                    data.forEach((v,i) => $('#documentsTbl tbody').append(Form.billRow(v,i)));
                });
            }
        },

        employeeChange() {
            const employee_id = $(this).val();
            $('#documentsTbl tbody').html('');
            $('#supplier').attr({required: true, disabled: false});
            if (!employee_id) return; 
            
            $('#supplier').attr({required: false, disabled: true});
            $.post("{{ route('biller.utility-bills.employee_bills') }}", {employee_id}, data => {
                data.forEach((v,i) => $('#documentsTbl tbody').append(Form.billRow(v,i)));
            });
        },

        billRow(v,i) {
            const diff = v.total - v.amount_paid;
            const balance = accounting.formatNumber(diff > 0? diff : 0);
            return `
                <tr>
                    <td class="text-center">${v.due_date.split('-').reverse().join('-')}</td>
                    <td class="bill-no">${v.tid}</td>
                    <td>${v.suppliername? v.suppliername : v.supplier.name}</td>
                    <td class="text-center">${v.note}</td>
                    <td>${v.status}</td>
                    <td>${accounting.formatNumber(v.total)}</td>
                    <td>${accounting.formatNumber(v.amount_paid)}</td>
                    <td class="text-center due"><b>${balance}</b></td>
                    <td><input type="text" class="form-control paid" name="paid[]" required></td>
                    <input type="hidden" name="bill_id[]" value="${v.id}" class="bill-id">
                </tr>
            `;
        },

        allocateAmount() {
            let dueTotal = 0;
            let allocateTotal = 0;
            let amount = accounting.unformat($(this).val());
            $('#documentsTbl tbody tr').each(function() {
                const due = accounting.unformat($(this).find('.due').text());
                const paidInput = $(this).find('.paid');
                if (due > amount) paidInput.val(accounting.formatNumber(amount));
                else if (amount > due) paidInput.val(accounting.formatNumber(due));
                else paidInput.val(accounting.formatNumber(due));

                const paid = accounting.unformat(paidInput.val());
                amount -= paid;
                dueTotal += due;
                allocateTotal += paid;
            });
            $('#allocate_ttl').val(accounting.formatNumber(allocateTotal));
            $('#balance').val(accounting.formatNumber(dueTotal - allocateTotal));
        },

        columnTotals() {
            let dueTotal = 0;
            let allocateTotal = 0;
            $('#documentsTbl tbody tr').each(function(i) {
                const due = accounting.unformat($(this).find('.due').text());
                const paid = accounting.unformat($(this).find('.paid').val());
                dueTotal += due;
                allocateTotal += paid;
                $(this).find('.due').text(accounting.formatNumber(due));
                $(this).find('.paid').val(accounting.formatNumber(paid));
            });
            $('#allocate_ttl').val(accounting.formatNumber(allocateTotal));
            $('#balance').val(accounting.formatNumber(dueTotal - allocateTotal));
        },
    }

    $(() => Form.init());
</script>
@endsection
 