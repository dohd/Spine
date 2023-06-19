@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Form = {
        billPayment: @json(@$billpayment),
        directPurchaseBill: @json(@$direct_bill),
        unallocatedPmts: @json(@$unallocated_pmts),

        init() {
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('.select2').select2({allowClear: true});

            $('#amount')
                .keyup(this.allocateAmount)
                .focusout(function() { $(this).val(accounting.formatNumber($(this).val())); })
                .trigger('focusout');
                    
            $('#billsTbl').on('focusout', '.paid', this.billAmountChange);
            this.columnTotals();

            // edit mode
            if (this.billPayment.id) {
                const pmt = this.billPayment;
                $('#date').datepicker('setDate', new Date(pmt.date));
                if (pmt.supplier_id) {
                    $('#employee').val('').change().attr({'disabled': true, 'required':false});
                } else if (pmt.employee_id) {
                    $('#supplier').val('').change().attr({'disabled': true, 'required':false});
                }
            }

            $('#supplier').change(this.supplierChange);  
            $('#employee').change(this.employeeChange);     
            $('#payment_type').change(this.paymentTypeChange);
            $('#rel_payment').change(this.unallocatedPmtChange);         
            $('form').submit(this.formSubmit);
            this.paymentFromDirectPurchase();
        },

        formSubmit() {
            // filter unallocated inputs
            $('#billsTbl tbody tr').each(function() {
                let paymentInp = $(this).find('.paid');
                if (accounting.unformat(paymentInp.val()) == 0) {
                    $(this).remove();
                } 
            });
            if (Form.billPayment && $('#payment_type').val() == 'per_invoice' && !$('#billsTbl tbody tr').length) {
                if (!confirm('Allocating zero on line items will reset this payment! Are you sure?')) {
                    event.preventDefault();
                    location.reload();
                }
            }
            // check if payment amount >= allocated amount
            const pmtAmount = accounting.unformat($('#amount').val());
            const allocAmount = accounting.unformat($('#allocate_ttl').val());
            if (pmtAmount != allocAmount && $('#payment_type').val() == 'per_invoice') {
                event.preventDefault();
                alert('Total Allocated Amount must be equal to Payment Amount!');
            }
        },

        unallocatedPmtChange() {
            if ($(this).val()) {
                let data = $(this).children(':selected').attr('data');
                data = JSON.parse(data);
                $('#reference').prop('readonly', true).val(data.reference);
                $('#note').prop('readonly', true).val(data.note);

                const outstanding = (data.amount*1) - (data.allocate_ttl*1);
                $('#amount').val(accounting.formatNumber(outstanding)).keyup();

                $('#payment_type').prop('disabled', true)
                    .after(`<input type="hidden" name="payment_type" value="per_invoice" class="pmt-type" />`);
                $('#account').prop({disabled: true, required: false}).val(data.account_id)
                 .after(`<input type="hidden" name="account_id" value="${data.account_id}" class="account-id" />`);
                $('#payment_mode').prop({disabled: true}).val(data.payment_mode)
                 .after(`<input type="hidden" name="payment_mode" value="${data.payment_mode}" class="pmt-mode"/>`);
            } else {
                ['reference', 'amount', 'note'].map(v => $('#'+v).attr('readonly', false).val(''));
                $('#amount').keyup();

                $('#payment_type').prop('disabled', false);
                $('.pmt-type').remove();

                $('#account').prop({disabled: false, required: true}).val('');
                $('.account-id').remove();
                $('#payment_mode').prop({disabled: false});
                $('.pmt-mode').remove();
            }
        },

        paymentFromDirectPurchase() {
            const bill = this.directPurchaseBill;
            if (bill) {
                const billAmount = bill.amount*1;
                $('#supplier').val(bill.supplier_id).change();
                $('#amount').val(accounting.formatNumber(billAmount));
                const rowUpdater = setInterval(() => {
                    $('#billsTbl tbody tr').each(function() {
                        const itemTid = $(this).find('.bill-no').text();
                        if (itemTid == bill.tid) {
                            $(this).find('.paid').val(billAmount).focusout();
                            clearInterval(rowUpdater);
                        }
                    });
                }, 500);
            }
        },

        billAmountChange() {
            const tr = $(this).parents('tr:first');
            const paid = accounting.unformat($(this).val());
            const due = accounting.unformat(tr.find('.due').text());
            const amount = accounting.unformat(tr.find('.amount').text());

            if (Form.billPayment) {
                if (paid > amount) $(this).val(amount);
            } else {
                if (paid > due) $(this).val(due);
            }
            
            Form.columnTotals();
        },

        supplierChange() {
            $('#billsTbl tbody tr').remove();
            const supplier_id = this.value;
            if (supplier_id) {
                $('#employee').attr({required: false, disabled: true});
                // filter supplier unallocated payments
                $('#rel_payment option:not(:first)').each(function() {
                    if ($(this).attr('supplier_id') == supplier_id) {
                        $(this).removeClass('d-none');
                    } else  {
                        $(this).addClass('d-none');
                    }
                });
                // fetch bills
                $.post("{{ route('biller.suppliers.bills') }}", {supplier_id}, data => {
                    data.forEach((v,i) => $('#billsTbl tbody').append(Form.billRow(v,i)));
                });
            } else {
                $('#employee').attr({required: true, disabled: false});
            }
        },

        employeeChange() {
            $('#billsTbl tbody tr').remove();
            const employee_id = this.value;
            if (employee_id) {
                $('#supplier').attr({required: false, disabled: true});
                // fetch bills
                $.post("{{ route('biller.utility-bills.employee_bills') }}", {employee_id}, data => {
                    data.forEach((v,i) => $('#billsTbl tbody').append(Form.billRow(v,i)));
                });
            } else {
                $('#supplier').attr({required: true, disabled: false});
            }
        },

        paymentTypeChange() {
            switch (this.value) {
                case 'per_invoice':
                    if ($('#supplier').val()) $('#supplier').change();
                    else if ($('#employee').val()) $('#employee').change();
                    $('#rel_payment').val('').attr('disabled', false);
                    break;
                case 'on_account':
                    $('#billsTbl tbody tr').remove();
                    $('#rel_payment').val('').attr('disabled', true);
                    break;
                case 'advance_payment':
                    $('#billsTbl tbody tr').remove();
                    $('#rel_payment').val('').attr('disabled', true);
                    break;
            }
            Form.columnTotals();
        },

        billRow(v,i) {
            return `
                <tr>
                    <td class="text-center">${v.due_date.split('-').reverse().join('-')}</td>
                    <td class="bill-no">${v.tid}</td>
                    <td>${v.suppliername? v.suppliername : v.supplier.name}</td>
                    <td class="text-center">${v.note}</td>
                    <td>${v.status}</td>
                    <td class="amount">${accounting.formatNumber(v.total)}</td>
                    <td>${accounting.formatNumber(v.amount_paid)}</td>
                    <td class="text-center due"><b>${accounting.formatNumber(v.total - v.amount_paid)}</b></td>
                    <td><input type="text" class="form-control paid" name="paid[]" required></td>
                    <input type="hidden" name="bill_id[]" value="${v.id}" class="bill-id">
                </tr>
            `;
        },

        allocateAmount() {
            let dueTotal = 0;
            let allocateTotal = 0;
            let amount = accounting.unformat($(this).val());
            $('#billsTbl tbody tr').each(function() {
                const due = accounting.unformat($(this).find('.due').text());
                const paidInput = $(this).find('.paid');
                if (due > amount) paidInput.val(accounting.formatNumber(amount));
                else if (amount >= due) paidInput.val(accounting.formatNumber(due));
                else paidInput.val(accounting.formatNumber(0));
                
                const paid = accounting.unformat(paidInput.val());
                amount -= paid;
                dueTotal += due;
                allocateTotal += paid;
            });
            $('#allocate_ttl').val(accounting.formatNumber(allocateTotal));
            $('#balance').val(accounting.formatNumber(dueTotal - allocateTotal));
            const amount2 = accounting.unformat($(this).val());
            $('#unallocate_ttl').val(accounting.formatNumber(amount2 - allocateTotal));
        },

        columnTotals() {
            let dueTotal = 0;
            let allocateTotal = 0;
            $('#billsTbl tbody tr').each(function(i) {
                const due = accounting.unformat($(this).find('.due').text());
                const paid = accounting.unformat($(this).find('.paid').val());
                $(this).find('.due').text(accounting.formatNumber(due));
                $(this).find('.paid').val(accounting.formatNumber(paid));
                dueTotal += due;
                allocateTotal += paid;
            });
            $('#balance').val(accounting.formatNumber(dueTotal - allocateTotal));
            $('#allocate_ttl').val(accounting.formatNumber(allocateTotal));
            const amount = accounting.unformat($('#amount').val());
            $('#unallocate_ttl').val(accounting.formatNumber(amount - allocateTotal));
        },
    }

    $(() => Form.init());
</script>
@endsection
 