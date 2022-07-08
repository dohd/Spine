<div class="row">
    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label( 'purchase_date', 'Purchase Date',['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::date('purchase_date', null, ['class' => 'form-control box-size datepicker']) }}
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label('account_type', 'Account Type', ['class' => 'col control-label']) }}
            <div class='col'>
                <select class="form-control required" name="account_type" id="account_type" required>
                    <option value="">-- Select Account Type --</option>
                    @foreach (['Asset' => 'Asset', 'Expense' => 'Stockable Expense e.g PPE'] as $key => $val)
                        <option value="{{ $key }}" {{ $key == @$assetequipment->account_type? 'selected' : '' }}>
                            {{ $val }}
                        </option>
                    @endforeach                                       
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label('ledger_account', 'Ledger Account', ['class' => 'col control-label']) }}
            <div class='col'>
                <select class="form-control" name="account_id" id="account_id" data-placeholder="Choose Ledger Account" required>
                    @isset ($assetequipment) 
                        <option value="{{ $assetequipment->account_id }}" selected>
                            {{ $assetequipment->account? $assetequipment->account->holder : '' }}
                        </option>
                    @endisset
                </select>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label('condition', 'Condition Type',['class' => 'col control-label']) }}
            <div class='col'>
                <select class="form-control" name="condition">
                    <option value="">-- Select Condition Type --</option>
                    @foreach (['new', 'used'] as $val)
                        <option value="{{ $val }}" {{ $val == @$assetequipment->condition? 'selected' : '' }}>
                            {{ ucfirst($val) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label( 'name', 'Item Name',['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::text('name', null, ['class' => 'form-control box-size', 'placeholder' => 'Item Name*', 'required']) }}
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label( 'serial', 'Item Serial',['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::text('serial', null, ['class' => 'form-control box-size', 'placeholder' => 'Item Serial']) }}
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label('manufacturer', 'Manufacturer',['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::text('manufacturer', null, ['class' => 'form-control box-size', 'placeholder' => 'Vendor']) }}
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label('model', 'Model', ['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::text('model', null, ['class' => 'form-control box-size']) }}
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label( 'location', 'Location', ['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::text('location', null, ['class' => 'form-control box-size', 'placeholder' => 'Location']) }}
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label('warranty', 'Under Warranty', ['class' => 'col control-label']) }}
            <div class='col'>
                <select class="form-control" name="warranty">
                    <option value="">-- Select Warranty --</option>
                    @foreach (['no', 'yes'] as $val)
                        <option value="{{ $val }}" {{ $val == @$assetequipment->warranty? 'selected' : '' }}>
                            {{ ucfirst($val) }}
                        </option>
                    @endforeach                  
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class='form-group'>
            {{ Form::label( 'warranty_expiry_date', 'Warranty Expiry Date', ['class' => 'col control-label']) }}
            <div class='col'>
                {{ Form::date('warranty_expiry_date', null, ['class' => 'form-control box-size']) }}
            </div>
        </div>
    </div>
</div>

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    $("#account_id").select2();
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    // select2 config
    $("#account_id").select2({
        ajax: {
            url: "{{ route('biller.assetequipments.ledger_load') }}",
            dataType: 'json',
            type: 'POST',
            data: ({term}) => ({term, account_type: $("#account_type").val()}),
            quietMillis: 50,
            processResults: function(data) {
                return { results: data.map(v => ({text: v.holder, id: v.id})) };
            },
        }
    });

    $("#account_type").on('change', function() {
        $("#account_id").val('').change();
    });
</script>
@endsection
