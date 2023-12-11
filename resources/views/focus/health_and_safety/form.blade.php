<div class="row">
    <div class="col-4">
        <label for="date">Date</label>
        <input type="date" id="date" name="date" required class="datepicker form-control box-size mb-2">
        {{-- {{ Form::text('date', null, ['class' => 'form-control round datepicker', 'id' => 'date_of_request']) }} --}}
    </div>
</div>


<div class="row">
    <div class="col-4">
        <label for="ticket">Customer</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <select class="custom-select customer_id" name="customer_id" id="customer_id">
                {{-- @foreach ($clients as $client)
                    <option value="{{ +$client->id }}">{{ $client->name }}</option>
                @endforeach --}}
            </select>
        </div>
    </div>
    <div class="col-4">
        <label for="ticket">Branch</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <select class="custom-select branch_id" name="branch_id" id="branch_id">
                {{-- @foreach ($clients as $client)
                    <option value="{{ +$client->id }}">{{ $client->name }}</option>
                @endforeach --}}
            </select>
        </div>
    </div>
    <div class="col-4">
        <label for="ticket">Project</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <select class="custom-select project_id" name="project_id" id="project_id">
                {{-- @foreach ($clients as $client)
                    <option value="{{ +$client->id }}">{{ $client->name }}</option>
                @endforeach --}}
            </select>
        </div>
    </div>
</div>

<br>
<div class="row">
    <div class="col-4">
        <label for="ticket">Parties involved</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <select class="custom-select employee" name="employee[]" id="employee" multiple required>
                @foreach ($employees as $employee)
                    <option value="{{ +$employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-4">
        <label for="ticket">Incident Description</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <textarea name="incident_desc" id="incident_desc-p0" cols="35" rows="2" class="form-control"
                        placeholder="Incident description" required></textarea>
        </div>
    </div>
    <div class="col-4">
        <label for="ticket">Root Cause</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <textarea name="route_course" id="route_course-p0" cols="35" rows="2" class="form-control"
                        placeholder="Root Cause" required></textarea>
        </div>
    </div>
</div>
<br>


<div class="row">
    <div class="col-4">
        <label for="ticket">Status</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <select class="custom-select required" id="status" name="status">
                <option value="first-aid-case"> First Aid case </option>
                <option value="lost-work-day"> Lost Work day </option>
            </select>
        </div>
    </div>
    <div class="col-4">
        <label for="ticket">Responsibility</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <select class="custom-select responsibility" name="responsibility" id="responsibility-p0">
                <option value="">Responsibility</option>
                @foreach ($employees as $employee)
                    <option value="{{ +$employee->id }}">{{ $employee->first_name }}
                        {{ $employee->last_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-4">
        <label for="ticket">Time to resolve(days)</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <input type="number" name="timing" id="timing-p0" class="form-control" placeholder="1" min="1">
        </div>
    </div>
</div>
<br>
<div class="row">
    <div class="col-4">
        <label for="ticket">PDCA Cycle</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <select class="custom-select required" id="pdca_cycle" name="pdca_cycle">
                <option value="plan">Action Identified(PLAN)</option>
                <option value="do">Action Being Implemented(DO)</option>
                <option value="check">Action Being Evaluated(CHECK)</option>
                <option value="act">Action Closed(ACT)</option>
            </select>
        </div>
    </div>
    <div class="col-8">
        <label for="ticket">Comments</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <textarea name="comments" id="comments-p0" cols="35" rows="2" class="form-control" placeholder="Coments"></textarea>
        </div>
    </div>

</div>
<br><br>