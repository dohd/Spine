<div class="table-reponsive" style="overflow-x: scroll;">
    <table id="equipmentTbl" class="table">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>System ID</th>
                <th>Location</th>
                <th>Description</th>                                           
                <th>Jobcard No</th>
                <th>Jobcard Date</th>
                <th width="10%">Status</th>
                <th>Amount</th>
                <th>
                    Charge
                    <div class="d-inline ml-2">
                        <input type="checkbox" class="form-check-input" id="selectAll">
                    </div>
                </th>
                <th>Technician</th>
                <th width="12%">Note</th>
            </tr>
        </thead>
        <tbody>                                            
            @foreach ($contractservice->items as $i => $row)                                            
                <tr>                                                    
                    <td>{{ gen4tid('E-', $row->equipment->tid) }}</td>
                    <td>{{ $row->equipment->location }}</td>                                                    
                    <td>
                        @php
                            $descr = array_intersect_key(
                                $row->equipment->toArray(), 
                                array_flip(['make_type', 'equip_serial', 'unique_id', 'capacity', 'machine_gas'])
                            );
                            echo implode('; ', array_values($descr));
                        @endphp                                                                                          
                    </td>
                    <td><input type="text" class="form-control" name="jobcard_no[]" value="{{ $row->jobcard_no }}" id=""></td>
                    <td><input type="text" class="form-control datepicker" name="jobcard_date[]" id="jobcardDate-{{ $i }}"></td>
                    <td>
                        <select name="status[]" class="form-control" id="">
                            @foreach (['working', 'faulty', 'cannibalised', 'decommissioned'] as $val)
                                <option value="{{ $val }}" {{ $val == $row->status? 'selected' : '' }}>{{ ucfirst($val) }}</option>
                            @endforeach
                        </select>                                                   
                    </td>
                    <td>{{ numberFormat($row->equipment->service_rate) }}</td>
                    <td>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input ml-1 select" id="chargeCheck-{{ $i }}">
                        </div>
                    </td>                       
                    <td><input type="text" class="form-control" name="technician[]" value="{{ $row->technician }}"></td>
                    <td><input type="text" class="form-control" name="note[]" value="{{ $row->note }}"></td>
                    <input type="hidden" class="rate" value="{{ $row->equipment->service_rate }}" disabled>
                    <input type="hidden" class="charged" name="is_charged[]" value="{{ $row->is_charged }}">                    
                    <input type="hidden" name="id[]" value="{{ $row->id }}">                    
                </tr>                                                        
            @endforeach                                                    
        </tbody>
    </table>
</div>
<div class="form-group row">
    <div class="col-2 ml-auto">
        <label for="charge">Total Charge Amount</label>
        <input type="text" name="charge_amount" class="form-control" id="totalRate" readonly>
    </div>
</div>
<div class="form-group row">
    <div class="col-2 ml-auto">
        {{ Form::submit('Update', ['class' => 'btn btn-primary btn-lg']) }}
    </div>
</div>