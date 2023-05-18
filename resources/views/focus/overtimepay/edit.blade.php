@extends ('core.layouts.app')

@section ('title', 'OverTime Pay Management' . ' | ' . 'Edit')

@section('page-header')
    <h1>
        {{ 'OverTime Pay Management' }}
        <small>Edit</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Edit</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.overtimepay.partials.overtimepay-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-content">

                                <div class="card-body">
                                    {{ Form::model($overtimepay, ['route' => ['biller.overtimepay.update', $overtimepay], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'edit-department']) }}

                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.overtimepay.form")
                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.overtimepay.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                            {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
                                            <div class="clearfix"></div>
                                        </div><!--edit-form-btn-->
                                    </div><!--form-group-->

                                    {{ Form::close() }}
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const departmentText = "{{ $overtimepay->employee->first_name }} ";
    const departmentVal = "{{ $overtimepay->department_id }}";
    $('#employeebox').append(new Option(departmentText, true)).change();
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});
    $('.datepicker').datepicker({autoHide: true})
    $('#date').datepicker('setDate', new Date());

    // On searching supplier
    $('#employeebox').change(function() {
        const name = $('#employeebox option:selected').text().split(' : ')[0];
        const [id, taxId] = $(this).val().split('-');
        $('#employeeid').val(id);
        $('#employee').val(name);
        $('#issue_type').prop("disabled", false);
    });

    // load employees
    const employeeUrl = "{{ route('biller.surcharge.select') }}";
    function employeeData(data) {
        return {results: data.map(v => ({id: v.id, text: v.first_name+' : '+v.email}))};
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
