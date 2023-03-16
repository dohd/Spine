
    <div class='form-group row'>
        <div class='col-4'>
            {{ Form::label( 'employee', 'Employee Name',['class' => 'control-label']) }}
            <select class="form-control round" id="employeebox" data-placeholder="Search Employee"></select>
            <input type="hidden" name="employee_id" value="{{ @$salary->employee_name ?: 1 }}" id="employeeid">
             <input type="hidden" name="employee_name" value="{{ @$salary->employee_name?: 1 }}" id="employee">
        </div>
        <div class='col-4'>
            {{ Form::label( 'basic_pay', 'Basic Pay',['class' => 'control-label']) }}
            {{ Form::number('basic_pay', null, ['class' => 'form-control round', 'placeholder' => '0.00', 'required']) }}
        </div>
        <div class='col-4'>
            {{ Form::label( 'house_allowance', 'House Allowance',['class' => 'control-label']) }}
            {{ Form::number('house_allowance', null, ['class' => 'form-control round', 'placeholder' => '0.00']) }}
        </div>
    </div>
    <div class='form-group row'>
        <div class='col-4'>
            {{ Form::label( 'transport_allowance', 'Transport Allowance',['class' => 'control-label']) }}
            {{ Form::number('transport_allowance', null, ['class' => 'form-control round', 'placeholder' => '0.00']) }}
        </div>
        <div class='col-4'>
            {{ Form::label( 'directors_fee', 'Directors Fees',['class' => 'control-label']) }}
            {{ Form::number('directors_fee', null, ['class' => 'form-control round', 'placeholder' => '0.00']) }}
        </div>
        <div class="col-4">
            {{ Form::label( 'contract_type', 'Contract Type',['class' => 'control-label']) }}
            <select class="form-control round" name="contract_type" id="employeebox" data-placeholder="Search Contract">
                <option value="permanent">Permanent</option>
                <option value="contract">Contract</option>
                <option value="casual">Casual</option>
                <option value="intern">Intern</option>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-4">
            {{ Form::label( 'start_date', 'Start Date',['class' => 'control-label']) }}
            {{ Form::date('start_date', null, ['class' => 'form-control round', 'placeholder' => '', 'required']) }}
        </div>
        <div class="col-4">
            {{ Form::label( 'duration', 'Duration',['class' => 'control-label']) }}
            {{ Form::number('duration', null, ['class' => 'form-control round', 'placeholder' => '1', 'required']) }}
        </div>
        
    </div>

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
    <script type="text/javascript">
        $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

// On searching supplier
$('#employeebox').change(function() {
    const name = $('#employeebox option:selected').text().split(' : ')[0];
    const [id, taxId] = $(this).val().split('-');
    $('#employeeid').val(id);
    $('#employee').val(name);
});


// load employees
const employeeUrl = "{{ route('biller.salary.select') }}";
function employeeData(data) {
    return {results: data.map(v => ({id: v.id, text: v.first_name}))};
}
$('#employeebox').select2(select2Config(employeeUrl, employeeData));
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
