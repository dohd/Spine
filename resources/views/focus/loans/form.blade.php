<div class='row'>
    <div class='form-group col-5'>
        <div><label for="bank">Lender</label></div>
        <select name="lender_id" class='form-control round' required>
            <option value="">-- Select Lender --</option>
            @foreach($accounts as $account)
                @if ($account->account_type_id == 2)
                    <option value="{{ $account['id'] }}">
                        {{ $account['holder'] }}
                    </option>
                @endif
            @endforeach
        </select>
    </div> 
    <div class='form-group col-6'>
        <div><label for="expense">Account</label></div>
        <select name="bank_id" class='form-control round' required>
            <option value="">-- Select Account --</option>
            @foreach($accounts as $account)
                @if ($account->account_type_id == 7)
                    <option value="{{ $account['id'] }}">
                        {{ $account['holder'] }}
                    </option>
                @endif
            @endforeach
        </select>
    </div> 
</div>

<div class='row'>
    <div class='form-group col-2'>
        <div><label for="tid">Loan ID</label></div>
        {{ Form::text('tid', @$last_loan->tid+1, ['class' => 'form-control round', 'readonly']) }}
    </div>
    <div class='form-group col-3'>
        <div><label for="date">Date</label></div>
        <input type="text" name="date" class="form-control datepicker round">
    </div>
    <div class='form-group col-2'>
        <div><label for="date">Loan Period (months)</label></div>
        <input type="number" name="time_pm" class="form-control round">
    </div>
    <div class='form-group col-2'>
        <div><label for="amount">Amount</label></div>
        {{ Form::text('amount', null, ['class' => 'form-control round', 'required']) }}
    </div>
    <div class='form-group col-2'>
        <div><label for="amount">Amount Payable (per month)</label></div>
        {{ Form::text('amount_pm', null, ['class' => 'form-control round', 'required']) }}
    </div>
</div>

<div class='row'>
    <div class='form-group col-8'>
        <div><label for="note">Note</label></div>
        {{ Form::text('note', null, ['class' => 'form-control round', 'required']) }}
    </div>
</div>

<div class="row">
    <div class="col-2 ml-auto mr-5">
        {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-lg block round']) }}
    </div>
</div>

@section("after-scripts")
<script type="text/javascript">
    $('.datepicker')
    .datepicker({
        autoHide: true,
        format: "{{ config('core.user_date_format') }}"
    })
    .datepicker('setDate', new Date());
</script>
@endsection