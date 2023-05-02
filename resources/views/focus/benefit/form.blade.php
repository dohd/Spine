
    <div class='form-group row'>
    
        <div class='col-4'>
            {{ Form::label( 'employee_name', 'Employee Name',['class' => 'control-label']) }}
            <select class="form-control round" id="employeebox" data-placeholder="Search employee"></select>
            <input type="hidden" name="employee_id" value="{{ @$benefits->employee_name ?: 1 }}" id="employeeid">
             <input type="hidden" name="employee_name" value="{{ @$benefits->employee_name?: 1 }}" id="employee">
            {{-- {{ Form::text('employee_name', null, ['class' => 'form-control round', 'placeholder' => '']) }} --}}
        </div>
        <div class='col-4'>
            {{ Form::label( 'name', 'Benefit Name',['class' => 'control-label']) }}
            {{ Form::text('name', null, ['class' => 'form-control round', 'placeholder' => 'e.g Award']) }}
        </div>
        <div class='col-4'>
            {{ Form::label( 'type', 'Benefit Type',['class' => 'control-label']) }}
            <select class="form-control round" name="type" id="leave-payment" data-placeholder="Leave Payment">
                <option value="leave_payment">Leave Payment</option>
                <option value="others">Others</option>
            </select>
            {{-- {{ Form::text('type', null, ['class' => 'form-control round', 'placeholder' => 'e.g Best Sales']) }} --}}
        </div>
    </div>
    <div class="form-group row">
        <div class='col-4'>
            {{ Form::label( 'month', 'Month',['class' => 'control-label']) }}
            {{ Form::month('month', null, ['class' => 'form-control round', 'placeholder' => 'e.g Jan 2022']) }}
        </div>
        <div class='col-4'>
            {{ Form::label( 'leave_payment', 'Leave Payment Days',['class' => 'control-label']) }}
            {{ Form::number('leave_payment_days', null, ['class' => 'form-control round', 'placeholder' => '1']) }}
        </div>
        <div class='col-4'>
            {{ Form::label( 'amount', 'Amount',['class' => 'control-label']) }}
            {{ Form::number('amount', null, ['class' => 'form-control round', 'placeholder' => '0.00']) }}
        </div>
    </div>
    <div class="form-group row">
        <div class='col-8'>
            {{ Form::label( 'note', 'Note',['class' => 'control-label']) }}
            {{ Form::text('note', null, ['class' => 'form-control round', 'placeholder' => 'note']) }}
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
