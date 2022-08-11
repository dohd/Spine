<div class="form-group row">
    <div class="col-4">
        <label for="supplier">Supplier</label>
        {{ Form::text('supplier_id', @$supplier->name, ['class' => 'form-control', 'id' => 'supplier', 'readonly']) }}
    </div>
    <div class="col-2">
        <label for="tid" class="caption">Bill No.</label>
        {{ Form::text('tid', @$goodsreceivenote ? $goodsreceivenote->tid : $tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
    </div>  
    <div class="col-2">
        <label for="pin">KRA PIN</label>
        {{ Form::text('tax_pin', @$supplier->taxid, ['class' => 'form-control', 'id' => 'tax_pin', 'required']) }}
    </div> 
    <div class="col-2">
        <label for="date">Bill Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
    </div> 
    <div class="col-2">
        <label for="date">Bill Due Date</label>
        {{ Form::text('due_date', null, ['class' => 'form-control datepicker', 'id' => 'due_date']) }}
    </div> 
</div> 

<div class="form-group row">  
    <div class="col-6">
        <label for="note">Note</label>    
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note', 'required']) }}
    </div>                          
</div>

<div class="table-responsive">
    <table class="table tfr my_stripe_single text-center" id="grnTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>#</th>
                <th>Date</th>
                <th>GRN No.</th>
                <th>Purchase Type</th>
                <th>Dnote</th>
                <th>Note</th>
                <th>Rate</th>
            </tr>
        </thead>
        <tbody>   
            @if ($supplier)
                @php
                    $subtotal = $supplier->goodsreceivenotes->sum('subtotal');
                    $tax = $supplier->goodsreceivenotes->sum('tax');
                    $total = $supplier->goodsreceivenotes->sum('total');
                @endphp
                @foreach ($supplier->goodsreceivenotes as $i => $grn)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ dateFormat($grn->date) }}</td>
                        <td>{{ gen4tid('GRN-', $grn->tid) }}</td>
                        <td>{{ $grn->purchaseorder? gen4tid('PO-', $grn->purchaseorder->tid) . ' - ' . $grn->purchaseorder->note : '' }}</td>
                        <td>{{ $grn->dnote }}</td>
                        <td>{{ $grn->note }}</td>
                        <td>{{ numberFormat($grn->total) }}</td>
                        <input type="hidden" name="grn_id[]" value="{{ $grn->id }}">
                        <input type="hidden" name="grn_note[]" value="{{ $grn->note }}">
                        <input type="hidden" name="grn_subtotal[]" value="{{ $grn->subtotal }}">
                        <input type="hidden" name="grn_tax[]" value="{{ $grn->tax }}">
                        <input type="hidden" name="grn_total[]" value="{{ $grn->total }}">
                    </tr>                 
                @endforeach
            @endif
        </tbody>                
    </table>
</div>
<div class="row">  
    <div class="col-2 ml-auto">
        <label for="subtotal">Subtotal</label>    
        {{ Form::text('subtotal', numberFormat($subtotal), ['class' => 'form-control', 'id' => 'subtotal', 'readonly']) }}
    </div>                          
</div>
<div class="row">  
    <div class="col-2 ml-auto">
        <label for="tax">Tax</label>    
        {{ Form::text('tax', numberFormat($tax), ['class' => 'form-control', 'id' => 'tax', 'readonly']) }}
    </div>                           
</div>
<div class="row">  
    <div class="col-2 ml-auto">
        <label for="total">Total</label>    
        {{ Form::text('total', numberFormat($total), ['class' => 'form-control', 'id' => 'total', 'readonly']) }}
    </div>                          
</div>
<div class="row mt-1">                            
    <div class="col-2 ml-auto">  
        {{ Form::submit(@$payment? 'Update Payment' : 'Create Bill', ['class' =>'btn btn-primary btn-lg']) }}
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
        },

    }

    $(() => Form.init());
</script>
@endsection
