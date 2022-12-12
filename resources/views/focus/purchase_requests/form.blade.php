<div class="form-group row">
    <div class="col-2">
        <label for="title">Requisition No.</label>
        {{ Form::text('tid', gen4tid('REQ-', @$purchase_request? $purchase_request->tid : @$tid+1), ['class' => 'form-control', 'disabled']) }}
        {{ Form::hidden('tid', @$purchase_request? $purchase_request->tid : @$tid+1) }}
    </div>

    <div class="col-4">
        <label for="employee">Requestor</label>
        <select name="employee_id" id="user" class="form-control" data-placeholder="Search Employee" required>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" {{ @$purchase_request->employee_id == $user->id? 'selected' : '' }}>
                    {{ $user->full_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-2">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
    </div>

    <div class="col-2">
        <label for="priority">Priority Level</label>
        <select name="priority" id="priority" class="custom-select">
            @foreach (['low', 'medium', 'high'] as $val)
                <option value="{{ $val }}" {{ @$purchase_request->priority == $val? 'selected' : '' }}>
                    {{ ucfirst($val) }}
                </option>
            @endforeach
        </select>
    </div>   

    <div class="col-2">
        <label for="expect_date">Expected Delivery Date</label>
        {{ Form::text('expect_date', null, ['class' => 'form-control datepicker', 'id' => 'expect_date']) }}
    </div>    
</div>

<div class="form-group row">
    <div class="col-12">
        <label for="item_descr">Item List Description</label>
        {{ Form::textarea('item_descr', null, ['class' => 'form-control html_editor', 'id' => 'item_descr', 'required']) }}
    </div>
</div>

<div class="form-group row">
    <div class="col-6">
        <label for="title">Remark</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note']) }}
    </div>
</div>

<div class="form-group row no-gutters">
    <div class="col-1 ml-auto">
        <a href="{{ route('biller.purchase_requests.index') }}" class="btn btn-danger block">Cancel</a>    
    </div>
    <div class="col-1 ml-1">
        @php
            $disabled = '';
            if (isset($purchase_request) && $purchase_request->status == 'approved')
                $disabled = 'disabled';
        @endphp
        {{ Form::submit(@$purchase_request? 'Update' : 'Create', ['class' => 'form-control btn btn-primary text-white', $disabled]) }}
    </div>
</div>

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Form = {
        purchaseRequest: @json(@$purchase_request),

        init() {
            $.ajaxSetup(config.ajax);
            $('#user').select2({allowClear: true});
            $('.datepicker').datepicker(config.date)
            // initialize html editor
            editor();

            if (this.purchaseRequest) {
                const request = this.purchaseRequest;
                $('#date').datepicker('setDate', new Date(request.date));
                $('#expect_date').datepicker('setDate', new Date(request.expect_date));
            } else {
                $('#user').val('').change();
                $('.datepicker').datepicker('setDate', new Date());
            }
            $('#user').change(this.employeeChange);
        },
    };

    $(() => Form.init());
</script>
@endsection