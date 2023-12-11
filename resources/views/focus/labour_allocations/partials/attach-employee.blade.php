<div class="modal" id="AddEmployeeModal" role="dialog" aria-labelledby="data_project" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title content-header-title" id="data_project">Employee Hours</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body"> 
                {{ Form::open(['route' => 'biller.labour_allocations.store']) }}   
                    <div id="project_name" class="mb-1"></div>
                    <input type="hidden"  name="project_id" id="project_id">
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="date">Date</label>
                            <input type="text" class="form-control datepicker" name="date" id="date" >
                        </div>
                        <div class="col-md-6">
                            <label for="type">Job Type</label>
                            <select name="type" id="type" class="custom-select" required>
                                <option value="">-- Select Job Type --</option>
                                @php
                                    $job_types = ['diagnosis', 'repair', 'maintenance', 'installation', 'supply', 'special_movement_allowance', 'standby_time', 'others'];
                                @endphp
                                @foreach($job_types as $value)
                                    <option value="{{ $value }}">
                                        @php
                                            if ($value == 'diagnosis') echo 'Diagnosis / Site Survey';
                                            elseif ($value == 'special_movement_allowance') echo 'Special Movement Allowance (> 2 Hours)';
                                            elseif ($value == 'standby_time') echo 'Standby Time';
                                            else echo ucfirst($value);
                                        @endphp
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <div class="col-md-12">
                            <label for="assigns">Employees</label>
                            <select class="form-control select-box" name="employee_id[]" id="employee" data-placeholder="Search Employee" multiple>
                                <option value=""></option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee['id'] }}">
                                        {{ $employee->fullname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label for="job_card">Job Card / DNote</label>
                            <div class="row no-gutters">
                                <div class="col-md-6">
                                    <select name="ref_type" class="custom-select">
                                        @foreach (['jobcard' => 'Job Card', 'dnote' => 'Delivery Note'] as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="job_card" id="job_card" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="hrs">Labour Hours <span id="expectedHrs" class="text-primary"></span> </label>
                            <input type="text" name="hrs" id="hrs" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="hrs">Is Payable</label>
                            <select name="is_payable" id="is_payable" class="custom-select">
                                @foreach(['1' => 'Yes','0' => 'No'] as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <div class="col-md-12">
                            <label for="note">Note</label>
                            <input type="text" name="note" id="note" class="form-control">
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>