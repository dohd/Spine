<div class="modal fade " id="callModal" tabindex="-1" role="dialog" aria-labelledby="callModal" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 1080px" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-remarks-label">Call History</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>


            <div class="mx-3">
                <div class="form-group row">
                    <div class='col-sm-6 mb-2'>

                        <div><label for="prospect-type">Select Call Status</label></div>
                        <div class="d-inline-block custom-control custom-checkbox mr-1">
                            <input type="radio" class="custom-control-input bg-primary call-status" name="call_status"
                                id="colorCheck1" value="picked" checked>
                            <label class="custom-control-label" for="colorCheck1">Picked</label>
                        </div>
                        <div class="d-inline-block custom-control custom-checkbox mr-1">
                            <input type="radio" class="custom-control-input bg-purple call-status" name="call_status"
                                value="notpicked" id="colorCheck3">
                            <label class="custom-control-label" for="colorCheck3">Not Picked</label>
                        </div>

                    </div>
                </div>
            </div>

            <div class="picked">
                <div class="mx-2">
                    <h3>Follow up questions</h3>
                    <div class="form-group">
                        <p>Do you have an ERP</p>
                        <div class="d-inline-block">
                            <input type="radio" id="yes" name="erp" value="1">
                            <label for="yes">Yes</label><br>
                        </div>
                        <div class="d-inline-block">
                            <input type="radio" id="no" name="erp" value="0">
                            <label for="no">No</label><br>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-6">
                            <p>Which One</p>
                            {!! Form::text('current_erp', null, ['class' => 'form-control ', 'placeholder' => 'Current ERP', 'id'=>'current_erp' ]) !!}
                        </div>
                        <div class="col-md-6">
                            <p>How long have you been using the ERP</p>
                            {!! Form::text('current_erp_usage', null, ['class' => 'form-control ', 'placeholder' => 'Weeks/Months/Years', 'id'=>'current_erp_usage' ]) !!}
                        </div>
                        
                    </div>
                    <div class="form-group">
                        <p>Do you have any challenges in your existing ERP</p>
                        <div class="d-inline-block">
                            <input type="radio" id="yes" name="erp_challenges" value="1">
                            <label for="yes">Yes</label><br>
                        </div>
                        <div class="d-inline-block">
                            <input type="radio" id="no" name="erp_challenges" value="0">
                            <label for="no">No</label><br>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-12">
                            <p>State some of the challenges</p>
                            {!! Form::textarea('current_erp_challenges', null, ['class' => 'form-control ', 'rows' => 3,'placeholder' => 'Challenges', 'id'=>'current_erp_challenges' ]) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <p>Are you interested in us showing you a demo</p>
                        <div class="d-inline-block">
                            <input type="radio" id="yes" name="erp_demo" value="1">
                            <label for="yes">Yes</label><br>
                        </div>
                        <div class="d-inline-block">
                            <input type="radio" id="no" name="erp_demo" value="0">
                            <label for="no">No</label><br>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-6">
                            <p>When do you think is the appropriate date</p>
                            {!! Form::text('demo_date', null, ['class' => 'form-control ', 'placeholder' => 'Demo date', 'id'=>'demo_date' ]) !!}
                        </div>
                        <div class="col-md-6">
                            <p>Any Notes?</p>
                            {!! Form::text('notes', null, ['class' => 'form-control ', 'placeholder' => 'Notes/Remarks', 'id'=>'notes' ]) !!}
                        </div>
                    </div>
                    {{ Form::button("Save Call Chat",['class'=>' my-2 btn btn-md btn-primary','id'=>'save_remark']) }}
                </div>
            </div>

            <div class="not-picked">
                <div class="mx-2">
                    <h3>Busy Not Picking</h3>
                    <div class="form-group">
                        <p>Leave Reminder</p>
                        <div class="d-inline-block">
                            <input type="radio" id="yes" name="reminder" value="1">
                            <label for="yes">Yes</label><br>
                        </div>
                        <div class="d-inline-block">
                            <input type="radio" id="no" name="reminder" value="0">
                            <label for="no">No</label><br>
                        </div>
                    </div>
    
                    <div class="form-group row">
                        <div class="col-md-6">
                            <p>Reminder Date</p>
                            {!! Form::text('reminder_date', null, ['class' => 'form-control ', 'placeholder' => 'Reminder date', 'id'=>'reminder_date' ]) !!}
                        </div>
                        <div class="col-md-6">
                            <p>Reminder NOte</p>
                            {!! Form::text('reminder_note', null, ['class' => 'form-control ', 'placeholder' => 'Reminder note', 'id'=>'reminder_note' ]) !!}
                        </div>
                    </div>
                    {{ Form::button("Save Reminder",['class'=>' my-2 btn btn-md btn-primary','id'=>'save_remark']) }}
                </div>
               
            </div>

            {{-- <div class="mx-3">
               
                    <h5 class="my-2">New Remark</h5>
                    {{ Form::open(['id'=>'remarkform']) }}
                    <div class="form-group column">
                        {{ Form::hidden('prospect_id',null,['class'=>'form-control','id'=>'prospect_id']) }}
                        <div class="row">
                           
                            <div class="col-sm-9"><label for="recepient" class="caption">Recepient Name</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                    {{ Form::text('recepient', null, ['class' => 'form-control ', 'placeholder' => 'Name', 'id'=>'recepient' ,'required']) }}
                                </div>
                            </div>
                            <div class="col-sm-3"><label for="reminder_date" class="caption">Next Reminder Date</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                    {{ Form::text('reminder_date', null, ['class' => 'form-control datepicker', 'placeholder' => 'Date', 'id' => 'reminder_date' ,'required']) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12"><label for="remarks" class="caption">Remarks</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                    {{ Form::textarea('remarks', null, ['class' => 'form-control','rows' => 3, 'placeholder' => 'Remark','id'=>'remarks','required']) }}
                                </div>
                            </div>
                        </div>
                      
                        
                            {{ Form::button("Save Remark",['class'=>' my-2 btn btn-md btn-primary','id'=>'save_remark']) }}
                        {{ Form::close() }}   
                    </div>
        
                         
            
            </div> --}}

        </div>
    </div>
</div>
