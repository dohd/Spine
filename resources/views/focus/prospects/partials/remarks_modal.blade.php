<div class="modal fade " id="remarksModal" tabindex="-1" role="dialog" aria-labelledby="remarksModal" aria-hidden="true">
    <div class="modal-dialog  modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-remarks-label">Remarks History</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div id="tableModal" class="mx-5">
               
            </div>
           
           
            <div class="mx-3">
               
                    <h5 class="mx-1 my-2">New Remark</h5>
                    {{ Form::open(['id'=>'remarkform']) }}
                    <div class="form-group column">
                        {{ Form::hidden('prospect_id',null,['class'=>'form-control','id'=>'prospect_id']) }}
                        <div class="col-sm-12"><label for="reminder_date" class="caption">Next Reminder Date</label>
                            <div class="input-group">
                                <div class="input-group-addon"><span class="icon-calendar4" aria-hidden="true"></span></div>
                                {{ Form::text('reminder_date', null, ['class' => 'form-control datepicker', 'placeholder' => 'Date', 'id' => 'reminder_date' ,'required']) }}
                            </div>
                        </div>
                        <div class="col-sm-12"><label for="recepient" class="caption">Recepient Name</label>
                            <div class="input-group">
                                <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                {{ Form::text('recepient', null, ['class' => 'form-control ', 'placeholder' => 'Name', 'id'=>'recepient' ,'required']) }}
                            </div>
                        </div>
                        <div class="col-sm-12"><label for="remarks" class="caption">Remarks</label>
                            <div class="input-group">
                                <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                {{ Form::textarea('remarks', null, ['class' => 'form-control','rows' => 3, 'placeholder' => 'Remark','id'=>'remarks','required']) }}
                            </div>
                        </div>
                        
                            {{ Form::button("Save Remark",['class'=>'mx-1 my-2 btn btn-md btn-primary','id'=>'save_remark']) }}
                        {{ Form::close() }}   
                    </div>
        
                         
            
            </div>
        
        </div>
    </div>
</div>

