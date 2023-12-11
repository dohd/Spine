
    <div class='row mb-1'>
        <div class='col-md-4'>
            {{ Form::label( 'employee', 'Employee Name',['class' => 'control-label']) }}
            <select class="form-control round" id="employeebox" data-placeholder="Search Employee"></select>
            <input type="hidden" name="employee_id" value="{{ @$salary->employee_name ?: 1 }}" id="employeeid">
             <input type="hidden" name="employee_name" value="{{ @$salary->employee_name?: 1 }}" id="employee">
        </div>
        <div class='col-md-4'>
            {{ Form::label( 'basic_pay', 'Basic Pay',['class' => 'control-label']) }}
            {{ Form::number('basic_pay', null, ['class' => 'form-control round', 'placeholder' => '0.00', 'required']) }}
        </div>
        <div class="col-md-4">
            {{ Form::label( 'contract_type', 'Contract Type',['class' => 'control-label']) }}
            <select class="form-control round" name="contract_type" id="employeebox" data-placeholder="Search Contract">
                <option value="permanent">Permanent</option>
                <option value="contract">Contract</option>
                <option value="casual">Casual</option>
                <option value="intern">Intern</option>
            </select>
        </div>
    </div>
    <div class='row mb-1'>
        {{-- <div class='col-md-4'>
            {{ Form::label( 'transport_allowance', 'Transport Allowance',['class' => 'control-label']) }}
            {{ Form::number('transport_allowance', null, ['class' => 'form-control round', 'placeholder' => '0.00']) }}
        </div>
        <div class='col-md-4'>
            {{ Form::label( 'directors_fee', 'Directors Fees',['class' => 'control-label']) }}
            {{ Form::number('directors_fee', null, ['class' => 'form-control round', 'placeholder' => '0.00']) }}
        </div> --}}
       
    </div>
    <div class="row mb-1">
        <div class="col-md-4">
            {{ Form::label('workshift', 'Select Workshift',['class' => 'control-label']) }}
            <select class="form-control round" name="workshift_id"  data-placeholder="Search Workshift">
                @foreach ($workshifts as $work)
                    <option value="{{$work->id}}" {{ $work->id == @$salary->workshift_id ? 'selected' : '' }}>{{$work->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            {{ Form::label( 'start_date', 'Start Date',['class' => 'control-label']) }}
            {{ Form::date('start_date', null, ['class' => 'form-control round datepicker', 'placeholder' => '', 'required']) }}
        </div>
        <div class="col-md-4">
            {{ Form::label( 'duration', 'Duration (in Months)',['class' => 'control-label']) }}
            {{ Form::number('duration', null, ['class' => 'form-control round', 'placeholder' => '1', 'required']) }}
        </div>
    </div>
    
    <div class="row mb-1">
        <div class="col-md-4">
            {{ Form::label('pay_per_hr', 'Pay Rate/Hr',['class' => 'control-label']) }}
            {{ Form::text('pay_per_hr', null, ['class' => 'form-control round', 'placeholder' => '0.00']) }}
        </div>
    </div>
    
    <div class="edit-form-btn text-right mb-2">
        {{ link_to_route('biller.salary.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md px-5']) }}
        {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md px-5']) }}
        <div class="clearfix"></div>
    </div>
    <p>Add Allowances</p>
    @include('focus.salary.partials.allowance_form')   


