@php
    $label = 'Quote';
    $tid = gen4tid('QT-', $quote->tid);
    if ($quote->bank_id) {
        $label = 'PI';
        $tid = gen4tid('PI-', $quote->tid);
    }
    $ptax = $quote->tax_id / 100;
@endphp
<div class="form-group row">
    <div class="col-2">
        <label for="tid">{{ $label }} No</label>
        <input type="text" class="form-control" value="{{ $tid }}" disabled>
    </div>
    <div class="col-2">
        <label for="date">Date</label>
        <input type="text" name="date" class="form-control datepicker">
    </div>
    <div class="col-3">
        <label for="tool_ref">Tool Requisition No</label>
        <input type="text" class="form-control" name="tool_ref">
    </div>
    <div class="col-5">
        <label for="note">Note</label>
        <input type="text" class="form-control" name="note" required>
    </div>
</div>

<div class="table-responsive">
    <table class="tfr my_stripe_single text-center" id="stockTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>No</th>
                <th>Product Name</th>
                <th>UoM</th>
                <th>Approved Qty</th>
                <th>Issued Qty</th>
                <th width="10%">Qty</th>
                <th width="10%">Requisition No</th>
                <th>Warehouse</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quote->budget->items as $item)
                @if ($item->a_type == 1)
                    <tr>
                        <td>{{ $item->numbering }}</td>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->unit }}</td>
                        <td>{{ number_format($item->new_qty, 1) }}</td>
                        <td>{{ $item->issue_qty }}</td>
                        <td><input type="text" class="form-control qty" name="qty[]"></td>
                        <td><input type="text" class="form-control ref" name="ref[]"></td>
                        <td>
                            <select name="warehouse_id[]" id="" class="form-control wh">
                                <option value="">-- Select Warehouse --</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->title }}</option>
                                @endforeach
                            </select>
                        </td>
                        <input type="hidden" name="product_id[]" value="{{ $item->product_id }}">
                        <input type="hidden" class="price" name="price[]" value="{{ $item->price }}">
                        <input type="hidden" class="amount" name="amount[]" value="{{ $item->price * ($quote->tax_id / 100 + 1) }}" >
                    </tr>
                @else
                    <tr>
                        <td>{{ $item->numbering }}</td>
                        <td colspan="7">{{ $item->product_name }}</td>
                    </tr>
                @endif
            @endforeach
            <tr class="bg-white">
                <td colspan="6"></td>
                <td colspan="2">
                    <div class="form-inline float-right">
                        <label for="subtotal">Subtotal:</label>
                        <input type="text" class="form-control ml-1" name="subtotal" id="subtotal" readonly>
                    </div>
                </td>
            </tr>
            <tr class="bg-white">
                <td colspan="6"></td>
                <td colspan="2">
                    <div class="form-inline float-right">
                        <label for="tax">Tax:</label>
                        <input type="text" class="form-control ml-1" name="tax" id="tax" readonly>
                    </div>
                </td>
            </tr>
            <tr class="bg-white">
                <td colspan="6"></td>
                <td colspan="2">
                    <div class="form-inline float-right">
                        <label for="total">Total:</label>
                        <input type="text" class="form-control ml-1" name="total" id="total" readonly>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="form-group row">
    <div class="col-12">
        {{ Form::submit('Issue Item', ['class' => 'btn btn-success btn-lg float-right mr-1']) }}
        <input type="hidden" name="quote_id" value="{{ $quote->id }}">
    </div>
</div>
