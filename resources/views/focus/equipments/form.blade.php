<div class='form-group row'>
    <div class='col-md-4'>
        {{ Form::label( 'customer_id', 'Customer',['class' => 'col-12 control-label']) }}
        <select id="person" name="customer_id" class="form-control round required select-box" data-placeholder="{{trans('customers.customer')}}">
            <option value="{{@$equipments->customer->id}}" selected>{{@$equipments->customer->company}}
        </select>
    </div>
    <div class='col-md-4'>
        {{ Form::label( 'branch_id', 'Branch',['class' => 'col-12 control-label']) }}
        <select id="branch" name="branch_id" class="form-control   select-box" data-placeholder="Branch">
            <option value="{{@$equipments->branch->id}}" selected>{{@$equipments->branch->name}}
        </select>
    </div>
    <div class='col-md-4'>
        {{ Form::label( 'unique_id', 'Unique ID',['class' => 'col-12 control-label']) }}
        {{ Form::text('unique_id',  @$last_id->unique_id+1, ['class' => 'col form-control ', 'placeholder' => 'Unique ID*','required'=>'required']) }}
    </div>
</div>
<div class='form-group row'>
    <div class='col-md-4'>
        {{ Form::label( 'equip_serial', 'Equipment Serial',['class' => 'col-12 control-label']) }}
        {{ Form::text('equip_serial', null, ['class' => 'col form-control ', 'placeholder' => 'Equipment Serial*','required'=>'required']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label( 'unit_type', 'Unit Type:',['class' => 'col-12 control-label']) }}
        <select class="custom-select" id="unit_type" name="unit_type">
            <option value="{{@$projects->priority}}">--{{trans('Unit Type.'.@$projects->priority)}}--</option>
            @foreach (['InDoor', 'OutDoor', 'StandAlone'] as $k => $val)
                <option value="{{ $k+1 }}">{{ $val }}</option>
            @endforeach
        </select>
    </div>
    <div class='col-md-4'>
        {{ Form::label( 'rel_id', 'Select Related Indoor Unit',['class' => 'col-12 control-label']) }}
        <select id="indoor" name="rel_id" class="form-control   select-box" data-placeholder="Indoor Unit">
        </select>
    </div>
</div>
<div class='form-group row'>
    <div class='col-md-4'>
        {{ Form::label( 'manufacturer', 'Manufacrurer',['class' => 'col-12 control-label']) }}
        {{ Form::text('manufacturer', null, ['class' => 'col form-control ', 'placeholder' => 'Manufacrurer*','required'=>'required']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label( 'model', 'Model/Model Number',['class' => 'col-12 control-label']) }}
        {{ Form::text('model', null, ['class' => 'col form-control ', 'placeholder' => 'Model Name  Or Model Number*','required'=>'required']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label( 'capacity', 'Capacity:',['class' => 'col-12 control-label']) }}
        {{ Form::text('capacity', null, ['class' => 'col form-control ', 'placeholder' => 'Capacity']) }}
    </div>
</div>
<div class='form-group row'>
    <div class='col-md-4'>
        {{ Form::label( 'machine_gas', 'Gas Type:',['class' => 'col-12 control-label']) }}
        <select class="custom-select" id="todo-select" name="machine_gas">
            <option value="{{@$projects->priority}}">--{{trans('Gas.'.@$projects->priority)}}--</option>
            @foreach (['R22', 'R404a', 'R410a', 'R134a'] as $val)
                <option value="{{ $val }}">{{ $val }}</option>
            @endforeach
        </select>
    </div>
    <div class='col-md-4'>
        {{ Form::label( 'location', 'Machine Location:',['class' => 'col-12 control-label']) }}
        {{ Form::text('location', null, ['class' => 'col form-control ', 'placeholder' => 'Location*','required'=>'required']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label( 'installation_date', 'Maintanance Start Date',['class' => 'col control-label']) }}
        <fieldset class="form-group position-relative has-icon-left">
            <input type="text" class="form-control round required" placeholder="Maintanance Start Date*" value="{{timeFormat(@$equipments->installation_date)}}" name="installation_date" data-toggle="datepicker" required="required">
            <div class="form-control-position">
                <span class="fa fa-calendar" aria-hidden="true"></span>
            </div>
        </fieldset>
    </div>
</div>
<div class='form-group row'>
    <div class='col-md-4'>
        {{ Form::label( 'next_maintenance_date', 'Next Maintanance  Date',['class' => 'col control-label']) }}
        <fieldset class="form-group position-relative has-icon-left">
            <input type="text" class="form-control round required" placeholder="Next Maintanance  Date*" name="next_maintenance_date" data-toggle="datepicker" required="required">
            <div class="form-control-position">
                <span class="fa fa-calendar" aria-hidden="true"></span>
            </div>
        </fieldset>
    </div>
    <div class='col-md-4'>
        {{ Form::label( 'main_duration', 'Regular Maintenance Duration:',['class' => 'col-12 control-label']) }}
        {{ Form::text('main_duration', null, ['class' => 'col form-control ', 'placeholder' => 'Duration (In days)','required'=>'required']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label( 'service_rate', 'Maintanance Rate Exc VAT: *:',['class' => 'col-12 control-label']) }}
        {{ Form::text('service_rate', null, ['class' => 'col form-control ', 'placeholder' => 'Rate Exc VAT']) }}
    </div>
</div>