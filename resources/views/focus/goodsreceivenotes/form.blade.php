<div class="form-group row">
    <div class="col-6">
        <label for="supplier">Search Supplier</label>
        <select id="supplier" name="supplier_id" class="form-control" data-placeholder="Choose Supplier" required></select>
    </div>
    <div class="col-4">
        <label for="purchaseorder" class="caption">Purchase Order</label>
        <select name="purchaseorder_id" id="purchaseorder" class="custom-select"></select>
    </div> 
    <div class="col-2">
        <label for="tid" class="caption">GRN No.</label>
        {{ Form::text('tid', @$goodsreceivenote ? $goodsreceivenote->tid : $tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
    </div> 
    
</div> 

<div class="form-group row">  
    <div class="col-2">
        <label for="date" class="caption">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
    </div>   
    <div class="col-2">
        <label for="dnote" class="caption">DNote No.</label>
        {{ Form::text('dnote', null, ['class' => 'form-control', 'id' => 'dnote', 'required']) }}
    </div>  
    <div class="col-2">
        <label for="receive_status" class="caption">Invoice Status</label>
        <select name="invoice_status" id="invoice_status" class="custom-select">
            @foreach (['without_invoice', 'with_invoice'] as $val)
                <option value="{{ $val }}">{{ ucfirst(str_replace('_', ' ', $val)) }}</option>
            @endforeach
        </select>
    </div>    
    <div class="col-2">
        <label for="invoice" class="caption">Invoice No.</label>
        {{ Form::text('invoice_no', null, ['class' => 'form-control', 'id' => 'invoice_no', 'disabled']) }}
    </div>  
    <div class="col-2">
        <label for="tax" class="caption">TAX %</label>
        <select name="tax_rate" id="tax_rate" class="custom-select">
            @foreach ([0, 16, 8] as $val)
                <option value="{{ $val }}">{{ $val? $val . '% VAT' : 'OFF' }}</option>
            @endforeach
        </select>
    </div>  
</div>

<div class="form-group row">
    <div class="col-6">
        <label for="note">Note</label>    
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note']) }}
    </div>
</div>

<div class="table-responsive">
    <table class="table tfr my_stripe_single text-center" id="productTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>#</th>
                <th>Product Description</th>
                <th>UoM</th>
                <th>Qty Ordered</th>
                <th>Qty Received</th>
                <th>Qty Due</th>
                <th width="12%">Qty</th>
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
        {{ Form::submit(@$payment? 'Update Payment' : 'Receive Goods', ['class' =>'btn btn-primary btn-lg']) }}
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
        fetchLpo: (supplier_id) => {
            return $.ajax({
                url: "{{ route('biller.suppliers.purchaseorders') }}",
                type: 'POST',
                quietMillis: 50,
                data: {supplier_id},
            });
        },
        fetchLpoGoods: (purchaseorder_id) => {
            return $.ajax({
                url: "{{ route('biller.purchaseorders.goods') }}",
                type: 'POST',
                quietMillis: 50,
                data: {purchaseorder_id},
            });
        }
    };

    const Form = {
        init() {
            $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());
            $('#supplier').select2(config.supplierSelect2).change(this.supplierChange);
            $('#purchaseorder').change(this.purchaseorderChange);
            $('#tax_rate').change(() => Form.columnTotals());
            $('#productTbl').on('keyup', '.qty', () => Form.columnTotals());
            $('#invoice_status').change(this.invoiceStatusChange);
        },

        invoiceStatusChange() {
            const el = $(this);
            if (el.val() == 'with_invoice') $('#invoice_no').val('').attr('disabled', false);
            else $('#invoice_no').val('').attr('disabled', true);
        },

        supplierChange() {
            const el = $(this);
            $('#purchaseorder').html('');
            $('#productTbl tbody').html('');
            if (!el.val()) return;
            config.fetchLpo(el.val()).done(data => {
                data.forEach(v => {
                    $('#purchaseorder').append(`
                        <option value="${v.id}">LPO-${v.tid} - ${v.note}</option>
                    `);
                });
                $('#purchaseorder').change();
            });
        },

        purchaseorderChange() {
            const el = $(this);
            $('#productTbl tbody').html('');
            if (!el.val()) return;
            config.fetchLpoGoods(el.val()).done(data => {
                console.log(data)
                data.forEach((v,i) => $('#productTbl tbody').append(Form.productRow(v,i)));
            });
        },

        productRow(v,i) {
            const qty = accounting.formatNumber(v.qty);
            const received = accounting.formatNumber(v.qty_received);
            const due = accounting.formatNumber(v.qty - v.qty_received);
            return `
                <tr>
                    <td>${i+1}</td>    
                    <td>${v.description}</td>    
                    <td>${v.uom}</td>    
                    <td>${qty}</td>    
                    <td>${received}</td>    
                    <td>${due}</td>    
                    <td><input name="qty[]" id="qty" class="form-control qty"></td>    
                    <input type="hidden" name="purchaseorder_item_id[]" value="${v.id}">
                    <input type="hidden" name="rate[]" value="${parseFloat(v.rate)}" class="rate">
                    <input type="hidden" name="item_id[]" value="${v.item_id}">
                </tr>
            `;
        },

        columnTotals() {
            subtotal = 0;
            total = 0;
            const tax_rate = 1 + $('#tax_rate').val() / 100;
            $('#productTbl tbody tr').each(function() {
                const row = $(this);
                const qty = accounting.unformat(row.find('.qty').val());
                const rate = accounting.unformat(row.find('.rate').val());
                subtotal += qty * rate;
                total += qty * rate * tax_rate;
            });
            $('#subtotal').val(accounting.formatNumber(subtotal));
            $('#tax').val(accounting.formatNumber(total - subtotal));
            $('#total').val(accounting.formatNumber(total));
        },
    }

    $(() => Form.init());
</script>
@endsection
