{{ Form::open(['route' => ['biller.import.general', $data['type']], 'method' => 'POST', 'files' => true, 'id' => 'import-data']) }}
    {{ Form::hidden('update', 1) }}
    {!! Form::file('import_file', array('class'=>'form-control input col-md-6 mb-1' )) !!}
    {{ Form::submit(trans('import.upload_import'), ['class' => 'btn btn-primary btn-md']) }}
{{ Form::close() }}

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    const config = {
        ajaxSetup: { headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
    };

    const Form = {
        init() {
            const {ajaxSetup} = config;
            $.ajaxSetup(ajaxSetup);
        },
    }

    $(() => Form.init());
</script>
@endsection