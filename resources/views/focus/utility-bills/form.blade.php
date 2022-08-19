<div class="form-group row">
    <div class="col-2">
        <label for="tid" class="caption">Bill No.</label>
        {{ Form::text('tid', @$utility_bill ? $utility_bill->tid : $tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
    </div>  
    
    <div class="col-3">
        <label for="type">Document Type</label>
        <select name="document_type" id="document_type" class="custom-select">
            <option value="">-- Select Document --</option>
            @foreach (['goods_receive_note', 'kra'] as $val)
                <option value="{{ $val }}">{{ strtoupper(str_replace('_', ' ', $val)) }}</option>
            @endforeach
        </select>
    </div> 
    <div class="col-2">
        <label for="reference">Rerence</label>
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
    <div class="col-5">
        <label for="supplier">Supplier</label>
        <select name="supplier_id" id="supplier" class="custom-select" data-placeholder="Choose Supplier" disabled>
            @foreach ($suppliers as $row)
                <option value="{{ $row->id }}">{{ $row->name }}</option>
            @endforeach
        </select>
    </div> 
    <div class="col-6">
        <label for="note">Note</label>    
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note', 'required']) }}
    </div>                          
</div>

<div class="table-responsive">
    <table class="table tfr my_stripe_single text-center" id="documentsTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>#</th>
                <th>Date</th>
                <th>Description</th>
                <th>Qty</th>
                <th>Rate</th>
                <th>Tax</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody></tbody>  
    </table>
</div>
<div class="row">  
    <div class="col-2 ml-auto">
        <label for="subtotal">Subtotal</label>    
        {{ Form::text('subtotal', null, ['class' => 'form-control', 'id' => 'subtotal', 'readonly']) }}
    </div>                          
</div>
<div class="row">  
    <div class="col-2 ml-auto">
        <label for="tax">Tax</label>    
        {{ Form::text('tax', null, ['class' => 'form-control', 'id' => 'tax', 'readonly']) }}
    </div>                           
</div>
<div class="row">  
    <div class="col-2 ml-auto">
        <label for="total">Total</label>    
        {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total', 'readonly']) }}
    </div>                          
</div>
<div class="row mt-1">                            
    <div class="col-2 ml-auto">  
        {{ Form::submit(@$utility_bill? 'Update Bill' : 'Create Bill', ['class' =>'btn btn-primary btn-lg']) }}
    </div>
</div>

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajaxSetup: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        datepicker: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        goodsReceiveNoteUrl: @json(route('biller.suppliers.goodsreceivenote')),
    };

    const Form = {
        init() {
            $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());
            $('#supplier').select2({allowClear: true}).change(this.supplierChange);
            $('#document_type').change(this.documentTypeChange);
        },

        documentTypeChange() {
            const el = $(this);
            if (el.val() == 'goods_receive_note') {
                $('#supplier').attr('disabled', false);
            } else $('#supplier').attr('disabled', true);
        },

        supplierChange() {
            const el = $(this);
            const supplier_id = el.val();
            $.post(config.goodsReceiveNoteUrl, {supplier_id}, (data) => {
                data = data.map(v => ({
                    date: v.date, 
                    note: `DN-${v.dnote} - ${v.note}`, 
                    qty: 1,
                    rate: v.subtotal,
                    tax: v.tax,
                    total: v.total
                }));
                $('#documentsTbl tbody tr').remove();
                data.forEach((v,i) => $('#documentsTbl tbody').append(Form.documentsRow(v,i)));
                Form.columnTotals();
            });
        },

        documentsRow(v,i) {
            const rate = accounting.formatNumber(v.rate);
            const tax = accounting.formatNumber(v.tax);
            const total = accounting.formatNumber(v.total);

            return `
                <tr>
                    <td>${i+1}</td>
                    <td>${new Date(v.date).toDateString()}</td>
                    <td><input type="text" name="row_note[]" value="${v.note}" id="" class="form-control note" readonly></td>
                    <td><input type="text" name="row_qty[]" value="${parseFloat(v.qty)}"id="" class="form-control qty" readonly></td>
                    <td><input type="text" name="row_subtotal[]" value="${rate}"id="" class="form-control rate" readonly></td>
                    <td><input type="text" name="row_tax[]" value="${tax}"id="" class="form-control tax" readonly></td>
                    <td><input type="text" name="row_total[]" value="${total}"id="" class="form-control total" readonly></td>
                    <input type="hidden" name="row_ref_id[]" value="${v.id}">
                </tr>
            `;
        },

        columnTotals() {
            colSubtotal = 0;
            colTaxTotal = 0;
            colTotal = 0;
            $('#documentsTbl tbody tr').each(function() {
                const row = $(this);
                const subtotal = accounting.unformat(row.find('.rate').val());
                const tax = accounting.unformat(row.find('.tax').val());
                const total = accounting.unformat(row.find('.total').val());
                colSubtotal += subtotal;
                colTaxTotal += tax;
                colTotal += total;
            });
            $('#subtotal').val(accounting.formatNumber(colSubtotal));
            $('#tax').val(accounting.formatNumber(colTaxTotal));
            $('#total').val(accounting.formatNumber(colTotal));            
        },
    }

    $(() => Form.init());
</script>
@endsection
