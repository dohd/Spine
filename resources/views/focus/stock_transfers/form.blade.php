<div class="form-group row">
    <div class="col-2">
        <label for="tid">Transfer No.</label>
        {{ Form::text('tid', @$tid+1,['class' => 'form-control round', 'id' => 'tid', 'readonly']) }}
    </div>

    <div class="col-4">
        <label for="warehouse_from">Source Location</label>
        <select name="source_id" id="source" class="form-control round" required>
            <option value="">-- select source --</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}">
                    {{ $warehouse->title }} {{ $warehouse->extra }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-4">
        <label for="warehouse_to">Destination Location</label>
        <select name="destination_id" id="destination" class="form-control round" required>
            <option value="">-- select destination --</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}">
                    {{ $warehouse->title }} {{ $warehouse->extra }}
                </option>
            @endforeach
        </select>
    </div>  
</div>

<div class="form-group row">
    <div class="col-10">
        <label for="note">Note</label>
        {{ Form::text('note', null, ['class' => 'form-control round', 'id' => 'note']) }}
    </div>
</div>
<br>
<div class="table-responsive">
    <table class="table tfr my_stripe_single text-center" id="productsTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th width="5%">#</th>
                <th width="25%">Item Description</th>
                <th width="10%">Stock Qty</th>
                <th width="10%">Qty</th>
                <th>UoM</th>
                <th>Unit Price</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody></tbody>  
    </table>
</div>

<div class="form-group row">
    <div class="col-2 ml-auto">
        <label for="total">Total Amount</label>
        {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total', 'readonly']) }}
    </div>
</div>


<div class="form-group row no-gutters">
    <div class="col-1 ml-auto">
        <a href="{{ route('biller.leave.index') }}" class="btn btn-danger block">Cancel</a>    
    </div>
    <div class="col-1 ml-1">
        {{ Form::submit(@$leave? 'Update' : 'Create', ['class' => 'form-control btn btn-primary text-white']) }}
    </div>
</div>

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        stockTransfer: @json(@$stock_transfer),

        init() {
            $.ajaxSetup(config.ajax);
            $('#source').change(this.sourceLocationChange).change();
            $('#destination').change(this.destinationLocationChange);
            $('#productsTbl').on('change', '.qty, .unit_price', this.qtyPriceChange);
        },

        qtyPriceChange() {
            const row = $(this).parents('tr:first');

            const stockQty = accounting.unformat(row.find('.stock_qty').val());
            let qty = accounting.unformat(row.find('.qty').val());
            if (qty > stockQty) qty = stockQty;

            const unitPrice = accounting.unformat(row.find('.unit_price').val());
            const amount = qty * unitPrice;

            row.find('.qty').val(qty);
            row.find('.unit_price').val(accounting.formatNumber(unitPrice));
            row.find('.amount').val(accounting.formatNumber(amount));
            Index.calcTotals();
        },

        sourceLocationChange() {
            const value = $(this).val();
            $.post("{{ route('biller.warehouse_products.get') }}", {warehouse_id: value}, data => {
                $('#productsTbl tbody tr').remove();
                data.forEach((v,i) => $('#productsTbl tbody').append(Index.rowRender(v,i)));
                Index.calcTotals();
            });

            $('#destination').val('');
            $('#destination option').each(function() {
                if ($(this).attr('value') == value) $(this).addClass('d-none');
                else $(this).removeClass('d-none');
            });
        },

        destinationLocationChange() {
            const value = $('#source').val();
            $('#source option').each(function() {
                if ($(this).attr('value') == value) $(this).addClass('d-none');
                else $(this).removeClass('d-none');
            });
        },

        rowRender(v,i) {
            const unitPrice = parseFloat(v.price);
            return `
                <tr>
                    <td>${i+1}</td>
                    <td>${v.name}</td>
                    <td>${parseFloat(v.qty)}</td>
                    <td><input type="text" class="form-control qty" name="qty[]"></td>
                    <td><input type="text" class="form-control uom" name="uom[]" value="${v.unit}"></td>
                    <td><input type="text" class="form-control unit_price" name="unit_price[]" value="${accounting.formatNumber(unitPrice)}"></td>
                    <td><input type="text" class="form-control amount" name="amount[]" readonly></td>
                    <input type="hidden" class="stock_qty" value="${parseFloat(v.qty)}">
                    <input type="hidden" name="product_id[]" value="${v.id}">
                </tr>
            `;
        },

        calcTotals() {
            let total = 0;
            $('#productsTbl tbody tr').each(function() {
                row = $(this);
                const qty = accounting.unformat(row.find('.qty').val());
                const unitPrice = accounting.unformat(row.find('.unit_price').val());
                total += qty * unitPrice;
            });
            $('#total').val(accounting.formatNumber(total));
        },
    };

    $(() => Index.init());
</script>
@endsection