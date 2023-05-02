<div class="form-group row">
    <div class="col-2">
        <label for="tid" class="caption">Bill No.</label>
        {{ Form::text('tid', @$utility_bill ? $utility_bill->tid : $tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
    </div>  
    <div class="col-2">
        <label for="type">Parent Document</label>
        <select name="document_type" id="document_type" class="custom-select">
            @foreach (['goods_receive_note', 'kra_bill'] as $val)
                <option value="{{ $val }}" {{ @$utility_bill && $utility_bill->document_type == $val? 'selected' : '' }}>
                    {{ strtoupper(str_replace('_', ' ', $val)) }}
                </option>
            @endforeach
        </select>
    </div> 
    <div class="col-2">
        <label for="reference_type">Reference Type</label>
        <select name="reference_type" id="reference_type" class="custom-select">
            @foreach (['invoice', 'receipt', 'voucher'] as $val)
                <option value="{{ $val }}" {{ @$utility_bill && $utility_bill->reference_type == $val? 'selected' : '' }}>
                    {{ ucfirst($val) }}
                </option>
            @endforeach
        </select>
    </div> 
    <div class="col-2">
        <label for="reference">Reference No</label>
        {{ Form::text('reference', null, ['class' => 'form-control', 'id' => 'reference']) }}
    </div> 
    <div class="col-2">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
    </div> 
    <div class="col-2">
        <label for="date">Due Date</label>
        {{ Form::text('due_date', null, ['class' => 'form-control datepicker', 'id' => 'due_date']) }}
    </div> 
</div> 

<div class="form-group row">  
    <div class="col-4">
        <label for="supplier">Supplier</label>
        <select name="supplier_id" id="supplier" class="custom-select" data-placeholder="Choose Supplier">
            @foreach ($suppliers as $row)
                <option value="{{ $row->id }}" {{ @$utility_bill && $utility_bill->supplier_id == $row->id? 'selected' : '' }}>
                    {{ $row->name }}
                </option>
            @endforeach
        </select>
    </div> 
    <div class="col-6">
        <label for="note">Note</label>    
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note', 'required']) }}
    </div>   
    <div class="col-2">
        <label for="tax_rate">TAX %</label>
        <select name="tax_rate" id="tax_rate" class="custom-select">
            @foreach ([0, 8, 16] as $val)
                <option value="{{ $val }}">{{ $val? $val . '% VAT' : 'OFF' }}</option>
            @endforeach
        </select>
    </div>                        
</div>

<div class="table-responsive">
    <table class="table tfr my_stripe_single text-center" id="documentsTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th width="5%">#</th>
                <th>Date</th>
                <th width="25%">Item Description</th>
                <th width="10%">Qty</th>
                <th>Rate</th>
                <th>Tax</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @isset($utility_bill)
                @foreach ($utility_bill->items as $k => $item)
                    <tr>
                        <td>{{ $k+1 }}</td>
                        <td>{{ dateFormat($item->date) }}</td>
                        <td><input type="text" name="item_note[]" value="{{ $item->note }}"  class="form-control note" readonly></td>
                        <td><input type="text" name="item_qty[]" value="{{ +$item->qty }}" class="form-control qty" readonly></td>
                        <td><input type="text" name="item_subtotal[]" value="{{ numberFormat($item->subtotal) }}" class="form-control rate" readonly></td>
                        <td><input type="text" name="item_tax[]" value="{{ numberFormat($item->tax) }}" class="form-control tax" readonly></td>
                        <td><input type="text" name="item_total[]" value="{{ numberFormat($item->total) }}" class="form-control total" readonly></td>
                        <td><a href="#" class="btn btn-link pt-0 delete"><i class="fa fa-trash fa-2x text-danger"></i></a></td>
                        <input type="hidden" name="item_ref_id[]" value="{{ $item->ref_id }}">
                        <input type="hidden" name="id[]" value="{{ $item->id }}">
                    </tr>
                @endforeach
            @endisset
        </tbody>  
    </table>
</div>

<div class="row form-group">  
    <div class="col-10 ml-auto text-right pt-1">
        <label for="subtotal">Subtotal</label>    
    </div>      
    <div class="col-2 mr-auto">
        {{ Form::text('subtotal', null, ['class' => 'form-control', 'id' => 'subtotal', 'readonly']) }}
    </div>                      
</div>
<div class="row form-group">  
    <div class="col-10 ml-auto text-right pt-1">
        <label for="tax">Tax</label>    
    </div>   
    <div class="col-2 mr-auto">
        {{ Form::text('tax', null, ['class' => 'form-control', 'id' => 'tax', 'readonly']) }}
    </div>                          
</div>
<div class="row form-group">  
    <div class="col-10 ml-auto text-right pt-1">
        <label for="total">Total</label>    
    </div>   
    <div class="col-2 mr-auto">
        {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total', 'readonly']) }}
    </div>                          
</div>

<div class="row mt-1">                            
    <div class="col-2 ml-auto">  
        {{ Form::submit(@$utility_bill? 'Update' : 'Generate', ['class' =>'btn btn-primary btn-lg']) }}
    </div>
</div>

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Form = {
        utilityBill: @json(@$utility_bill),

        init() {
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#supplier').select2({allowClear: true});

            $('#tax_rate').change(() => this.columnTotals());
            this.columnTotals();

            if (this.utilityBill) {
                $('#supplier').attr('disabled', true);
            } else {
                $('#supplier').val('').change();
            }
            $('#supplier').change(this.supplierChange);
            $('#documentsTbl').on('click', '.delete', this.deleteRow);
        },

        deleteRow() {
            const row = $(this).parents('tr');
            row.remove();
            if (!$('table tbody tr:first').length) 
                $('#supplier').val('').change();

            Form.columnTotals();
        },

        supplierChange() {
            $('#documentsTbl tbody tr').remove();
            const supplier_id = $(this).val();
            if (!supplier_id) return;

            // fetch supplier grn items
            const grnUrl = "{{ route('biller.utility-bills.goods_receive_note') }}";
            $.post(grnUrl, {supplier_id}, data => {
                data.forEach((v,i) => $('#documentsTbl tbody').append(Form.billItemRow(v,i)));
                Form.columnTotals();
            });
        },

        billItemRow(v,i) {
            const rate = accounting.formatNumber(v.rate);
            const tax = accounting.formatNumber(v.tax);
            let total = accounting.formatNumber(v.total);
            if (v.tax > 0) total = accounting.formatNumber(v.total / (1 + v.tax * 0.01));

            return `
                <tr>
                    <td>${i+1}</td>
                    <td>${new Date(v.date).toDateString()}</td>
                    <td>${v.note}</td>
                    <td><input type="text" name="item_qty[]" value="${parseFloat(v.qty)}" class="form-control qty"></td>
                    <td><input type="text" name="item_subtotal[]" value="${rate}" class="form-control rate" readonly></td>
                    <td><input type="text" name="item_tax[]" value="${tax}" class="form-control tax" readonly></td>
                    <td><input type="text" name="item_total[]" value="${total}" class="form-control total" readonly></td>
                    <td><a href="#" class="btn btn-link pt-0 delete"><i class="fa fa-trash fa-2x text-danger"></i></a></td>
                    <input type="hidden" name="item_ref_id[]" value="${v.id}">
                    <input type="hidden" name="item_note[]" value="${v.note}"  class="note">
                </tr>
            `;
        },

        columnTotals() {
            colSubtotal = 0;
            colTotal = 0;
            $('#documentsTbl tbody tr').each(function() {
                const row = $(this);
                const rate = accounting.unformat(row.find('.rate').val());
                const qty = accounting.unformat(row.find('.qty').val());

                const amount = qty * rate * (1 + $('#tax_rate').val() * 0.01);
                colSubtotal += rate * qty;
                colTotal += amount;

                row.find('.tax').val(accounting.formatNumber(amount - qty * rate));
                row.find('.total').val(accounting.formatNumber(amount));
            });
            $('#subtotal').val(accounting.formatNumber(colSubtotal));
            $('#tax').val(accounting.formatNumber(colTotal - colSubtotal));
            $('#total').val(accounting.formatNumber(colTotal));            
        },
    }

    $(() => Form.init());
</script>
@endsection
