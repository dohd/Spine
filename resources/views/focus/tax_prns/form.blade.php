<div class="form-group row">
    <div class="col-6">
        <label for="return_month">Return Month</label>
        {{ Form::text('return_month', @$prev_month, ['class' => 'form-control datepicker', 'id' => 'return_month', 'required']) }}
    </div> 
    <div class="col-6">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
    </div> 
</div>

<div class="form-group row">
    <div class="col-6">
        <label for="prn_code">PRN Code</label>
        {{ Form::text('code', null, ['class' => 'form-control', 'id' => 'prn_code', 'required']) }}
    </div> 
    <div class="col-3">
        <label for="mode">Payment Mode</label>
        <select name="payment_mode" id="payment_mode" class="custom-select">
            @foreach (['eft', 'rtgs','cash', 'mpesa', 'cheque'] as $val)
                <option value="{{ $val }}">{{ strtoupper($val) }}</option>
            @endforeach
        </select>
    </div> 
    <div class="col-3">
        <label for="amount">Payment Amount</label>
        {{ Form::text('amount', null, ['class' => 'form-control', 'id' => 'amount', 'required']) }}
    </div> 
    
</div>


<div class="form-group row no-gutters">
    <div class="col-1 ml-auto">
        <a href="{{ route('biller.tax_prns.index') }}" class="btn btn-danger block">Cancel</a>    
    </div>
    <div class="col-1 ml-1">
        {{ Form::submit(@$tax_prn? 'Update' : 'Create', ['class' => 'form-control btn btn-primary']) }}
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
            // month picker
            $('#return_month').datepicker({
                autoHide: true,
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                format: 'MM-yyyy',
                onClose: function(dateText, inst) { 
                    $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
                }
            });
            $('#date').datepicker(config.date).datepicker('setDate', new Date());

            if (this.taxPrn) {
                const taxPrn = this.taxPrn;
                $('#date').datepicker('setDate', new Date(taxPrn.date));
                $('#payment_mode').val(taxPrn.payment_mode);
                $('#amount').val(accounting.formatNumber(taxPrn.amount*1));
            }
        },
    };

    $(() => Form.init());
</script>
@endsection