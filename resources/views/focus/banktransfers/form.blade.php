<div class='row'>
    <div class='col-md-6'>
        <div class='form-group'>
            {{ Form::label('account_id', 'From Account (Credited)',['class' => 'col-12 control-label']) }}
            <div class="col">
                <select name="account_id" class='form-control round' required>
                    <option value="">-- Select Account --</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account['holder'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class='col-md-6'>
        <div class='form-group'>
            {{ Form::label('debit_account_id', 'To   Account (Debited)',['class' => 'col-12 control-label']) }}
            <div class="col">                
                <select name="debit_account_id" class='form-control round' required>
                    <option value="">-- Select Account --</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account['holder'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
<div class='row'>
    <div class='col-md-3'>
        <div class='form-group'>
            {{ Form::label('tid', 'Transaction ID',['class' => 'col-6 control-label']) }}
            <div class='col'>
                {{ Form::text('tid', $last_tid+1, ['class' => 'form-control round required', 'placeholder' => trans('general.note'),'autocomplete'=>'off','readonly']) }}
            </div>
        </div>
    </div>
    <div class='col-md-3'>
        <div class='form-group'>
            {{ Form::label( 'method', trans('transactions.method'),['class' => 'col-12 control-label']) }}
            <div class="col">
                <select name="method" class='col form-control round'>
                    @foreach(payment_methods() as $payment_method)
                    <option value="{{$payment_method}}">{{$payment_method}}</option>
                    @endforeach
                    <option value="Card">Card</option>
                </select>
            </div>
        </div>
    </div>
    <div class='col-md-6'>
        <div class='form-group'>
            {{ Form::label('transaction_date', 'Transaction Date', ['class' => 'col control-label']) }}
            <div class='col-6'>
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
    <div class='col-md-3'>
        <div class='form-group'>
            {{ Form::label( 'amount', 'Amount',['class' => 'col control-label']) }}
            <div class="col">
                {{ Form::text('amount', numberFormat(0), ['class' => 'form-control round required', 'placeholder' => trans('transactions.debit').'*','required'=>'required','onkeypress'=>"return isNumber(event)"]) }}
            </div>
        </div>
    </div>
    <div class='col-md-3'>
        <div class='form-group'>
            {{ Form::label( 'refer_no', 'Voucher Number',['class' => 'col-6 control-label']) }}
            <div class='col'>
                {{ Form::text('refer_no', null, ['class' => 'form-control round', 'placeholder' => 'Voucher No','autocomplete'=>'off']) }}
            </div>
        </div>
    </div>
    <div class='col-md-6'>
        <div class='form-group'>
            {{ Form::label( 'note', trans('general.note'),['class' => 'col-6 control-label']) }}
            <div class='col'>
                {{ Form::text('note', null, ['class' => 'form-control round', 'placeholder' => trans('general.note'),'autocomplete'=>'off']) }}
            </div>
        </div>
    </div>
</div>


@section("after-scripts")
<script type="text/javascript">
    // datepicker
    $('.datepicker').datepicker({format: "{{config('core.user_date_format')}}", autoHide: true})
    .datepicker('setDate', new Date());

</script>
@endsection