<div class="form-group row">
    <div class="col-6">
        <label for="quote">{{ $quote->bank_id? '#Proforma Invoice' : '#Quote' }}</label>
        @php
            $quote_tid = gen4tid($quote->bank_id? 'PI-' : 'Qt-', $quote->tid);
        @endphp
        {{ Form::text('quote', $quote_tid . ' - '. $quote->notes, ['class' => 'form-control', 'id' => 'reference', 'disabled']) }}
        {{ Form::hidden('quote_id', $quote->id) }}
    </div>
    <div class="col-2">
        <label for="tid">Issuance No.</label>
        {{ Form::text('tid', @$projectstock ? $projectstock->tid : $tid, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
    </div>  
    <div class="col-2">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
    </div> 
    <div class="col-2">
        <label for="reference">Reference</label>
        {{ Form::text('reference', null, ['class' => 'form-control', 'id' => 'reference', 'required']) }}
    </div> 
</div> 
<div class="form-group row">  
    <div class="col-12">
        <label for="note">Note</label>    
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note', 'required']) }}
    </div>                          
</div>

<div class="table-responsive">
    <table class="table tfr my_stripe_single text-center" id="productsTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>#</th>
                <th>Product</th>
                <th>UoM</th>
                <th>Qty Approved</th>
                <th>Qty Issued</th>
                <th>Warehouse</th>
                <th width="10%">Qty</th>
            </tr>
        </thead>
        <tbody>   
            @foreach ($budget_items as $i => $item)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td>
                        <select name="unit[]" id="unit" class="custom-select">
                            <option value="{{ $item->unit }}">{{ $item->unit }}</option>
                        </select>
                    </td>
                    <td>{{ +$item->new_qty }}</td>
                    <td>{{ +$item->issue_qty }}</td>
                    <td>
                        @php
                            $qty_limit = 0;
                            $stock_qty = 0;
                        @endphp
                        <select name="warehouse_id[]" id="warehouse" class="custom-select wh">
                            @foreach ($stock as $stock_item)
                                @php
                                    if ($item->product && $item->product->parent_id == $stock_item->parent_id) {
                                        $qty_limit = $item->product->alert;
                                        $stock_qty = $stock_item->qty;
                                        $wh = $stock_item->warehouse;
                                    } else continue;
                                @endphp
                                <option value="{{ $wh->id }}"> 
                                    {{ $wh->title }} ({{ +$stock_qty }})
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="text" name="qty[]" id="qty" class="form-control qty"></td>
                    <input type="hidden" name="budget_item_id[]" value="{{ $item->id }}">
                    <input type="hidden" name="qty_limit[]" value="{{ +$qty_limit }}" class="qty-limit">
                    <input type="hidden" name="stock_qty[]" value="{{ +$stock_qty }}" class="qty-stock">
                </tr>
            @endforeach
        </tbody>                
    </table>
</div>
<div class="row">  
    <div class="col-2 ml-auto">
        <label for="qty_total">Total Issue Qty</label>    
        {{ Form::text('qty_total', null, ['class' => 'form-control', 'id' => 'qty_total', 'readonly']) }}
    </div>                          
</div>
<div class="row mt-1">                            
    <div class="col-2 ml-auto">  
        {{ Form::submit(@$projectstock? 'Update' : 'Issue Stock', ['class' =>'btn btn-primary btn-lg']) }}
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
        init() {
            $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());
            $('#productsTbl').ready(this.tableReady);
            $('#productsTbl').on('keyup', '.qty', function() { 
                Form.columnTotals(); 
                Form.qtyAlert($(this));
            });
        },

        tableReady() {
            $(this).find('tbody tr').each(function() {
                const el = $(this);
                const warehouse = el.find('.wh');
                const qty = el.find('.qty');
                if (!warehouse.children().length) {
                    warehouse.attr('disabled', true);
                    qty.attr('disabled', true);
                }
            });
        },

        qtyAlert(el) {
            const qty = el.val();
            const row = el.parents('tr');
            const qtyLimit = row.find('.qty-limit').val();
            const qtyStock = row.find('.qty-stock').val();
            const msg = `<div class="alert alert-warning col-12 stock-alert" role="alert">
                <strong>Minimum inventory limit!</strong> Please restock product.
            </div>`;
            if (qtyStock <= qtyLimit && qty >= qtyStock) {
                $('.content-header div:first').before(msg);
                setTimeout(() => $('.content-header div:first').remove(), 4000);
                scroll(0,0);
            }
        },

        columnTotals() {
            let qtyTotal = 0;
            $('#productsTbl tbody tr').each(function() {
                let qty = $(this).find('.qty').val();
                if (qty > 0) qtyTotal += parseFloat(qty);
            });
            $('#qty_total').val(qtyTotal);
        },
    }

    $(() => Form.init());
</script>
@endsection
