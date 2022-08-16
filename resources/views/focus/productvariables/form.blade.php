<div class="row form-group">
    <div class='col-3'>
        <label for="title">Title</label>
        {{ Form::text('title', null, ['class' => 'form-control', 'required']) }}
    </div>
    <div class='col-2'>
        <label for="code">Code</label>
        {{ Form::text('code', null, ['class' => 'form-control', 'required']) }}
    </div>
    <div class='col-2'>
        <label for="type">Unit Type</label>
        <select name="unit_type" id="unit_type" class="custom-select">
            @foreach (['base', 'compound'] as $val)
                <option value="{{ $val }}" {{ $val == @$productvariable->unit_type? 'sselected' : '' }}>
                    {{ ucfirst($val) }}
                </option>    
            @endforeach          
        </select>
    </div>
    
    <div class='col-2'>
        <label for="rate">Ratio (per base unit)</label>
        {{ Form::text('base_ratio', '1.00', ['class' => 'form-control', 'id' => 'base_ratio', 'readonly']) }}
    </div>

    <div class='col-2'>
        <label for="count_type">Count Type</label>
        <select name="count_type" id="count_type" class="custom-select">
            @foreach (['whole', 'rational'] as $val) 
                <option value="{{ $val }}" {{ $val == @$productvariable->count_type? 'sselected' : '' }}>{{ ucfirst($val) }}</option>
            @endforeach
        </select>
    </div>
</div>

@section("after-scripts")
<script type="text/javascript">
    const Form = {
        init() {
            $('#unit_type').change(this.unitTypeChange);
            $('#base_ratio').focusout(this.baseRatioChange);
        },

        baseRatioChange() {
            const el = $(this);
            const val = accounting.formatNumber(el.val()); 
            el.val(val);
        },

        unitTypeChange() {
            const el = $(this);
            if (el.val() == 'compound') {
                $('#base_ratio').attr({
                    readonly: false,
                    required: true
                });
            } else {
                $('#base_ratio').val('1.00').attr({
                    readonly: true,
                    required: false
                });
            }
        }
    }

    $(() => Form.init());
</script>
@endsection
