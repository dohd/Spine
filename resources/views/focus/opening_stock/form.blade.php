<div class="form-group row">
    <div class="col-2">
        <label for="tid">Transaction No</label>
        {{ Form::text('tid', @$opening_stock? $opening_stock->tid : $tid+1, ['class' => 'form-control', 'readonly']) }}
    </div>
    <div class="col-2">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
    </div>
    <div class="col-8">
        <label for="note">Note</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note', 'required']) }}
    </div>
</div>

<legend>Warehouse Products</legend>
<hr>
<div class="form-group row">
    <div class="col-3">
        <select name="warehouse_id" id="warehouse" class="custom-select">
            <option value="">-- select warehouse --</option>
            @foreach ($warehouses as $row)                
                <option value="{{ $row->id }}" {{ @$opening_stock->warehouse_id == $row->id? 'selected' : '' }}>
                    {{ $row->title }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="table-responsive">
    <table id="productsTbl" class="table table-sm tfr my_stripe_single text-center">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>#</th>
                <th>Description</th>
                <th width="10%">Base Unit</th>
                <th width="16%">Purchase Price</th>
                <th width="12%">Unit Qty</th>
                <th width="16%">Amount</th>
            </tr>
        </thead>
        <tbody>
            @isset($opening_stock)
                @foreach ($opening_stock->items as $i => $item)                   
                    <tr>
                        <td><span id="index-${i}" class="index">{{ $i+1 }}</span></td>
                        <td><span id="prodname-${i}" class="prod-name">{{ $item->productvariation->name }}</span></td>                    
                        <td><span id="unit-${i}" class="unit">{{ $item->product->unit? $item->product->unit->code : ''}}</span></td>
                        <td><input type="text" id="buyprice-${i}" name="purchase_price[]" value="{{ numberFormat($item->purchase_price) }}" class="form-control buy-price"></td>
                        <td><input type="text" id="qty-${i}" name="qty[]" value="{{ +$item->qty }}" class="form-control qty"></td>
                        <td><input type="text" id="amount-${i}" name="amount[]" value="{{ numberFormat($item->amount) }}" class="form-control amount" readonly></td>
                        <input type="hidden" name="product_id[]" id="prodid-${i}" value="${v.id}" class="prod-id">
                        <input type="hidden" name="parent_id[]" id="parentid-${i}" value="${v.parent_id}" class="parent-id">
                    </tr>
                @endforeach       
            @endisset
        </tbody>
    </table>
</div>

<div class="form-group row">
    <div class="col-2 ml-auto">
        <label for="total">Total Amount</label>
        {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total','readonly']) }}
    </div>
</div>
<div class="row">
    <div class="col-2 ml-auto">
        {{ Form::submit('Generate', ['class' => 'btn btn-primary btn-lg block']) }}
    </div>
</div>

@section('extra-scripts')
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        warehouseId: '',
        openingStock: @json(@$opening_stock),

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());

            $('#warehouse').change(this.warehouseChange);
            $('#productsTbl').on('change', '.qty, .buy-price', this.tableChange);

            if (this.openingStock.id) {
                this.calcTotals();
            }
        },

        tableChange() {
            const row = $(this).parents('tr');
            const buyPrice = accounting.unformat(row.find('.buy-price').val());
            const qty = accounting.unformat(row.find('.qty').val());
            const amount = qty * buyPrice;

            row.find('.qty').val(accounting.formatNumber(qty));
            row.find('.buy-price').val(accounting.formatNumber(buyPrice));
            row.find('.amount').val(accounting.formatNumber(amount));
            Index.calcTotals();
        },

        warehouseChange() {
            Index.warehouseId = $(this).val();
            Index.fetchProductVariation();
        },

        fetchProductVariation() {
            $('#productsTbl tbody').html('');
            if (!this.warehouseId) return;

            const url = "{{ route('biller.opening_stock.product_variation') }}";
            $.post(url, {warehouse_id: this.warehouseId}, (data) => {
                data.forEach((v, i) => $('#productsTbl tbody').append(this.loadProductVariation(v, i)));
            });            
        },

        loadProductVariation(v, i) {
            return `
                <tr>
                    <td><span id="index-${i}" class="index">${i+1}</span></td>
                    <td><span id="prodname-${i}" class="prod-name">${v.name}</span></td>                    
                    <td><span id="unit-${i}" class="unit">${v.unit}</span></td>
                    <td><input type="text" id="buyprice-${i}" name="purchase_price[]" class="form-control buy-price"></td>
                    <td><input type="text" id="qty-${i}" name="qty[]" class="form-control qty"></td>
                    <td><input type="text" id="amount-${i}" name="amount[]" value="0.00" class="form-control amount" readonly></td>
                    <input type="hidden" name="product_id[]" id="prodid-${i}" value="${v.id}" class="prod-id">
                    <input type="hidden" name="parent_id[]" id="parentid-${i}" value="${v.parent_id}" class="parent-id">
                </tr>
            `;            
        },

        calcTotals() {
            let total = 0;
            $('#productsTbl tbody tr').each(function() {
                const amount = accounting.unformat($(this).find('.amount').val());
                total += amount;
            });
            $('#total').val(accounting.formatNumber(total));
        },
    };

    $(() => Index.init());
</script>
@endsection