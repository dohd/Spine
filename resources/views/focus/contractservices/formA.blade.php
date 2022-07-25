<div class="form-group row">
    <div class="col-4">
        <label for="client">Customer</label>
        <select name="customer_id" id="customer" class="form-control"></select>
    </div>
    <div class="col-2">
        <label for="branch">Branch</label>
        <select name="branch_id" id="branch" class="form-control"></select>
    </div>
    <div class="col-4">
        <label for="contract">Contract</label>
        <select name="contract_id" id="contract" class="form-control"></select>
    </div>
    <div class="col-2">
        <label for="schedule">Schedule</label>
        <select name="schedule_id" id="schedule" class="form-control"></select>
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
    <table id="equipmentTbl" class="table text-center">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>System ID</th>
                <th>Location</th>
                <th>Description</th>                                           
                <th>Rate</th>
                <th>Status</th>
                <th>Bill</th>
                <th width="30%">Note</th>
            </tr>
        </thead>
        <tbody>        
            <tr>                                                    
                <td>Eq-0001</td>
                <td>ATM ROOM</td>                                                    
                <td>456; 1; 24000; R22; LG HIGHWALL</td> 
                <td>1,586.69</td>	
                <td>
                    <select name="status[]" class="custom-select" id="">
                        @foreach (['working', 'faulty', 'cannibalised', 'decommissioned'] as $val)
                            <option value="{{ $val }}">{{ ucfirst($val) }}</option>
                        @endforeach
                    </select>                                                   
                </td>
                <td>
                    <select name="billed[]" class="custom-select" id="">
                        @foreach (['No', 'Yes'] as $k => $val)
                            <option value="{{ $k }}" {{ $k? 'selected' : ''}}>{{ $val }}</option>
                        @endforeach
                    </select>     
                </td>                       
                <td><input type="text" class="form-control" name="note[]"></td>                   
            </tr>                                                        
        </tbody>
    </table>
</div>