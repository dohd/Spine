
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
</div>