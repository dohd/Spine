<div class="row form-group">
    <div class='col-3'>
        <label for="category">Unit Category</label>
        <select name="category" id="category" class="custom-select">
            <option value="">--  Select Category --</option>
            @foreach ($categories as $val)
                <option value="{{ $val }}">{{ $val }}</option>
            @endforeach
        </select>
    </div>
    <div class='col-3'>
        <label for="category">Add Category</label>
        {{ Form::text('category', null, ['class' => 'form-control ', 'placeholder' => 'e.g Mass', 'id' => 'add_category']) }}
    </div>
    <div class='col-3'>
        <label for="description">Description</label>
        {{ Form::text('description', null, ['class' => 'form-control ', 'placeholder' => 'e.g Liquid', 'required']) }}
    </div>
</div>
<div class="row form-group">
    <div class='col-3'>
        <label for="compound_unit">Compound Unit</label>
        {{ Form::text('compound_unit', null, ['class' => 'form-control ', 'placeholder' => 'e.g L', 'required']) }}
    </div>
    <div class='col-3'>
        <label for="base_unit">Base Unit</label>
        {{ Form::text('base_unit', null, ['class' => 'form-control ', 'placeholder' => 'e.g ml', 'required']) }}
    </div>
    <div class='col-3'>
        <label for="rate">Ratio (per base unit)</label>
        {{ Form::text('base_ratio', 1, ['class' => 'form-control ', 'placeholder' => 'e.g 1,000', 'id' => 'base_ratio', 'required']) }}
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

@section("after-scripts")
<script type="text/javascript">
    const Form = {
        init() {
            $('#base_ratio').change(this.rateChange);
            $('#add_category').change(this.addCategoryChange);
            $('#category').change(this.categoryChange);
        },

        categoryChange() {
            const el = $(this);
            if (el.val()) {
                $('#add_category').val('').attr({
                    disabled: true,
                    required: false
                });
            } else {
                $('#add_category').attr({
                    disabled: false,
                    required: true
                });
            }
        },

        addCategoryChange() {
            const el = $(this);
            if (el.val()) {
                el.val(el.val().toLowerCase());
                $('#category').val('').attr({
                    disabled: true,
                    required: false
                });
            } else {
                $('#category').attr({
                    disabled: false,
                    required: true
                });
            }
        },

        rateChange() {
            const el = $(this);
            el.val(accounting.formatNumber(el.val()));
        }
    }

    $(() => Form.init());
</script>
@endsection
