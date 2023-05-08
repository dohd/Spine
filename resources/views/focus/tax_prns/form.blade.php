<div class="form-group row">
    <div class="col-6">
        <label for="period_from">Period From</label>
        {{ Form::text('period_from', null, ['class' => 'form-control datepicker', 'id' => 'period_from', 'required']) }}
    </div> 
    <div class="col-6">
        <label for="period_to">Period To</label>
        {{ Form::text('period_to', null, ['class' => 'form-control datepicker', 'id' => 'period_to', 'required']) }}
    </div> 
</div>

<div class="form-group row">
    <div class="col-6">
        <label for="date">Acknowledgement Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
    </div> 
    <div class="col-6">
        <label for="prn_code">Return Number</label>
        {{ Form::text('code', null, ['class' => 'form-control', 'id' => 'prn_code', 'required']) }}
    </div> 
</div>

<div class="form-group row">
    <div class="col-6">
        <label for="mode">Payment Mode</label>
        <select name="payment_mode" id="payment_mode" class="custom-select">
            @foreach (['eft', 'rtgs','cash', 'mpesa', 'cheque'] as $val)
                <option value="{{ $val }}">{{ strtoupper($val) }}</option>
            @endforeach
        </select>
    </div> 
    <div class="col-6">
        <label for="amount">Payment Amount</label>
        {{ Form::text('amount', null, ['class' => 'form-control', 'id' => 'amount', 'required']) }}
    </div> 
</div>

<div class="form-group row">
    <div class="col-12">
        <label for="note">Remark</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note']) }}
    </div> 
</div>

<div class="form-group row no-gutters">
    <div class="col-1 ml-auto">
        <a href="{{ route('biller.tax_prns.index') }}" class="btn btn-danger block">Cancel</a>    
    </div>
    <div class="col-1 ml-1">
        {{ Form::submit(@$tax_prn? 'Update' : 'Generate', ['class' => 'form-control btn btn-primary']) }}
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
        taxPrn: @json(@$tax_prn),

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());

            if (this.taxPrn) {
                const prn = this.taxPrn;
                $('#date').datepicker('setDate', new Date(prn.date));
                $('#period_from').datepicker('setDate', new Date(prn.period_from));
                $('#period_to').datepicker('setDate', new Date(prn.period_to));
                $('#payment_mode').val(prn.payment_mode);
                $('#amount').val(accounting.formatNumber(prn.amount*1));
            }
        },
    };

    $(() => Form.init());
</script>
@endsection