<div class='row'>
    <div class='form-group col-4'>
        <div><label for="bank">Loan Lender<span class="text-danger">*</span></label></div>
        
        {!! Form::select('lender_id', $lenders, null, [
            'placeholder' => '-- Select Lender --',
            'class' => 'form-control round',
            'id' => 'lender_id',
            'required' => 'required',
        ]) !!}


       
    </div> 
    <div class='form-group col-4'>
        <div><label for="lending_type">Lending Type<span class="text-danger">*</span></label></div>
        {!! Form::select('lending_type', ['Borrow From Business'=>'Borrow From Business','Lend To Business'=>'Lend To Business'], null, [
            'placeholder' => '-- Select Lending Type --',
            'class' => 'form-control round',
            'id' => 'lending_type',
            'required' => 'required',
        ]) !!}
    </div> 

 
    <div class='form-group col-4'>
        <div><label for="bank_id">Bank Account<span class="text-danger">*</span></label></div>
        {!! Form::select('bank_id', $accounts, null, [
            'placeholder' => '-- Select Lending Type --',
            'class' => 'form-control round',
            'id' => 'bank_id',
            'required' => 'required',
        ]) !!}
    </div> 
</div>

<div class='row'>
    <div class='form-group col-3'>
        <div><label for="tid">Loan ID<span class="text-danger">*</span></label></div>
        {{ Form::text('tid', $last_tid+1, ['class' => 'form-control round', 'readonly']) }}
    </div>
    <div class='form-group col-3'>
        <div><label for="date">Date Borrowed<span class="text-danger">*</span></label></div>
        <input type="text" name="date" class="form-control datepicker round">
    </div>
    <div class='form-group col-3'>
        <div><label for="payment_day">Payment Day</label></div>
        {!! Form::select('payment_day', $payday, null, [
            'placeholder' => '-- Pay Day --',
            'class' => 'form-control round',
            'id' => 'payment_day'
        ]) !!}

   
    </div>
    <div class='form-group col-3'>
        <div><label for="date">Loan Period (months)<span class="text-danger">*</span></label></div>
        <input type="number" name="time_pm" class="form-control round">
    </div>

  
</div>

<div class='row'>
    <div class='form-group col-3'>
        <div><label for="amount">Principal Amount<span class="text-danger">*</span></label></div>
        {{ Form::text('amount', null, ['class' => 'form-control round', 'required']) }}
    </div>
    <div class='form-group col-3'>
        <div><label for="loan_fees">Processing Fee</label></div>
        {{ Form::text('loan_fees', null, ['class' => 'form-control round']) }}
    </div>
 
    <div class='form-group col-3'>
        <div><label for="date">Installment (Paid Monthly)<span class="text-danger">*</span></label></div>
        {{ Form::number('loan_inst', null, ['class' => 'form-control round', 'required']) }}


       
    </div>
    
    <div class='form-group col-3'>
        <div><label for="amount">Interest (Paid Monthly)</label></div>
        {{ Form::text('loan_interest', null, ['class' => 'form-control round', 'required']) }}
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