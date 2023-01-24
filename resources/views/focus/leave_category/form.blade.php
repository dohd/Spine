<div class="form-group row">
    <div class="col-2">
        <label for="leave_code">Leave Code</label>
        {{ Form::text('leave_code', null, ['class' => 'form-control', 'id' => 'leave_code', 'required']) }}
    </div>
    <div class="col-4">
        <label for="title">Leave Title</label>
        {{ Form::text('title', null, ['class' => 'form-control', 'id' => 'title', 'required']) }}
    </div>
    <div class="col-2">
        <label for="color">Color</label>
        {{ Form::color('color', null, ['class' => 'form-control', 'id' => 'color', 'required']) }}
    </div>
    
    <div class="col-2">
        <label for="gender">Gender</label>
        <select name="gender" id="gender" class="custom-select">
            @foreach (['a' => 'all', 'm' => 'male', 'f' => 'female'] as $k => $val)
                <option value="{{ $k }}" {{ @$leave_category && $leave_category->gender == $k? 'selected' : '' }}>
                    {{ ucfirst($val) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-2">
        <label for="is_payable">Payable Leave</label>
        <select name="is_payable" id="is_payable" class="custom-select">
            @foreach ([1 => 'yes', 0 => 'no'] as $k => $val)
                <option value="{{ $k }}" {{ @$leave_category && $leave_category->is_payable == $k? 'selected' : '' }}>
                    {{ ucfirst($val) }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group row">
    <div class="col-2">
        <label for="is_encashed">Encash Leave</label>
        <select name="is_encashed" id="is_encashed" class="custom-select">
            @foreach ([1 => 'no', 0 => 'yes'] as $k => $val)
                <option value="{{ $k }}" {{ @$leave_category && $leave_category->is_encashed == $k? 'selected' : '' }}>
                    {{ ucfirst($val) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-2">
        <label for="days">Leave Duration (days)</label>
        {{ Form::number('qty', null, ['class' => 'form-control', 'min' => '1']) }}
    </div>
    <div class="col-2">
        <label for="">Exclude (days)</label><br>
        <input type="checkbox" class="off_days" min="1" value="1" name="off_days" @isset($leave_category){{($leave_category->off_days == 1 ? ' checked' : '')}} @endisset >
        <span>Exclude Off Days</span><br>
        <input type="checkbox" class="holidays" min="1" value="1" name="holidays" @isset($leave_category){{($leave_category->holidays == 1 ? ' checked' : '')}} @endisset>
        <span>Exclude Holidays</span><br>
        
    </div>
    <div class="col-2">
        <label for="leave_pay">Leave Pay (rate)</label>
        {{ Form::text('leave_pay', null, ['class' => 'form-control', 'min' => '1', 'placeholder'=>'0.00']) }}
    </div>
    <div class="col-2">
        <label for="leave_start">Leave Starts at (year)</label>
        {{ Form::date('leave_start', null, ['class' => 'form-control', 'min' => '1']) }}
    </div>
    <div class="col-2">
        <label for="start_contract"></label><br>
        <input type="checkbox" class="start_contract" min="1" value="1" name="start_contract"  @isset($leave_category){{($leave_category->start_contract == 1 ? ' checked' : '')}} @endisset >
        <span>Start of Contract</span><br>
    </div>
</div>

<div class="form-group row">
    
    <div class="col-2">
        <label for="days_accrued">Days Accrued (days)</label>
        <select name="days_accrued" id="days-accrued" class="custom-select">
            <option value="yearly">Yearly</option>
        </select>
    </div>
    <div class="col-4">
        <label for="registered_accrued">Accrual Registed at (days)</label>
        <select name="registered_accrued" id="registered_accrued" class="custom-select">
            <option value="start_year">Start of Year</option>
        </select>
    </div>
    <div class="col-2">
        <label for="max_days">Max. Days Carried Forward</label>
        {{ Form::number('days_carried_foward', null, ['class' => 'form-control', 'min' => '1']) }}
    </div>
       <div class="col-4">
        <label for="">Employment Types</label><br>
        {{-- {{dd($leave_category)}} --}}
        @isset($leave_category)
        @php
            $leave = explode(",",$leave_category->employment_type);
            //dd($leave[0])
        @endphp
        @endisset
        
        <input type="checkbox" min="1" name="" id="all_types">
            <span class="mr-2">All</span>
        <div class="border round p-1">
            <input type="checkbox" class="sub_types" min="1" data-id="permanent" name="permanent" @isset($leave_category)
            {{  (in_array('permanent',$leave) == 'permanent' ? ' checked' : '') }}
            @endisset >
            <span class="mr-2">Permanent</span>
            <input type="checkbox" class="sub_types" min="1"  data-id="contract" name="contract" @isset($leave_category)
            {{  (in_array('contract',$leave) == 'contract' ? ' checked' : '') }}
            @endisset >
            <span class="mr-2">Contract</span><br>
            <input type="checkbox" class="sub_types" min="1" data-id="casual" name="casual" @isset($leave_category)
            {{  (in_array('casual',$leave) == 'casual' ? ' checked' : '') }}
            @endisset >
            <span class="mr-4">Casual</span>
            <input type="checkbox" class="sub_types" min="1" data-id="intern" name="intern" @isset($leave_category)
            {{  (in_array('intern',$leave) == 'intern' ? ' checked' : '') }}
            @endisset >
            <span class="mr-2">Intern</span>
            <input type="hidden" name="employment_type" id="employment_type">
        </div>
       </div>
</div>

<div class="form-group row">
    <div class="col-8">
        <label for="policy">Policy</label>
        {{ Form::text('policy', null, ['class' => 'form-control', 'id' => 'policy', 'required']) }}
    </div>
    <div class="col-2">
        <label for="">Mandatory Attachment</label><br>
       <div class="p-1">
        <input type="checkbox" class="mandatory" min="1" value="1" name="mandatory" @isset($leave_category)
        {{  ($leave_category->mandatory == 1 ? ' checked' : '') }}
        @endisset>
        <span class="mr-2">mandatory</span>
       </div>
    </div>
</div>


<div class="form-group row no-gutters">
    <div class="col-1 ml-auto">
        <a href="{{ route('biller.leave_category.index') }}" class="btn btn-danger block">Cancel</a>    
    </div>
    <div class="col-1 ml-1">
        {{ Form::submit(@$leave_category? 'Update' : 'Create', ['class' => 'form-control btn btn-primary submit']) }}
    </div>
</div>

@section('extra-scripts')
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };
    // $('form').submit(function (e) { 
    //     e.preventDefault();
    //     console.log($(this).serializeArray());
    // });

    $('#all_types').on('click', function(e) {
            if ($(this).is(':checked', true)) {
                $(".sub_types").prop('checked', true);
                console.log('clicked');
            } else {
                $(".sub_types").prop('checked', false);
                console.log('False clicked');
            }
        });
        
    $('.submit').click(function (e) { 
        var allVals = [];
            $(".sub_types:checked").each(function() {
                allVals.push($(this).attr('data-id'));
                
            });
            $('#employment_type').val(allVals);
        console.log(allVals);
        var name = $('.start_contract:checked').val();
        if(name == '1'){
            console.log(name);
        }else{
            name = 0;
            $('.start_contract').val(name);
            console.log(name);
        }
        
    });
    const Index = {
        leave_category: @json(@$leave_category),

        init() {
            $.ajaxSetup(config.ajax);
        },

    };

    $(() => Index.init());
</script>
@endsection