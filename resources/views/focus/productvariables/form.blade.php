<div class="row form-group">
    <div class='col-3'>
        <label for="name">Unit of Measure</label>
        {{ Form::text('name', null, ['class' => 'form-control ', 'placeholder' => 'e.g Litre']) }}
    </div>
    <div class='col-3'>
        <label for="compound_unit">Compound Unit</label>
        {{ Form::text('compound_unit', null, ['class' => 'form-control ', 'placeholder' => 'e.g L']) }}
    </div>
    <div class='col-3'>
        <label for="base_unit">Base Unit</label>
        {{ Form::text('base_unit', null, ['class' => 'form-control ', 'placeholder' => 'e.g l']) }}
    </div>
    <div class='col-2'>
        <label for="count_type">Unit Count Type</label>
        <select name="count_type" id="count_type" class="custom-select">
            @foreach (['whole', 'rational'] as $val) 
                <option value="{{ $val }}" {{ $val == @$productvariable->count_type? 'sselected' : '' }}>{{ ucfirst($val) }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="row form-group">
    <div class='col-3'>
        <label for="rate">Compound Conversion Rate (per base unit)</label>
        {{ Form::text('conversion_rate', 1, ['class' => 'form-control ', 'placeholder' => 'e.g 1,000', 'id' => 'conversion_rate']) }}
    </div>
</div>

@section("after-scripts")
<script type="text/javascript">
    const Form = {
        init() {
            $('#conversion_rate').change(this.rateChange);
        },

        rateChange() {
            const el = $(this);
            el.val(accounting.formatNumber(el.val()));
        }
    }

    $(() => Form.init());
</script>
@endsection
