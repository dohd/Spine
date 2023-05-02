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
                <option value="{{ $val }}" {{ $val == @$productvariable->unit_type? 'selected' : '' }}>
                    {{ ucfirst($val) }}
                </option>    
            @endforeach          
        </select>
    </div>

    <div class='col-2'>
        <label for="type">Related Base Unit</label>
        <select name="base_unit_id" id="base_unit_id" class="custom-select" disabled>
            @foreach ($base_units as $unit)
                <option value="{{ $unit->id }}" {{ $val == @$productvariable->base_unit_id? 'selected' : '' }}>
                    {{ ucfirst($unit->title) }} ({{ $unit->code }})
                </option>    
            @endforeach          
        </select>
    </div>
    
    <div class='col-2'>
        <label for="rate">Ratio (per base unit)</label>
        {{ Form::text('base_ratio', null, ['class' => 'form-control', 'id' => 'base_ratio', 'readonly']) }}
    </div>
</div>

<div class="form-group row">
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
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    const Form = {
        init() {
            $('#unit_type').change(this.unitTypeChange);
            $('#base_unit_id').select2({allowClear: true}).val('').change();
            $('#base_ratio').focusout(this.baseRatioChange).focusout();

            const unit = @json(@$productvariable);
            if (unit) {
                $('#base_unit_id').val(unit.base_unit_id).attr('selected', true).change();
                $('#unit_type').change();
                const ratio = $('#base_ratio').val();
                $('#base_ratio').val(accounting.formatNumber(ratio));                
            }
        },

        baseRatioChange() {
            const el = $(this);
            const ratio = accounting.unformat(el.val());
            if (!ratio) el.val(1);
            if ($('#unit_type').val() == 'compound') {
                if (ratio <= 1) el.val(2);
            }
               
            el.val(accounting.formatNumber(el.val()));
        },

        unitTypeChange() {
            const el = $(this);
            if (el.val() == 'compound') {
                $('#base_ratio').attr({
                    readonly: false,
                    required: true
                });
                $('#base_unit_id').attr({
                    disabled: false,
                    required: true
                });
            } else {
                $('#base_ratio').val('1.00').attr({
                    readonly: true,
                    required: false
                });
                $('#base_unit_id').attr({
                    disabled: true,
                    required: false
                }).val('').change();
            }
            $('#base_ratio').focusout();
        }
    }

    $(() => Form.init());
</script>
@endsection
