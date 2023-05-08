<div class="row">
    <div class="col-sm-6 cmp-pnl">
        <div id="customerpanel" class="inner-cmp-pnl">
            <div class="form-group row">
                <div class="fcol-sm-12">
                    <h3 class="title pl-1">Customer Info </h3>
                </div>
            </div>
            <div class="form-group row">
                <div class='col-md-12'>
                    <div class='col m-1'>
                        <div><label for="client-type">Select Client Type</label></div>                        
                        <div class="d-inline-block custom-control custom-checkbox mr-1">
                            <input type="radio" class="custom-control-input bg-primary client-type" name="client_status" id="colorCheck1" value="customer" checked>
                            <label class="custom-control-label" for="colorCheck1">Existing</label>
                        </div>
                        <div class="d-inline-block custom-control custom-checkbox mr-1">
                            <input type="radio" class="custom-control-input bg-purple client-type" name="client_status" value="new" id="colorCheck3">
                            <label class="custom-control-label" for="colorCheck3">New Client</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-6"><label for="client_id" class="caption">Customer <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        <select id="customer" name="client_id" class="form-control" data-placeholder="Choose Customer">
                            @foreach ($customers as $row)
                                <option value="{{ $row->id }}">
                                    {{ $row->company }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-6"><label for="ref_type" class="caption">Branch</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        <select id="branch" name="branch_id" class="form-control  select-box" data-placeholder="Choose Branch" disabled>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-6"><label for="client_name" class="caption">Client Name</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('client_name', null, ['class' => 'form-control round', 'placeholder' => 'Name', 'id'=>'payer-name', 'readonly']) }}
                    </div>
                </div>
                <div class="col-sm-6"><label for="client_email" class="caption">Client Email</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('client_email', null, ['class' => 'form-control round', 'placeholder' => 'Email','id'=>'client_email', 'readonly']) }}
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-6"><label for="client_contact" class="caption">Client Contact</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('client_contact', null, ['class' => 'form-control round', 'placeholder' => 'Contact','id'=>'client_contact', 'readonly']) }}
                    </div>
                </div>
                <div class="col-sm-6"><label for="client_address" class="caption">Client Address</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('client_address', null, ['class' => 'form-control round', 'placeholder' => 'Contact', 'id' => 'client_address', 'readonly']) }}
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class='col-md-12'>
                    <div class='col m-1'>
                                                
                        <input type="checkbox" id="add-reminder" value="checked">
                        <label for="client-type">Add Reminder</label>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-6"><label for="reminder_date" class="caption">Reminder Start Date</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        <input type="datetime-local" name="reminder_date" id="reminder_date" class="form-control" />
                    </div>
                </div>
                <div class="col-6"><label for="client_ref" class="caption">Event Date</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        <input type="datetime-local" name="exact_date" id="exact_date" class="form-control" disabled/>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 cmp-pnl">
        <div class="inner-cmp-pnl">
            <div class="form-group row">
                <div class="col-sm-12">
                    <h3 class="title">Ticket Info</h3>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-6"><label for="reference" class="caption">Ticket No</span></label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        {{ Form::text('reference', gen4tid("{$prefixes[0]}-", @$lead->reference ?: $tid+1), ['class' => 'form-control round', 'disabled']) }}
                        {{ Form::hidden('reference', @$lead->reference ?: $tid+1) }}                        
                    </div>
                </div>
                <div class="col-sm-6"><label for="date_of_request" class="caption">Callout / Client Report Date</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                        {{ Form::text('date_of_request', null, ['class' => 'form-control round datepicker', 'id' => 'date_of_request']) }}
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-12"><label for="title" class="caption"> Subject / Title <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span>
                        </div>
                        {{ Form::text('title', null, ['class' => 'form-control round', 'placeholder' => 'Title', 'required']) }}
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-6"><label for="source" class="caption">Source <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        <select id="source" name="source" class="custom-select round" required>
                            <option value="">-- Select Source --</option>
                            @foreach (['Emergency Call', 'RFQ', 'Site Survey', 'Existing SLA', 'Tender', 'Other'] as $val)
                                <option value="{{ $val }}" {{ @$lead->source == $val? 'selected' : '' }}>
                                    {{ $val }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-6"><label for="employee_id" class="caption">Requested By (Client Rep)<span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        {{ Form::text('assign_to', null, ['class' => 'form-control round', 'placeholder' => 'Requested By', 'required']) }}
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-6"><label for="client_ref" class="caption">Client Ref / Callout ID</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        {{ Form::text('client_ref', null, ['class' => 'form-control round', 'placeholder' => 'Client Reference No.', 'id' => 'client_ref', 'maxlength' => 30]) }}
                    </div>
                </div>
               
            </div>
            <div class="form-group row">
                <div class="col-12"><label for="refer_no" class="caption">Note</label>
                    <div class="input-group">
                        <div class="w-100">
                            {{ Form::textarea('note', null, ['class' => 'form-control', 'rows' => 6]) }}
                        </div>
                    </div>
                </div>
            </div>            
        </div>
    </div>
</div>

@section("after-scripts")
@include('focus.leads.form_js')
@endsection