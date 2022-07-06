<div class="form-group row">
    <div class="col-md-6">
        {{ Form::label('account_type', trans('accounts.account_type')) }}
        <select name="account_type" class="form-control" id="accType" required>
            <option value="">-- Select Account Type --</option>
            @foreach ($account_types as $row)
                <option 
                    value="{{ $row->category }}" 
                    key="{{ $row->id }}"
                    isMultiple="{{ $row->is_multiple }}"
                    isOpenBalance="{{ $row->is_opening_balance }}"
                    {{ $row->id == @$account->account_type_id ? 'selected' : '' }}
                >
                    {{ $row->name }}
                </option>
            @endforeach
        </select>
        <input type="hidden" name="account_type_id" id="accTypeId" value="{{ @$account->account_type_id }}">
        <input type="hidden" name="is_multiple" id="isMultiple">
    </div>
    <div class="col-md-6">
        {{ Form::label('number', trans('accounts.number')) }}
        {{ Form::text('number', null, ['class' => 'form-control', 'id' => 'account_number', 'readonly']) }}
    </div>
</div>
<div class="form-group row">
    <div class="col-md-6">
        {{ Form::label('holder', 'Account Name') }}
        {{ Form::text('holder', null, ['class' => 'form-control box-size','placeholder' => 'Account Name *','required' => 'required']) }}
    </div>
    <div class="col-md-6">
        {{ Form::label('number', 'Account Can Be Used In Manual Journal') }}
        <select name="is_manual_journal" class="form-control" required id="is_manual_journal" required>
            <option value="">-- Select --</option>
            @foreach (['No', 'Yes'] as $k => $val) 
                <option value="{{ $k }}" {{ $k == @$account->is_manual_journal ? 'selected' : '' }}>
                    {{ $val }}
                </option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group row">
    <div class="col-md-6">
        {{ Form::label('is_parent', 'Is Account Sub-Category') }}
        <select name="is_parent" class="form-control" id="is_parent" required>
            <option value="">-- Select --</option>
            @foreach (['No', 'Yes'] as $k => $val) 
                <option value="{{ $k }}" {{ $k == @$account->is_parent ? 'selected' : '' }}>
                    {{ $val }}
                </option>
            @endforeach
        </select>       
    </div>
    <div class="col-md-6">       
        {{ Form::label('category', 'Account Category') }}            
        {!! Form::select('category_id', $account_categories, null, ['class' => 'form-control ', 'placeholder' => '-- Select Category --', 'id' => 'category_id', 'disabled']) !!}            
    </div>
</div>
<div class="form-group row">
    <div class="col-md-6">        
        {{ Form::label('opening_balance', 'Opening Balance') }}           
        {{ Form::text('opening_balance', numberFormat(@$account->opening_balance), ['class' => 'form-control', 'readonly', 'id' => 'openBalance']) }}
    </div>
    <div class="col-md-6">
        {{ Form::label('date', 'Date') }}
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'readonly', 'id' => 'date']) }}
    </div>
</div>
<div class="form-group row">
    <div class="col-md-12">        
        {{ Form::label('note', trans('accounts.note')) }}
        {{ Form::text('note', null, ['class' => 'form-control', 'placeholder' => trans('accounts.note')]) }}
    </div>
</div>

@section('after-scripts')
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });

    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    .datepicker('setDate', new Date());

    const account = @json(@$account);
    if (account) {
        $('#date').datepicker('setDate', new Date(account.opening_balance_date)).attr('readonly', false);
        $('#openBalance').attr('readonly', false);
    }
        
    // on selecting account type
    $('#accType').change(function() {
        const opt = $('#accType option:selected');
        $('#accTypeId').val(opt.attr('key'));
        $('#isMultiple').val(opt.attr('isMultiple'));

        $('#openBalance').attr('readonly', true);
        $('#date').attr('readonly', false);
        if (opt.attr('isOpenBalance') == 1) {
            $('#openBalance').attr('readonly', false);
            $('#date').attr('readonly', false);
        }
        $.ajax({
            url: "{{ route('biller.accounts.search_next_account_no') }}",
            type: 'POST',
            dataType: 'json',
            data: {account_type: $(this).val()},
            success: data => $('#account_number').val(data.account_number),            
        });
    });

    // on open balance change
    $("#openBalance").change(function() {
        const val = $(this).val().replace(/,/g, '');
        $(this).val((val*1).toLocaleString());
    });

    // on account sub-category change
    $('#is_parent').on('change', function() {
        $('#category_id').prop('disabled', true);
        if ($(this).val() == 1) $('#category_id').prop('disabled', false);
    });
</script>
@endsection