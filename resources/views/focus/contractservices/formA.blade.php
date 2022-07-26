<div class="form-group row">
    <div class="col-4">
        <label for="client">Customer</label>
        <select name="customer_id" id="customer" class="form-control" data-placeholder="Choose Client"></select>
    </div>
    <div class="col-2">
        <label for="branch">Branch</label>
        <select name="branch_id" id="branch" class="form-control" data-placeholder="Choose Branch"></select>
    </div>
    <div class="col-4">
        <label for="contract">Contract</label>
        <select name="contract_id" id="contract" class="form-control" data-placeholder="Choose Contract"></select>
    </div>
    <div class="col-2">
        <label for="schedule">Schedule</label>
        <select name="schedule_id" id="schedule" class="form-control" data-placeholder="Choose Schedule"></select>
    </div>
</div>
<div class="form-group row">
    <div class="col-2">
        <label for="date">Jobcard Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
    </div>
    <div class="col-2">
        <label for="jobcard_no">Jobcard No</label>
        {{ Form::text('jobcard_no', null, ['class' => 'form-control', 'id' => 'jobcard_no']) }}
    </div>
    <div class="col-2">
        <label for="technician">Technician</label>
        {{ Form::text('technician', null, ['class' => 'form-control', 'id' => 'technician']) }}
    </div>
</div>
<div class="table-reponsive">
    <table id="equipTbl" class="table text-center">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>System ID</th>
                <th>Description</th>                                           
                <th>Location</th>
                <th>Rate</th>
                <th>Status</th>
                <th>Bill</th>
                <th width="30%">Note</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>        
            <tr>                                                    
                <td><span id="tid-0"></span></td>                                                               
                <td>
                    <textarea class="form-control" name="description" id="descr-0" cols="20" required></textarea>
                </td> 
                <td><span id="location-0"></span></td>     
                <td><span id="rate-0"></span></td>	
                <td>
                    <select name="status[]" class="custom-select" id="status-0">
                        @foreach (['working', 'faulty', 'cannibalised', 'decommissioned'] as $val)
                            <option value="{{ $val }}">{{ ucfirst($val) }}</option>
                        @endforeach
                    </select>                                                   
                </td>
                <td>
                    <select name="bill[]" class="custom-select" id="bill-0">
                        @foreach (['No', 'Yes'] as $k => $val)
                            <option value="{{ $k }}" {{ $k? 'selected' : ''}}>{{ $val }}</option>
                        @endforeach
                    </select>     
                </td>                       
                <td><input type="text" class="form-control" name="note[]" id="note-0"></td>    
                <td><a href="javascript:" class="btn btn-light del"><i class="danger fa fa-trash fa-lg"></i></a></td> 
                <input type="hidden" name="equipment_id[]" id="equipmentid-0">           
            </tr>                                                        
        </tbody>
    </table>
</div>
<a href="javascript:" class="btn btn-success" aria-label="Left Align" id="add_equip">
    <i class="fa fa-plus-square"></i> Add Equipment
</a>
<div class="form-group row mt-1">
    <div class="col-12">
        {{ Form::submit('Submit', ['class' => 'btn btn-primary btn-lg float-right']) }}
    </div>
</div>