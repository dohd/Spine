<div class="form-group row">
    <div class="col-md-6">
        <label for="category">Ticket Category</label>
        <select name="category_id" id="category" class="custom-select">
            @foreach ($categories as $i => $item)
                <option value="{{ $item->id }}" {{ @$client_vendor_ticket->category_id == $item->id? 'selected' : '' }}>
                    {{ $item->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label for="priority">Priority</label>
        <select name="priority" id="priority" class="custom-select">
            @foreach (['Low', 'Medium', 'High'] as $i => $value)
                <option value="{{ $value }}" {{ @$client_vendor_ticket->priority == $value? 'selected' : '' }}>
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