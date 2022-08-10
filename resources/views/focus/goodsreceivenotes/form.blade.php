<div class="form-group row">
    <div class="col-5">
        <label for="supplier">Search Supplier</label>
        <select id="supplier" name="supplier_id" class="form-control" data-placeholder="Search Supplier" required>
            @isset ($payment)
                <option value="{{ $payment->customer_id }}">{{ $payment->customer->company }}</option>
            @endisset
        </select>
    </div>
    <div class="col-5">
        <label for="purchaseorder" class="caption">LPO</label>
        <select name="purchaseorder_id" id="purchaseorder" class="custom-select">
            
        </select>
    </div> 
    <div class="col-2">
        <label for="tid" class="caption">GRN No.</label>
        {{ Form::text('tid', @$goodsreceivenote ? $goodsreceivenote->tid : $tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
    </div>  
</div> 

<div class="form-group row">  
    <div class="col-6">
        <label for="note">Note</label>    
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note']) }}
    </div>
    <div class="col-2">
        <label for="date" class="caption">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
    </div>         
    <div class="col-2">
        <label for="dnote" class="caption">DNote No.</label>
        {{ Form::text('dnote', null, ['class' => 'form-control', 'id' => 'dnote', 'required']) }}
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
        <tbody>   
            @isset ($payment)
                @foreach ($payment->items as $row)
                    @php
                        $invoice = $row->invoice;
                        if (!$invoice) continue;
                    @endphp
                    <tr>
                        <td>{{ dateFormat($invoice->invoiceduedate) }}</td>
                        <td>{{ gen4tid('Inv-', $invoice->tid) }}</td>
                        <td>{{ $invoice->notes }}</td>
                        <td>{{ $invoice->status }}</td>
                        <td class="inv-amount">{{ numberFormat($invoice->total) }}</td>
                        <td>{{ numberFormat($invoice->amountpaid) }}</td>
                        <td class="due"><b>{{ numberFormat($invoice->total - $invoice->amountpaid) }}<b></td>
                        <td><input type="text" class="form-control paid" name="paid[]" value="{{ numberFormat($row->paid) }}"></td>
                        <input type="hidden" name="id[]" value="{{ $row->id }}">
                    </tr>
                @endforeach
            @endisset
        </tbody>                
    </table>
</div>
<div class="form-group row">                            
    <div class="col-12">  
        {{ Form::submit(@$payment? 'Update Payment' : 'Receive Goods', ['class' =>'btn btn-primary btn-lg float-right mr-3']) }}
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
                    <td><input name="qty[]" id="qty" class="form-control"></td>    
                    <input type="hidden" name="purchaseorder_item_id[]" value="${v.id}">
                    <input type="hidden" name="item_id[]" value="${v.item_id}">
                </tr>
            `;
        },
    }

    $(() => Form.init());
</script>
@endsection
