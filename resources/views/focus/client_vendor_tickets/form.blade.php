<div class="form-group row">
    <div class="col-md-4">
        <label for="category">Ticket Category</label>
        <select name="category" id="category" class="custom-select">
            @foreach (['Login Reset'] as $i => $value)
                <option value="{{ $value }}" {{ @$tenant_ticket->category == $value? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label for="service">Related Service</label>
        <select name="tenant_service_id" id="service" class="custom-select">
            <option value="">None</option>
            @foreach ([] as $i => $service)
                <option value="{{ $service->id }}" {{ @$tenant_ticket->tenant_service_id == $service->id? 'selected' : '' }}>
                    {{ $service->description }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label for="priority">Priority</label>
        <select name="priority" id="priority" class="custom-select">
            @foreach (['Low', 'Medium', 'High'] as $i => $value)
                <option value="{{ $value }}" {{ @$tenant_ticket->priority == $value? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group row">
    <div class="col-12">
        <label for="subject" class="caption">Subject</label>
        <div class="input-group">
            <div class="w-100">
                {{ Form::text('subject', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
    </div>
</div> 
<div class="form-group row">
    <div class="col-12">
        <label for="message" class="caption">Message</label>
        <div class="input-group">
            <div class="w-100">
                {{ Form::textarea('message', null, ['class' => 'form-control', 'rows' => 6, 'required' => 'required']) }}
            </div>
        </div>
    </div>
</div> 

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        init() {
            $.ajaxSetup(config.ajax);
        },
    };

    $(Index.init);
</script>
@endsection