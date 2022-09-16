<div class="form-group row">
    <div class="col-2">
        <label for="tid">List No</label>
        {{ Form::text('tid', @$holiday_list? $holiday_list->tid : $tid+1, ['class' => 'form-control', 'readonly']) }}
    </div>
    <div class="col-4">
        <label for="title">Title</label>
        {{ Form::text('title', null, ['class' => 'form-control', 'id' => 'title', 'required']) }}
    </div>
    <div class="col-6">
        <label for="note">Note</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note', 'required']) }}
    </div>
</div>

<hr>
<div class="form-group row">
    <div class="col-8">
        <label for="date_list">Date List</label>
        {{ Form::text('date_list', null, ['class' => 'form-control', 'id' => 'date_list', 'required']) }}
    </div>
</div>

@section('extra-scripts')
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
        },

    };

    $(() => Index.init());
</script>
@endsection