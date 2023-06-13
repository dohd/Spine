
    <div class='form-group row'>
        <div class='col-4'>
            {{ Form::label( 'department', 'Department Name',['class' => 'control-label']) }}
            <select class="form-control round" id="departmentbox" data-placeholder="Search Department"></select>
            <input type="hidden" name="department_id" value="{{ @$benefits->department_name ?: 1 }}" id="departmentid">
             <input type="hidden" name="department" value="{{ @$benefits->department_name?: 1 }}" id="department">
        </div>
        <div class='col-4'>
            {{ Form::label( 'name','Job Title Name',['class' => ' control-label']) }}
            <select id="pricegroup_id" name="pricegroup_id" class="custom-select">
                <option value="0" selected>Default </option>
                @foreach($dept_jobtitle as $group)
                    {{-- @if (!$group->is_client) --}}
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    
                @endforeach
            </select>
        </div>
        <div class='col-4'>
            {{ Form::label( 'car_benefit', 'Car Benefit',['class' => 'control-label']) }}
            {{ Form::number('car_benefit', null, ['class' => 'form-control round', 'placeholder' => '0.00']) }}
        </div>
    </div>
    <div class='form-group row'>
        <div class='col-4'>
            {{ Form::label( 'house_allowance', 'House Allowance',['class' => 'control-label']) }}
            {{ Form::number('house_allowance', null, ['class' => 'form-control round', 'placeholder' => '0.00']) }}
        </div>
        <div class='col-4'>
            {{ Form::label( 'food_allowance', 'Food Allowance',['class' => 'control-label']) }}
            {{ Form::number('food_allowance', null, ['class' => 'form-control round', 'placeholder' => '0.00']) }}
        </div>
        <div class='col-4'>
            {{ Form::label( 'transport_allowance', 'Transport Allowance',['class' => 'control-label']) }}
            {{ Form::number('transport_allowance', null, ['class' => 'form-control round', 'placeholder' => '0.00']) }}
        </div>
    </div>
    <div class="form-group row">
        <div class='col-4'>
            {{ Form::label( 'director_fee', 'Directors Fees',['class' => 'control-label']) }}
            {{ Form::number('director_fee', null, ['class' => 'form-control round', 'placeholder' => '0.00']) }}
        </div>
    </div>

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
    <script type="text/javascript">
        $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

// On searching supplier
$('#departmentbox').change(function() {
    const name = $('#departmentbox option:selected').text().split(' : ')[0];
    const [id, taxId] = $(this).val().split('-');
    $('#departmentid').val(id);
    $('#department').val(name);
    let priceCustomer = '';
            $('#pricegroup_id option').each(function () {
                if (id == $(this).val())
                priceCustomer = $(this).val();
            });
            
            $('#pricegroup_id').val(priceCustomer);
});


// load departments
const departmentUrl = "{{ route('biller.benefits.select') }}";
function departmentData(data) {
    return {results: data.map(v => ({id: v.id, text: v.name}))};
}
$('#departmentbox').select2(select2Config(departmentUrl, departmentData));
// select2 config
function select2Config(url, callback) {
    return {
        ajax: {
            url,
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: ({term}) => ({q: term, keyword: term}),
            processResults: callback
        }
    }
}
    </script>
@endsection
