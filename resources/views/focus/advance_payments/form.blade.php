<div class="form-group row">
    <div class="col-3">
        <label for="employee">Leave Applicant</label>
        <select name="employee_id" id="user" class="form-control" data-placeholder="Search Employee" required>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" {{ @$leave && $leave->employee_id == $user->id? 'selected' : '' }}>
                    {{ $user->first_name }} {{ $user->last_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-2">
        <label for="category">Leave Category</label>
        <select name="leave_category_id" id="leave_category" class="custom-select">
            <option value="">-- select leave category --</option>
            @isset($leave)
                <option value="{{ $leave->leave_category_id }}" selected>{{ $leave->leave_category->title }}</option>
            @endisset
        </select>
    </div>
    <div class="col-2">
        <label for="title">Viable Leave Days</label>
        {{ Form::text('viable_qty', null, ['class' => 'form-control', 'id' => 'viable_days', 'readonly']) }}
    </div>
    <div class="col-2">
        <label for="days">Leave Start Date</label>
        {{ Form::text('start_date', null, ['class' => 'form-control datepicker', 'id' => 'start_date']) }}
    </div>
    <div class="col-2">
        <label for="qty">Leave Duration (Days)</label>
        {{ Form::number('qty', null, ['class' => 'form-control', 'min' => '1', 'id' => 'qty', 'required']) }}
    </div>    
</div>

<div class="form-group row">
    <div class="col-12">
        <label for="title">Reason for Leave Request</label>
        {{ Form::text('reason', null, ['class' => 'form-control', 'id' => 'title', 'required']) }}
    </div>
</div>

<div class="form-group row">
    <div class="col-3">
        <label for="assistant">Duties Delegated To</label>
        <select name="assist_employee_id" id="assist_user" class="form-control" data-placeholder="Search Employee">
            @foreach ($users as $user)
                <option value="{{ $user->id }}" {{ @$leave && $leave->assist_employee_id == $user->id? 'selected' : '' }}>
                    {{ $user->first_name }} {{ $user->last_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group row no-gutters">
    <div class="col-1 ml-auto">
        <a href="{{ route('biller.leave.index') }}" class="btn btn-danger block">Cancel</a>    
    </div>
    <div class="col-1 ml-1">
        {{ Form::submit(@$leave? 'Update' : 'Create', ['class' => 'form-control btn btn-primary']) }}
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
        leave: @json(@$leave),

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#leave_category').change(this.leaveCategoryChange);
            $('#user').select2({allowClear: true});
            $('#assist_user').select2({allowClear: true});

            if (this.leave) {
                $('#start_date').datepicker('setDate', new Date(this.leave.start_date));
                $('#leave_category').val(this.leave.leave_category_id);
            } else {
                $('#user').val('').change();
                $('#assist_user').val('').change();
            }
            $('#user').change(this.employeeChange);
            $('#qty').change(this.leaveQtyChange);
        },

        leaveQtyChange() {
            const qty = accounting.unformat($(this).val());
            const viableDays = accounting.unformat($('#viable_days').val());
            if (qty > viableDays) $(this).val(viableDays);
        },

        employeeChange() {
            $('#viable_days').val('');
            $('#leave_category option:not(:eq(0))').remove();
            if (!$(this).val()) return; 

            const url = "{{ route('biller.leave.leave_categories') }}";
            $.post(url, {employee_id: $(this).val()}, data => {
                data.forEach(v => {
                    const opt = `<option value="${v.id}" category_qty="${v.qty}">${v.title}</option>`;
                    $('#leave_category').append(opt);
                });
            });
        },

        leaveCategoryChange() {
            const days = $(this).find(':selected').attr('category_qty');
            $('#viable_days').val(days);
        },
    };

    $(() => Index.init());
</script>
@endsection