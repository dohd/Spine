<div class='row'>
    <div class='col-md-6'>
        <div class='form-group'>
            {{ Form::label('account_id', 'Transfer From Account',['class' => 'col-12 control-label']) }}
            <div class="col">
                <select name="account_id" class='form-control round' required>
                    <option value="">-- select account --</option>
                    @foreach($accounts as $row)
                        <option value="{{ $row->id }}" {{ @$banktransfer->account_id == $row->id? 'selected' : '' }}>
                            {{ $row->holder }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class='col-md-2'>
        <div class='form-group'>
            {{ Form::label('tid', 'Transfer No',['class' => 'col-12 control-label']) }}
            <div class='col'>
                {{ Form::text('tid', @$banktransfer? $banktransfer->tid : $tid+1, ['class' => 'form-control round required', 'placeholder' => trans('general.note'),'autocomplete'=>'off','readonly']) }}
            </div>
        </div>
    </div>

    <div class='col-md-4'>
        <div class='form-group'>
            {{ Form::label('transaction_date', 'Transaction Date', ['class' => 'control-label']) }}
            <div class='col'>
                <fieldset class="form-group position-relative has-icon-left">
                    <input type="text" class="form-control round datepicker" placeholder="{{trans('general.payment_date')}}*" name="transaction_date">
                    <div class="form-control-position">
                        <span class="fa fa-calendar" aria-hidden="true"></span>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
</div>

<div class='row'>
    <div class='col-md-6'>
        <div class='form-group'>
            {{ Form::label('debit_account_id', 'Receive on Account',['class' => 'col-12 control-label']) }}
            <div class="col">                
                <select name="debit_account_id" class='form-control round' required>
                    <option value="">-- select account --</option>
                    @foreach($accounts as $row)
                        <option value="{{ $row->id }}" {{ @$banktransfer->debit_account_id == $row->id? 'selected' : '' }}>
                            {{ $row->holder }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>  
    </div>
    
    <div class='col-md-2'>
        <div class='form-group'>
            {{ Form::label( 'method', 'Transaction Method', ['class' => 'col-12 control-label']) }}
            <div class="col">
                <select name="method" class='col form-control round'>
                    @foreach(['Cash', 'Mobile Money', 'EFT', 'RTGS', 'Cheque'] as $val)
                        <option value="{{ $val }}" {{ @$pmt_mode == $val? 'selected' : ''}}>{{ $val }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class='col-md-2'>
        <div class='form-group'>
            {{ Form::label( 'refer_no', 'Reference No',['class' => 'col-12 control-label']) }}
            <div class='col'>
                {{ Form::text('refer_no', null, ['class' => 'form-control round', 'placeholder' => 'Reference No', 'id' => 'refer_no']) }}
            </div>
        </div>
    </div>

    <div class='col-md-2'>
        <div class='form-group'>
            {{ Form::label( 'amount', 'Amount', ['class' => 'col-12 control-label']) }}
            <div class="col">
                {{ Form::text('amount', null, ['class' => 'form-control round required', 'placeholder' => 'Amount', 'id' => 'amount', 'required']) }}
            </div>
        </div>
    </div>
</div>

<div class='row'>   
    <div class='col-md-12'>
        <div class='form-group'>
            {{ Form::label( 'note', trans('general.note'),['class' => 'col-12 control-label']) }}
            <div class='col'>
                {{ Form::text('note', null, ['class' => 'form-control round', 'placeholder' => trans('general.note'), 'id' => 'note']) }}
            </div>
        </div>
    </div>
</div>


@section("after-scripts")
<script type="text/javascript">
    const config = {
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    }

    $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
    $('#amount').focusout(function() {
        const amount = accounting.unformat($(this).val());
        $(this).val(accounting.formatNumber(amount));
    });
    
    const banktransfer = @json(@$banktransfer);
    if (banktransfer) {
        $('.datepicker').datepicker('setDate', new Date(banktransfer.transaction_date));
        $('#amount').val(banktransfer.amount).focusout();
        $('#refer_no').val(banktransfer.refer_no);
        $('#note').val(banktransfer.note);
    }
</script>
@endsection