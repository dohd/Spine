<div class='form-group'>
    {{ Form::label( 'department', 'Department Name',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{-- {{ Form::text('department', null, ['class' => 'form-control round', 'placeholder' => 'Department Name']) }} --}}
        <select class="form-control round" id="departmentbox" data-placeholder="Search Department"></select>
        <input type="hidden" name="department_id" value="{{ @$jobtitles->department ?: 1 }}" id="departmentid">
         <input type="hidden" name="department" value="{{ @$jobtitles->department?: 1 }}" id="department">
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'name','Job Title Name',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('name', null, ['class' => 'form-control round', 'placeholder' =>'Job Title Name']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'note', trans('departments.note'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('note', null, ['class' => 'form-control round', 'placeholder' => trans('departments.note')]) }}
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
});


// load departments
const departmentUrl = "{{ route('biller.jobtitles.select') }}";
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
