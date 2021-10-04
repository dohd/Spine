<div class='row'>
    <div class='col-md-4'>
        <div class='form-group'>
            {{ Form::label( 'customer_id', 'Customer',['class' => 'col-12 control-label']) }}
            <div class="col">
                <select id="person" name="customer_id" class="form-control round required select-box"  data-placeholder="{{trans('customers.customer')}}" >
                     <option value="{{$equipments->customer->id}}"
                        selected>{{$equipments->customer->company}}
                                </select></div>
        </div>
    </div>
    <div class='col-md-4'>
        <div class='form-group'>
            {{ Form::label( 'branch_id', 'Branch',['class' => 'col-12 control-label']) }}
            <div class="col">
                <select id="branch" name="branch_id" class="form-control   select-box"  data-placeholder="Branch" >
                     <option value="{{$equipments->branch->id}}"
                        selected>{{$equipments->branch->name}}
                                </select>
            </div>
        </div>
    </div>

     <div class='col-md-4'>
        <div class='form-group'>
            {{ Form::label( 'unique_id', 'Unique ID',['class' => 'col-12 control-label']) }}
            <div class="col">
                {{ Form::text('unique_id',  @$last_id->unique_id+1, ['class' => 'col form-control ', 'placeholder' => 'Unique ID*','required'=>'required']) }}
            </div>
        </div>
    </div>
</div>


<div class='row'>
    <div class='col-md-4'>
        <div class='form-group'>
            {{ Form::label( 'equip_serial', 'Equipment Serial',['class' => 'col-12 control-label']) }}
            <div class="col">
                {{ Form::text('equip_serial', null, ['class' => 'col form-control ', 'placeholder' => 'Equipment Serial*','required'=>'required']) }}</div>
        </div>
    </div>
    <div class='col-md-4'>
        <div class='form-group'>
            {{ Form::label( 'unit_type', 'Unit Type:',['class' => 'col-12 control-label']) }}
            <div class="col">
                <select class="custom-select" id="unit_type" name="unit_type">
                <option value="{{$projects->priority}}">--{{trans('Unit Type.'.$projects->priority)}}--</option>
                <option value="1">InDoor</option>
                 <option value="2">OutDoor</option>
                <option value="3">StandAlone</option>
               
            </select>
            </div>
        </div>
    </div>


     <div class='col-md-4'>
        <div class='form-group'>
            {{ Form::label( 'rel_id', 'Select Related Indoor Unit',['class' => 'col-12 control-label']) }}
            <div class="col">
                <select id="indoor" name="rel_id" class="form-control   select-box"  data-placeholder="Indoor Unit" >
                                </select>
            </div>
        </div>
    </div>
</div>



<div class='row'>

    <div class='col-md-4'>
        <div class='form-group'>
            {{ Form::label( 'manufacturer', 'Manufacrurer',['class' => 'col-12 control-label']) }}
            <div class="col">

                {{ Form::text('manufacturer', null, ['class' => 'col form-control ', 'placeholder' => 'Manufacrurer*','required'=>'required']) }}
            </div>
        </div>
    </div>



   

    <div class='col-md-4'>
        <div class='form-group'>
            {{ Form::label( 'model', 'Model/Model Number',['class' => 'col-12 control-label']) }}
            <div class="col">
                {{ Form::text('model', null, ['class' => 'col form-control ', 'placeholder' => 'Model Name  Or Model Number*','required'=>'required']) }}
            </div>
        </div>
    </div>


   <div class='col-md-4'>
        <div class='form-group'>
            {{ Form::label( 'capacity', 'Capacity:',['class' => 'col-12 control-label']) }}
            <div class="col">
                {{ Form::text('capacity', null, ['class' => 'col form-control ', 'placeholder' => 'Capacity']) }}</div>
        </div>
    </div>


</div>


<div class='row'>
     

    <div class='col-md-4'>
        <div class='form-group'>
            {{ Form::label( 'machine_gas', 'Gas Type:',['class' => 'col-12 control-label']) }}
            <div class="col">
                  <select class="custom-select" id="todo-select" name="machine_gas">
                <option value="{{$projects->priority}}">--{{trans('Gas.'.$projects->priority)}}--</option>
                <option value="R22">R22</option>
                 <option value="R404a">R404a</option>
                <option value="R410a">R410a</option>
                 <option value="R134a">R134a</option>
               
            </select>
            </div>
        </div>
    </div>

    <div class='col-md-4'>
        <div class='form-group'>
            {{ Form::label( 'location', 'Machine Location:',['class' => 'col-12 control-label']) }}
            <div class="col">
                {{ Form::text('location', null, ['class' => 'col form-control ', 'placeholder' => 'Location*','required'=>'required']) }}</div>
        </div>
    </div>

      
   <div class='col-md-4'>
        <div class='form-group'>
            {{ Form::label( 'installation_date', 'Maintanance Start Date',['class' => 'col control-label']) }}
            <div class='col-12'>
                <fieldset class="form-group position-relative has-icon-left">
                    <input type="text" class="form-control round required"
                           placeholder="Maintanance Start Date*"  value="{{timeFormat($equipments->installation_date)}}"  name="installation_date"
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
 

    <div class='col-md-4'>
        <div class='form-group'>
            {{ Form::label( 'next_maintenance_date', 'Next Maintanance  Date',['class' => 'col control-label']) }}
            <div class='col-12'>
                <fieldset class="form-group position-relative has-icon-left">

                    <input type="text" class="form-control round required"
                           placeholder="Next Maintanance  Date*" name="next_maintenance_date"
                           data-toggle="datepicker"   required="required">
                    <div class="form-control-position">
                      <span class="fa fa-calendar"
                            aria-hidden="true"></span>
                    </div>

                </fieldset>
            </div>
        </div>
    </div>
   <div class='col-md-4'>
        <div class='form-group'>
            {{ Form::label( 'main_duration', 'Regular Maintenance Duration:',['class' => 'col-12 control-label']) }}
            <div class="col">
                {{ Form::text('main_duration', null, ['class' => 'col form-control ', 'placeholder' => 'Duration (In days)','required'=>'required']) }}
            </div>
        </div>
    </div>
  
    <div class='col-md-4'>
        <div class='form-group'>
            {{ Form::label( 'service_rate', 'Maintanance Rate Exc VAT: *:',['class' => 'col-12 control-label']) }}
            <div class="col">
                {{ Form::text('service_rate', null, ['class' => 'col form-control ', 'placeholder' => 'Rate Exc VAT']) }}
            </div>
        </div>
    </div>

  
    
</div>











