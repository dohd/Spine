<div class="form-group row">
    <div class="col-4">
        <label for="supplier">Supplier</label>
        <select name="supplier_id" id="supplier" class="form-control" data-placeholder="Choose supplier" required>
            @foreach ($suppliers as $row)
                <option value="{{ $row->id }}" {{ @$billpayment && $billpayment->supplier_id == $row->id? 'selected' : '' }}>
                    {{ $row->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-2">
        <label for="tid" class="caption">Payment No.</label>
        {{ Form::text('tid', @$billpayment ? $billpayment->tid : $tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
    </div>  
    <div class="col-2">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
    </div> 
    <div class="col-2">
        <label for="payment_mode">Payment Mode</label>
        <select name="payment_mode" id="payment_mode" class="custom-select">
            @foreach (['eft', 'rtgs','cash', 'mpesa', 'cheque'] as $val)
                <option value="{{ $val }}" {{ @$billpayment->payment_mode == $val? 'selected' : '' }}>{{ strtoupper($val) }}</option>
            @endforeach
        </select>
    </div>  
    <div class="col-2">
        <label for="reference">Reference</label>
        {{ Form::text('reference', null, ['class' => 'form-control', 'id' => 'reference', 'required']) }}
    </div> 
</div> 

<div class="form-group row">  
    <div class="col-2">
        <label for="amount" class="caption">Amount (Ksh.)</label>
            {{ Form::text('amount', null, ['class' => 'form-control', 'id' => 'amount', 'required']) }}
    </div>  
    <div class="col-2">
        <label for="account">Pay From Account</label>
        <select name="account_id" id="account" class="custom-select">                                   
            @foreach ($accounts as $row)
                <option value="{{ $row->id }}" {{ $row->id == @$billpayment->account_id? 'selected' : '' }}>
                    {{ $row->holder }}
                </option>
            @endforeach
        </select>
    </div>  
    <div class="col-8">
        <label for="note">Note</label>    
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note', 'required']) }}
    </div>                          
</div>

<div class="table-responsive">
    <table class="table tfr my_stripe_single text-center" id="documentsTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>Due Date</th>
                <th>Bill No</th>
                <th>Supplier Name</th>
                <th>Note</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Paid</th>
                <th>Outstanding</th>
                <th>Allocate (Ksh.)</th>
            </tr>
        </thead>
        <tbody>   
            @isset ($billpayment)
                @foreach ($billpayment->items as $item)
                    @php
                        $bill = $item->supplier_bill;
                        if (!$bill) continue;
                    @endphp
                    <tr>
                        <td class="text-center">{{ dateFormat($bill->due_date) }}</td>
                        <td>{{ $bill->tid }}</td>
                        <td>{{ $bill->purchase? $bill->purchase->suppliername : $bill->supplier->name }}</td>
                        <td class="text-center">{{ $bill->name }}</td>
                        <td>{{ $bill->status }}</td>
                        <td>{{ numberFormat($bill->total) }}</td>
                        <td>{{ numberFormat($bill->amount_paid) }}</td>
                        <td class="text-center due"><b>{{ numberFormat($bill->total - $bill->amount_paid) }}</b></td>
                        <td><input type="text" class="form-control paid" name="paid[]" value="{{ numberFormat($item->paid) }}" required></td>
                        <input type="hidden" name="bill_id[]" value="{{ $bill->id }}">
                        <input type="hidden" name="id[]" value="{{ $item->id }}">
                    </tr>
                @endforeach
            @endisset      
        </tbody>                
    </table>
</div>
<div class="row">  
    <div class="col-2 ml-auto">
        <label for="balance">Total Balance</label>    
        {{ Form::text('balance', null, ['class' => 'form-control', 'id' => 'balance', 'readonly']) }}
    </div>                          
</div>
<div class="row">  
    <div class="col-2 ml-auto">
        <label for="allocate_ttl">Total Allocated</label>    
        {{ Form::text('allocate_ttl', null, ['class' => 'form-control', 'id' => 'allocate_ttl', 'readonly']) }}
    </div>                          
</div>
<div class="row mt-1">                            
    <div class="col-2 ml-auto">  
        {{ Form::submit(@$billpayment? 'Update Payment' : 'Make Payment', ['class' =>'btn btn-primary btn-lg']) }}
    </div>
</div>

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

        init() {
            $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());
            $('#supplier').select2({allowClear: true}); 

            $('#amount').keyup(this.allocateAmount).focusout(this.amountFocusOut).trigger('focusout');
            $('#documentsTbl').on('focusout', '.paid', this.tablePaidChange);
            this.columnTotals();

            if (this.billPayment) {
                $('#supplier').attr('disabled', true);
            } else $('#supplier').val('').change();  
            $('#supplier').change(this.supplierChange);  
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

        supplierChange() {
            $('#documentsTbl tbody').html('');
            const supplierId = $(this).val();
            if (supplierId) 
                $.post("{{ route('biller.suppliers.bills') }}", {supplier_id: supplierId}, data => {
                    data.forEach((v,i) => $('#documentsTbl tbody').append(Form.billRow(v,i)));
                });
        },

        billRow(v,i) {
            const diff = v.total - v.amount_paid;
            const balance = accounting.formatNumber(diff > 0? diff : 0);
            return `
                <tr>
                    <td class="text-center">${new Date(v.due_date).toDateString()}</td>
                    <td>${v.tid}</td>
                    <td>${v.purchase? v.purchase.suppliername : ''}</td>
                    <td class="text-center">${v.note}</td>
                    <td>${v.status}</td>
                    <td>${accounting.formatNumber(v.total)}</td>
                    <td>${accounting.formatNumber(v.amount_paid)}</td>
                    <td class="text-center due"><b>${balance}</b></td>
                    <td><input type="text" class="form-control paid" name="paid[]" required></td>
                    <input type="hidden" name="bill_id[]" value="${v.id}">
                </tr>
            `;
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
