
{!! Form::hidden('prospect_id', null, [
    'class' => 'form-control ',
    
    'id' => 'prospect_id',
]) !!}
<div class="form-group">
    <p>Do you have an ERP</p>
    <div class="d-inline-block">
        <input class="erp-status" type="radio" id="yes" name="erp" value="1" checked>
        <label for="yes">Yes</label><br>
    </div>
    <div class="d-inline-block">
        <input class="erp-status" type="radio" id="no" name="erp" value="0">
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
            <input class="challenges-status" type="radio" id="yes" name="erp_challenges" value="1" checked>
            <label for="yes">Yes</label><br>
        </div>
        <div class="d-inline-block">
            <input class="challenges-status" type="radio" id="no" name="erp_challenges" value="0">
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
        <input class="demo-status" type="radio" id="yes" name="erp_demo" value="1" checked>
        <label for="yes">Yes</label><br>
    </div>
    <div class="d-inline-block">
        <input class="demo-status" type="radio" id="no" name="erp_demo" value="0">
        <label for="no">No</label><br>
    </div>
</div>

<div id="demo" class="form-group row">
    <div class="col-md-6">
        <p>When do you think is the appropriate date</p>
        {!! Form::text('demo_date', null, [
            'class' => 'form-control ',
            'placeholder' => 'Demo date',
            'id' => 'demo_date',
        ]) !!}
    </div>

    <div class="col-md-6">
        <p>Any Notes?</p>
        {!! Form::text('notes', null, ['class' => 'form-control ', 'placeholder' => 'Notes/Remarks', 'id' => 'notes']) !!}
    </div>
</div>