<div class="form-group row">
    <div class="col-4">
        <label for="supplier">Supplier</label>
        <select name="supplier_id" id="supplier" class="form-control"></select>
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
                <option value="{{ $val }}">{{ strtoupper($val) }}</option>
            @endforeach
        </select>
    </div>  
    <div class="col-2">
        <label for="reference">Reference</label>
        {{ Form::text('reference', null, ['class' => 'form-control', 'id' => 'reference']) }}
    </div> 
</div> 

<div class="form-group row">  
    <div class="col-2">
        <label for="amount" class="caption">Amount (Ksh.)</label>
            {{ Form::text('amount', null, ['class' => 'form-control', 'id' => 'amount', 'required']) }}
    </div>  
    <div class="col-2">
        <label for="account">From Account (Credit)</label>
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
        {{ Form::submit(@$payment? 'Update Payment' : 'Make Payment', ['class' =>'btn btn-primary btn-lg']) }}
    </div>
</div>

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajaxSetup: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        datepicker: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        supplierSelect2: {
            placeholder: 'Choose Supplier',
            allowClear: true,
            ajax: {
                url: "{{ route('biller.suppliers.select') }}",
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({q: term, keyword: term}),
                processResults: data => {
                    return {results: data.map(v => ({id: v.id, text: v.name + ' : ' + v.email}))}; 
                }
            }
        },
        fetchSupplierBill: (supplier_id) => {
            return $.ajax({
                url: "{{ route('biller.suppliers.bills') }}",
                type: 'POST',
                quietMillis: 50,
                data: {supplier_id},
            });
        }
    };

    const Form = {
        init() {
            $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());
            $('#supplier').select2(config.supplierSelect2).change(this.supplierChange);  
            $('#amount').keyup(this.allocateAmount).focusout(this.amountFocusOut).focus(this.amountFocus);
            $('#documentsTbl').on('change', '.paid', () => Form.columnTotals());
        },

        amountFocusOut() {
            const el = $(this);
            el.val(accounting.formatNumber(el.val()));
        },

        amountFocus() {
            if (!$('#supplier').val()) $(this).blur();
        },

        allocateAmount() {
            const el = $(this);
            let amount = accounting.unformat(el.val());
            let dueTotal = 0;
            let allocateTotal = 0;
            $('#documentsTbl tbody tr').each(function() {
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

        supplierChange() {
            const el = $(this);
            $('#documentsTbl tbody').html('');
            if (!el.val()) return;
            config.fetchSupplierBill(el.val()).done(data => {
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
                    <td><input type="text" class="form-control paid" name="paid[]"></td>
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
