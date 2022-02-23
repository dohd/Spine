<div class='form-group'>
    {{ Form::label( 'number', trans('accounts.number'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        @if (isset($account))
            {{ Form::text('number', null, ['class' => 'form-control box-size', 'required', 'readonly']) }}
        @else
            {{ Form::text('number', 1, ['class' => 'form-control box-size', 'required', 'readonly']) }}
        @endif
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'holder', trans('accounts.holder'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('holder', null, ['class' => 'form-control box-size', 'placeholder' => trans('accounts.holder').'*','required'=>'required']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'balance', trans('accounts.balance'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('balance', null, ['class' => 'form-control box-size', 'placeholder' => trans('accounts.balance'),'onkeypress'=>"return isNumber(event)"]) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'code', trans('accounts.code'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('code', null, ['class' => 'form-control box-size', 'placeholder' => trans('accounts.code')]) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'account_type', trans('accounts.account_type'), ['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
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
<div class='form-group'>
    {{ Form::label( 'note', trans('accounts.note'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('note', null, ['class' => 'form-control box-size', 'placeholder' => trans('accounts.note')]) }}
    </div>
</div>

@section("after-scripts")
<script>
    // on selecting account type
    $('#accType').change(function() {
        const key = $(this).find('option:selected').attr('key');
        $('#accTypeId').val(key);
    });
    // on update page
    if (@json(@$account)) {
        const key = $('#accType option:selected').attr('key');
        $('#accTypeId').val(key);
    }
</script>
@endsection