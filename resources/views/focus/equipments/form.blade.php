<div class='form-group row'>
    <div class='col-md-4'>
        {{ Form::label('system_id', 'System ID',['class' => 'col-12 control-label']) }}
        {{ Form::text('tid', @$equipment->tid ?: @$last_tid+1, ['class' => 'col form-control', 'readonly']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label('customer_id', 'Customer',['class' => 'col-12 control-label']) }}
        <select id="person" name="customer_id" class="form-control round required select-box" data-placeholder="{{trans('customers.customer')}}">
            @isset ($equipment)
                <option value="{{ $equipment->customer_id }}" selected>
                    {{ $equipment->customer->name }} - {{ $equipment->customer->company }}
                </option>
            @endisset
        </select>
    </div>
    <div class='col-md-4'>
        {{ Form::label('branch_id', 'Branch',['class' => 'col-12 control-label']) }}
        <select id="branch" name="branch_id" class="form-control   select-box" data-placeholder="Branch">
            @isset ($equipment)
                <option value="{{ $equipment->branch_id }}" selected>
                    {{ $equipment->branch->name }}
                </option>
            @endisset
        </select>
    </div>
</div>

<div class='form-group row'>
    <div class='col-md-4'>
        {{ Form::label('equip_serial', 'Equipment Serial',['class' => 'col-12 control-label']) }}
        {{ Form::text('equip_serial', null, ['class' => 'col form-control ', 'placeholder' => 'Equipment Serial*', 'required']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label('unit_type', 'Unit Type',['class' => 'col-12 control-label']) }}
        <select class="custom-select" id="unit_type" name="unit_type" required>
            <option value="">-- Select Unit Type --</option>
            @foreach (['Indoor', 'Outdoor', 'Standalone'] as $val)
                <option value="{{ $val }}" {{ @$equipment->unit_type == $val? 'selected' : '' }}>
                    {{ $val }}
                </option>
            @endforeach
        </select>
    </div>
    <div class='col-md-4'>
        {{ Form::label('machine_gas', 'Gas Type',['class' => 'col-12 control-label']) }}
        <select class="custom-select" id="todo-select" name="machine_gas" required>
            <option value="">-- Select Gas Type --</option>
            @foreach (['R22', 'R404a', 'R410a', 'R134a'] as $val)
                <option value="{{ $val }}" {{ @$equipment->machine_gas == $val? 'selected' : '' }}>
                    {{ $val }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class='form-group row'>
    <div class='col-md-4'>
        {{ Form::label('make', 'Make',['class' => 'col-12 control-label']) }}
        {{ Form::text('make_type', null, ['class' => 'col form-control ', 'placeholder' => 'Make*', 'required']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label( 'model', 'Model/Model No',['class' => 'col-12 control-label']) }}
        {{ Form::text('model', null, ['class' => 'col form-control ', 'placeholder' => 'Model Name / Number*', 'required']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label('capacity', 'Capacity:',['class' => 'col-12 control-label']) }}
        {{ Form::text('capacity', null, ['class' => 'col form-control ', 'placeholder' => 'Capacity', 'required']) }}
    </div>
</div>

<div class='form-group row'>
    
    <div class='col-md-4'>
        {{ Form::label('location', 'Equipent Location',['class' => 'col-12 control-label']) }}
        {{ Form::text('location', null, ['class' => 'col form-control ', 'placeholder' => 'Location*', 'required']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label('building', 'Equipent Building',['class' => 'col-12 control-label']) }}
        {{ Form::text('building', null, ['class' => 'col form-control ', 'placeholder' => 'Building*', 'required']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label('floor', 'Building Floor',['class' => 'col-12 control-label']) }}
        {{ Form::text('floor', null, ['class' => 'col form-control ', 'placeholder' => 'Building*', 'required']) }}
    </div>
</div>

<div class="form-group row">
    <div class='col-md-4'>
        {{ Form::label('unique_id', 'Tag ID',['class' => 'col-12 control-label']) }}
        {{ Form::text('unique_id', null, ['class' => 'col form-control ', 'placeholder' => 'Building*', 'required']) }}
    </div>
    <div class='col-md-4'>
        {{ Form::label('equipment_category_id', 'Equipment Category',['class' => 'col-12 control-label']) }}
        <select name="equipment_category_id" class="custom-select" id="category_id">
            <option value="">-- Select Category --</option>
            @foreach ($categories as $row)
                <option value="{{ $row->id }}" {{ $row->id == @$equipment->equipment_category_id ? 'selected' : '' }}>
                    {{ $row->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class='col-md-4'>
        {{ Form::label('service_rate', 'Maintanance Rate (VAT Exc)',['class' => 'col-12 control-label']) }}
        {{ Form::text('service_rate', null, ['class' => 'col form-control ', 'placeholder' => 'Rate Exc VAT', 'required']) }}
    </div>
</div>
