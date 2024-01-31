<div class="form-group row">
    <div class="col-md-3">
        <label for="priority">Priority</label>
        <select name="priority" id="priority" class="custom-select">
            @foreach (['Low', 'Medium', 'High'] as $i => $value)
                <option value="{{ $value }}" {{ @$client_vendor_ticket->priority == $value? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label for="tag">Ticket Level</label>
        <select name="tag_id" id="tag" class="custom-select" required>
            <option value="">-- Select Level --</option>
            @foreach ($tags as $i => $item)
                <option value="{{ $item->id }}" {{ @$client_vendor_ticket->tag_id == $item->id? 'selected' : '' }}>
                    {{ $item->name }}
                </option>
            @endforeach
        </select>
    </div>
    
    <div class="col-md-3">
        <label for="category">Equipment Category</label>
        <select name="equip_categ_id" id="equip_category" class="custom-select" required>
            <option value="">-- Select Category --</option>
            @foreach ($equip_categories as $i => $item)
                <option value="{{ $item->id }}" {{ @$client_vendor_ticket->equip_categ_id == $item->id? 'selected' : '' }}>
                    {{ $item->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label for="location" class="caption">Equipment Location</label>
        <div class="input-group">
            <div class="w-100">
                {{ Form::text('equip_location', null, ['class' => 'form-control']) }}
            </div>
        </div>
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