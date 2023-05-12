
{!! Form::hidden('prospect_id', null, [
    'class' => 'form-control ',
    
    'id' => 'busyprospect_id',
]) !!}
{!! Form::hidden('call_id', null, [
    'class' => 'form-control ',
    
    'id' => 'busycall_id',
]) !!}

<div class="form-group row">
    <div class="col-md-6">
        <p>Reminder Date</p>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <input type="datetime-local" name="reminder_date" id="busy_reminder_date" class="form-control"/>
        </div>
    </div>
    <div class="col-md-6">
        <p>Reminder Note</p>
        {!! Form::text('any_remarks', null, [
            'class' => 'form-control ',
            'placeholder' => 'Reminder note',
            'id' => 'busy_reminder_notes',
        ]) !!}
    </div>
</div>