<div class="form-group row">
    <div class="col-3">
        <label for="employee">Applicant</label>
        <select name="employee_id" id="user" class="form-control" data-placeholder="Search Employee" required>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" {{ @$advance_payment && $advance_payment->employee_id == $user->id? 'selected' : '' }}>
                    {{ $user->first_name }} {{ $user->last_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-2">
        <label for="amount">Amount</label>
        {{ Form::text('amount', null, ['class' => 'form-control', 'id' => 'amount', 'required']) }}
    </div>
    <div class="col-2">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker']) }}
    </div>
</div>

<div class="form-group row no-gutters mt-5">
    <div class="col-1">
        <a href="{{ route('biller.advance_payments.index') }}" class="btn btn-danger block">Cancel</a>    
    </div>
    <div class="col-1 ml-1">
        {{ Form::submit(@$advance_payment? 'Update' : 'Create', ['class' => 'form-control btn btn-primary']) }}
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
        payment: @json(@$advance_payment),

        init() {
            $.ajaxSetup(config.ajax);
            $('#user').select2({allowClear: true});
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#amount').change(this.amountChange);

            if (this.payment) {
                $('#amount').val(accounting.formatNumber(this.payment.amount));
                $('.datepicker').datepicker('setDate', new Date(this.payment.date));
            } else {
                $('#user').val('').change();
            }
        },

        amountChange() {
            const val = accounting.unformat($(this).val());
            $(this).val(accounting.formatNumber(val));
        },
    };

    $(() => Index.init());
</script>
@endsection