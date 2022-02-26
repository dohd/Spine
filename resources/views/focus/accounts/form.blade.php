<div class="row">
    <div class="col-md-6">

        <div class='form-group'>
            {{ Form::label( 'account_type', trans('accounts.account_type')) }}
            <div class='col'>
                <select name="account_type" class="form-control" id="accType" required>
                    <option value="">-- Select Account Type --</option>
                    @foreach($account_types as $k => $row)
                        <option 
                            value="{{ $row->category }}" 
                            key="{{ $row->id }}" 
                            {{ $row->id == @$account->account_type_id ? 'selected' : '' }}
                        >
                            {{ $k+1 }}. {{ $row->name }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="account_type_id" id="accTypeId">
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class='form-group'>
            {{ Form::label( 'number', trans('accounts.number')) }}
            <div class='col'>
                @if (isset($account))
            {{ Form::text('number', null, ['class' => 'form-control box-size', 'required', 'readonly']) }}
        @else
            {{ Form::text('number', 1, ['class' => 'form-control box-size', 'id'=>'account_number', 'required', 'readonly']) }}
        @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">

        <div class='form-group'>
            {{ Form::label( 'holder', 'Account Name') }}
            <div class='col'>
                {{ Form::text('holder', null, ['class' => 'form-control box-size', 'placeholder' => 'Account Name *','required'=>'required']) }}
   
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class='form-group'>
            {{ Form::label( 'number','Can Be Used In Manual Journal') }}
            <div class='col'>
                <select name="is_manual_journal" class="form-control" required id="category" required>
                    <option value="">-- Select If Account Can Be Used In Manual Journal  --</option>
              
                        <option value="0" >No </option>
                        <option value="1" >Yes </option>
             
                </select>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">

        <div class='form-group'>
            {{ Form::label( 'is_parent','Is This Account Sub-Category') }}
            <div class='col'>
                <select name="is_parent" class="form-control" id="is_parent" required>
                    <option value="">-- Select If Account is Sub-Category --</option>
              
                        <option value="0" >No </option>
                        <option value="1" >Yes </option>
             
                </select>
            
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class='form-group'>
            {{ Form::label( 'number','Category') }}
            <div class='col'>
                {!! Form::select('category_id', $account_category,  null, ['disabled'=>'disabled','placeholder' => 'Select Category', 'class' => 'form-control ','id'=>'category_id']); !!}
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class='form-group'>
            {{ Form::label( 'opening_balance', trans('accounts.balance')) }}
            <div class='col'>
                {{ Form::text('opening_balance', null, ['class' => 'form-control', 'placeholder' => trans('accounts.balance'),'onkeypress'=>"return isNumber(event)", 'id'=>'balance']) }}
            </div>
        </div>
    </div>

    <div class="col-md-6">

        <div class='form-group'>
            {{ Form::label( 'opening_balance_date', 'Date') }}
            <div class='col'>
                {{ Form::text('opening_balance_date', null, ['class' => 'form-control datepicker', 'placeholder' => 'Date*','id'=>'opening_balance_date', 'required'=>'required']) }}
   
            </div>
        </div>
    </div>
  
</div>

<div class="row">
    <div class="col-md-12">
        <div class='form-group'>
            {{ Form::label( 'balance', trans('accounts.note')) }}
            <div class='col'>
                {{ Form::text('note', null, ['class' => 'form-control box-size', 'placeholder' => trans('accounts.note')]) }}
            </div>
        </div>
    </div>


  
</div>



@section("after-scripts")
<script>
    // on selecting account type
    $('#accType').change(function() {
        const key = $(this).find('option:selected').attr('key');
        $('#accTypeId').val(key);
        const account_type=$(this).val();

        $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


            $.ajax({
            url: "{{ route('biller.accounts.search_next_account_no') }}",
            type: 'POST',
            dataType: 'json',
            data: { account_type },
            success: function(data) {

                $('#account_number').val(data.account_number);

            }
        });


      //  console.log(key);
    });
    // on update page
    if (@json(@$account)) {
        const key = $('#accType option:selected').attr('key');
        $('#accTypeId').val(key);
    }


    $('#is_parent').on('change', function() {
        

   
  $('#category_id').prop('disabled', true);
  if($(this).val()==1){
    console.log($(this).val());
    $('#category_id').prop('disabled', false);

  }
  
  return false;
})


    // Initialize datepicker
    $('.datepicker').datepicker({ format: "{{config('core.user_date_format')}}"})    
    $('#opening_balance_date').datepicker('setDate', new Date());

    //number format
    $("#balance").change(function(){
        const input_val=$(this).val();
 $("#balance").val(accounting.formatNumber(input_val));

});
</script>
@endsection





