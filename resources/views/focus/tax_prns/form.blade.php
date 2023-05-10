<div class="form-group row">
<<<<<<< HEAD
    <div class="col-3">
        <label for="period_from">Return Period From</label>
        {{ Form::text('period_from', null, ['class' => 'form-control datepicker', 'id' => 'period_from', 'required']) }}
    </div> 
    <div class="col-3">
        <label for="period_to">Return Period To</label>
        {{ Form::text('period_to', null, ['class' => 'form-control datepicker', 'id' => 'period_to', 'required']) }}
    </div> 
    <div class="col-2">
        <label for="ackn_date">Acknowledgement Date</label>
        {{ Form::text('ackn_date', null, ['class' => 'form-control datepicker', 'id' => 'ackn_date', 'required']) }}
    </div> 
    <div class="col-2">
        <label for="return_no">Return Number</label>
        {{ Form::text('return_no', null, ['class' => 'form-control', 'id' => 'return_no', 'required']) }}
    </div> 
    <div class="col-2">
        <label for="search_code">Search Code</label>
        {{ Form::text('search_code', null, ['class' => 'form-control', 'id' => 'search_code', 'required']) }}
=======
    <div class="col-6">
        <label for="return_month">Return Month</label>
        {{ Form::text('return_month', @$prev_month, ['class' => 'form-control datepicker', 'id' => 'return_month', 'required']) }}
    </div> 
    <div class="col-6">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required']) }}
>>>>>>> tax-prn-fix
    </div> 
</div>

<div class="form-group row">
<<<<<<< HEAD
    <div class="col-3">
        <label for="prn_no">PRN Number</label>
        {{ Form::text('prn_no', null, ['class' => 'form-control', 'id' => 'prn_no', 'required']) }}
    </div> 
    <div class="col-3">
        <label for="date">PRN Date</label>
        {{ Form::text('prn_date', null, ['class' => 'form-control datepicker', 'id' => 'prn_date', 'required']) }}
    </div> 
    <div class="col-2">
=======
    <div class="col-6">
        <label for="prn_code">PRN Code</label>
        {{ Form::text('code', null, ['class' => 'form-control', 'id' => 'prn_code', 'required']) }}
    </div> 
    <div class="col-3">
>>>>>>> tax-prn-fix
        <label for="mode">Payment Mode</label>
        <select name="payment_mode" id="payment_mode" class="custom-select">
            @foreach (['eft', 'rtgs','cash', 'mpesa', 'cheque'] as $val)
                <option value="{{ $val }}">{{ strtoupper($val) }}</option>
            @endforeach
        </select>
    </div> 
<<<<<<< HEAD
    <div class="col-2">
        <label for="prn_code">Payment Reference</label>
        {{ Form::text('payment_ref', null, ['class' => 'form-control', 'id' => 'ref', 'required']) }}
    </div> 
    <div class="col-2">
=======
    <div class="col-3">
>>>>>>> tax-prn-fix
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
<<<<<<< HEAD
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());

            if (this.taxPrn) {
                const prn = this.taxPrn;
                ['period_from', 'period_to', 'ackn_date', 'prn_date'].forEach(v => {
                    $('#'+v).datepicker('setDate', new Date(prn[v]));
                });
                $('#payment_mode').val(prn.payment_mode);
                $('#amount').val(accounting.formatNumber(prn.amount*1));
=======
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
>>>>>>> tax-prn-fix
            }
        },
    };

    $(() => Form.init());
</script>
@endsection