<div class="form-group row">
    <div class="col-6">
        <label for="supplier">Search Supplier</label>
        <select id="supplier" name="supplier_id" class="form-control" data-placeholder="Choose Supplier" required>
            @foreach ($suppliers as $row)
                <option value="{{ $row->id }}" {{ @$goodsreceivenote && $goodsreceivenote->supplier_id == $row->id? 'selected' : '' }}>
                    {{ $row->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-4">
        <label for="purchaseorder" class="caption">Purchase Order</label>
        <select name="purchaseorder_id" id="purchaseorder" class="custom-select">
            @isset($goodsreceivenote)
                <option value="{{ $goodsreceivenote->purchaserder_id }}">
                    {{ $goodsreceivenote->purchaseorder? $goodsreceivenote->purchaseorder->note : '' }}
                </option>
            @endisset
        </select>
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
        <label for="invoice" class="caption">Invoice Date</label>
        {{ Form::text('invoice_date', null, ['class' => 'form-control datepicker', 'id' => 'invoice_date', 'disabled']) }}
    </div>
    <div class="col-2">
        <label for="tax" class="caption">TAX %</label>
        <select name="tax_rate" id="tax_rate" class="custom-select">
            @foreach ([0, 16, 8] as $val)
                <option value="{{ $val }}" {{ @$goodsreceivenote && $goodsreceivenote->tax_rate == $val? 'selected' : '' }}>
                    {{ $val? $val . '% VAT' : 'OFF' }}
                </option>
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
                <th>Project</th>
                <th>UoM</th>
                <th>Qty Ordered</th>
                <th>Qty Received</th>
                <th>Qty Due</th>
                <th width="12%">Qty</th>
            </tr>
        </thead>
        <tbody>
            @isset($goodsreceivenote)
                @php $grn = $goodsreceivenote @endphp
                @foreach ($grn->items as $i => $item)
                    @php 
                        $po_item = $item->purchaseorder_item;
                        $qty_due = $po_item->qty - $po_item->qty_received;

                    @endphp
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $po_item->description }}</td>
                        <td>{{ $po_item->project ? gen4tid('PRJ-', $po_item->project->tid) : ''}}</td>
                        <td>{{ $po_item->uom }}</td>
                        <td>{{ +$po_item->qty }}</td>
                        <td>{{ +$po_item->qty_received }}</td>
                        <td>{{ $qty_due > 0? +$qty_due : 0 }}</td>
                        <td><input name="qty[]" value="{{ +$item->qty }}" id="qty" class="form-control qty"></td>
                        <input type="hidden" name="rate[]" value="{{ +$po_item->rate }}" class="rate">
                        <input type="hidden" name="id[]" value="{{ $item->id }}">
                    </tr>
                @endforeach
            @endisset
        </tbody>    
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
        {{ Form::submit(@$goodsreceivenote? 'Update' : 'Receive Goods', ['class' =>'btn btn-primary btn-lg']) }}
    </div>
</div>

@section('after-scripts')
@include('focus.goodsreceivenotes.form_js')
@endsection
