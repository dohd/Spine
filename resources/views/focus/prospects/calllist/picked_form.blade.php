
{!! Form::hidden('prospect_id', null, [
    'class' => 'form-control ',
    
    'id' => 'picked_prospect_id',
]) !!}
<div class="form-group row">
    <div class="col-sm-6"><label for="recepient" class="caption">Recepient Name</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
            {{ Form::text('recepient', null, ['class' => 'form-control ', 'placeholder' => 'Name', 'id'=>'picked_recepient' ]) }}
        </div>
    </div>
</div>
<div class="form-group">
    <p>Do you have an ERP</p>
    <div class="d-inline-block">
        <input class="erp-status" type="radio" id="yes" name="erp" value="1" checked  required>
        <label for="yes">Yes</label><br>
    </div>
    <div class="d-inline-block">
        <input class="erp-status" type="radio" id="no" name="erp" value="0" required>
        <label for="no">No</label><br>
    </div>
</div>


<div id="erp_div" >
    <div class="form-group row">
        <div class="col-md-6">
            <p>Which One</p>
            {!! Form::text('current_erp', null, [
                'class' => 'form-control ',
                'placeholder' => 'Current ERP',
                'id' => 'current_erp',
                
            ]) !!}
        </div>
        <div class="col-md-6">
            <p>How long have you been using the ERP</p>
            {!! Form::text('current_erp_usage', null, [
                'class' => 'form-control ',
                'placeholder' => 'Weeks/Months/Years',
                'id' => 'current_erp_usage',
                
            ]) !!}
        </div>
    
    </div>
    <div class="form-group">
        <p>Do you have any challenges in your existing ERP</p>
        <div class="d-inline-block">
            <input class="challenges-status" type="radio" id="yes" name="erp_challenges" value="1" checked  required>
            <label for="yes">Yes</label><br>
        </div>
        <div class="d-inline-block">
            <input class="challenges-status" type="radio" id="no" name="erp_challenges" value="0" required>
            <label for="no">No</label><br>
        </div>
    </div>
    <div id="erpchallenges" class="form-group row">
        <div class="col-md-12">
            <p>State some of the challenges</p>
            {!! Form::textarea('current_erp_challenges', null, [
                'class' => 'form-control ',
                'rows' => 3,
                'placeholder' => 'Challenges',
                
                'id' => 'current_erp_challenges',
                
            ]) !!}
        </div>
    </div>
</div>

<div  class="form-group">
    <p>Are you interested in us showing you a demo</p>
    <div class="d-inline-block">
        <input class="demo-status" type="radio" id="yes" name="erp_demo" value="1" checked required>
        <label for="yes">Yes</label><br>
    </div>
    <div class="d-inline-block">
        <input class="demo-status" type="radio" id="no" name="erp_demo" value="0" required>
        <label for="no">No</label><br>
    </div>
</div>

<div id="demo" class="form-group row">
    <div class="col-md-6">
        <p>When do you think is the appropriate date</p>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <input type="datetime-local" name="reminder_date" id="demo_date" class="form-control"/>
        </div>
        
    </div>
</div>

<div class="form-group row">
    <div class="col-md-6">
        <p>Any Remarks?</p>
        {!! Form::textarea('any_remarks', null, ['class' => 'form-control ', 'rows'=>3, 'placeholder' => 'Notes/Remarks', 'id' => 'notes']) !!}
    </div>
</div>
