<div class='row'>
       <div class='col-md-3'>
        <div class='form-group'>
            {{ Form::label( 'tid', 'Transaction ID',['class' => 'col-6 control-label']) }}
            <div class='col'>
                {{ Form::text('tid', @$last_id->tid+1, ['class' => 'form-control round required', 'placeholder' => trans('general.note'),'autocomplete'=>'off','readonly']) }}
            </div>
        </div>
    </div>

     <div class='col-md-3'>
      <div class='form-group'>
            {{ Form::label( 'transaction_type', 'WithHolding  Type ',['class' => 'col-12 control-label']) }}
            <div class="col">
                <select name="transaction_type" class='form-control round required'>
                   
                        <option value="wht_vat">VAT (2%)</option>
                        <option value="wht_proffesional">Proffesional (5%)</option>
                        <option value="wht_management">Management (3)</option>
                    
                </select></div>
        </div> 
    </div>

    <div class='col-md-3'>
        <div class='form-group'>
            {{ Form::label( 'account_id', 'Select Invoice (Account Credited)',['class' => 'col-12 control-label']) }}
            <div class="col">
                <select name="account_id" id="invoice_id" class='form-control round required'>
                    @foreach($sales as $sale)
                        <option value="{{$sale['id']}}">{{$sale->customer->company.' INV '.$sale->refer_no}}</option>
                    @endforeach
                </select></div>
        </div> 


      
    </div>
 


       <div class='col-md-3'>
      <div class='form-group'>
            {{ Form::label( 'debited_account_id', 'Tax  Account (Debited)',['class' => 'col-12 control-label']) }}
            <div class="col">
                <select name="debited_account_id" class='form-control round required'>
                   
                        @foreach($whts as $account)
                        <option value="{{$account['id']}}">{{$account->holder}}</option>
                    @endforeach
                    
                </select></div>
        </div> 
    </div>
</div>
<div class='row'>

  
    
  
    <div class='col-md-6'>
        <div class='form-group'>
            {{ Form::label( 'refer_no', 'Certificate S/N',['class' => 'col-6 control-label']) }}
            <div class='col'>
                {{ Form::text('refer_no', null, ['class' => 'form-control round', 'placeholder' => 'Certificate S/N','autocomplete'=>'off']) }}
            </div>
        </div>
    </div>
    <div class='col-md-3'>
        <div class='form-group'>
            {{ Form::label( 'due_date','Certificate Date',['class' => 'col control-label']) }}
            <div class='col-12'>
                <fieldset class="form-group position-relative has-icon-left">
                    <input type="text" class="form-control round required"
                           placeholder="Certificate Date*" name="due_date"
                           data-toggle="datepicker" required="required">
                    <div class="form-control-position">
                      <span class="fa fa-calendar"
                            aria-hidden="true"></span>
                    </div>

                </fieldset>
            </div>
        </div>
    </div>
      <div class='col-md-3'>
        <div class='form-group'>
            {{ Form::label( 'transaction_date','Transaction Date',['class' => 'col control-label']) }}
            <div class='col-12'>
                <fieldset class="form-group position-relative has-icon-left">
                    <input type="text" class="form-control round required"
                           placeholder="Transaction Date*" name="transaction_date"
                           data-toggle="datepicker" required="required">
                    <div class="form-control-position">
                      <span class="fa fa-calendar"
                            aria-hidden="true"></span>
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
                {{ Form::text('amount', numberFormat(0), ['class' => 'form-control round required', 'placeholder' => trans('transactions.debit').'*','required'=>'required','onkeypress'=>"return isNumber(event)"]) }}</div>
        </div>
    </div>
     
   

    <div class='col-md-9'>
        <div class='form-group'>
            {{ Form::label( 'refer_no', trans('general.note'),['class' => 'col-6 control-label']) }}
            <div class='col'>
                {{ Form::text('note', null, ['class' => 'form-control round', 'placeholder' => trans('general.note'),'autocomplete'=>'off']) }}
            </div>
        </div>
    </div>
  

</div>

@section("after-styles")
 {!! Html::style('focus/css/select2.min.css') !!}
@endsection


@section("after-scripts")
    {{ Html::script('focus/js/select2.min.js') }}
    <script type="text/javascript">
         $("#invoice_id").select2();
        $(document).ready(function () {
            $('[data-toggle="datepicker"]').datepicker({
                autoHide: true,
                format: '{{config('core.user_date_format')}}'
            });
            $('[data-toggle="datepicker"]').datepicker('setDate', '{{date(config('core.user_date_format'))}}');
        });

        
    </script>
@endsection
