
   
{!! Form::hidden('prospect_id', null, [
    'class' => 'form-control ',
    'id' => 'hidden_prospect',
]) !!}
<div hidden class="form-group">
    <p >Do you have an ERP</p>
    <div class="d-inline-block">
        <input class="hiddenerp-status" type="radio" id="yes" name="erp" value="1"   hidden>
        <label for="yes">Yes</label><br>
    </div>
    <div class="d-inline-block">
        <input class="hiddenerp-status" type="radio" id="no" name="erp" value="0" checked  hidden>
        <label for="no">No</label><br>
    </div>
</div>


<div id="hiddenerp_div" >
    <div hidden class="form-group row">
        <div class="col-md-6">
            <p >Which One</p>
            {!! Form::hidden('current_erp', null, [
                'class' => 'form-control ',
                'placeholder' => 'Current ERP',
                'id' => 'hiddencurrent_erp',
                
            ]) !!}
        </div>
        <div hidden class="col-md-6">
            <p >How long have you been using the ERP</p>
            {!! Form::hidden('current_erp_usage', null, [
                'class' => 'form-control ',
                'placeholder' => 'Weeks/Months/Years',
                'id' => 'hiddencurrent_erp_usage',
                
            ]) !!}
        </div>
    
    </div>
    <div hidden class="form-group">
        <p >Do you have any challenges in your existing ERP</p>
        <div class="d-inline-block">
            <input class="challenges-status" type="radio" id="yes" name="erp_challenges" value="1" hidden >
            <label for="yes">Yes</label><br>
        </div>
        <div class="d-inline-block">
            <input class="challenges-status" type="radio" id="no" name="erp_challenges" value="0" checked >
            <label for="no">No</label><br>
        </div>
    </div>
    <div hidden id="erpchallenges" class="form-group row">
        <div class="col-md-12">
            <p >State some of the challenges</p>
            {!! Form::hidden('current_erp_challenges', null, [
                'class' => 'form-control ',
                'rows' => 3,
                'placeholder' => 'Challenges',
                
                'id' => 'hiddencurrent_erp_challenges',
                
            ]) !!}
        </div>
    </div>
</div>

<div hidden  class="form-group">
    <p >Are you interested in us showing you a demo</p>
    <div class="d-inline-block">
        <input class="hiddendemo-status" type="radio" id="yes" name="erp_demo" value="1"  hidden>
        <label for="yes">Yes</label><br>
    </div>
    <div class="d-inline-block">
        <input class="hiddendemo-status" type="radio" id="no" name="erp_demo" value="0" checked hidden>
        <label for="no">No</label><br>
    </div>
</div>

<div hidden id="hiddendemo" class="form-group row">
    <div class="col-md-6">
        <p >When do you think is the appropriate date</p>
        {!! Form::hidden('demo_date', null, [
            'class' => 'form-control ',
            'placeholder' => 'Demo date',
            'id' => 'hiddendemo_date',
            
        ]) !!}
    </div>

    <div hidden class="col-md-6">
        <p >Any Remarks?</p>
        {!! Form::hidden('notes', null, ['class' => 'form-control ', 'placeholder' => 'Notes/Remarks', 'id' => 'hiddennotes']) !!}
    </div>
</div>


{{-- <div class="form-group">
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
        {!! Form::text('reminder_date', null, [
            'class' => 'form-control ',
            'placeholder' => 'Reminder date',
            'id' => 'reminder_date',
        ]) !!}
    </div>
    <div class="col-md-6">
        <p>Reminder Note</p>
        {!! Form::text('reminder_note', null, [
            'class' => 'form-control ',
            'placeholder' => 'Reminder note',
            'id' => 'reminder_note',
        ]) !!}
    </div>
</div> --}}