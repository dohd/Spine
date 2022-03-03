<div class='form-group'>
    {{ Form::label( 'name', trans('additionals.name'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('name', null, ['class' => 'form-control round', 'placeholder' => 'Name E.g VAT']) }}
    </div>
</div>
<div class='form-group' id="value1">
    {{ Form::label( 'value', trans('additionals.value'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('value', null, ['class' => 'form-control round', 'placeholder' => 'Rate E.g 16']) }}
    </div>
</div>
<div class="form-group">
    {{ Form::label( 'default_a','Is Defalut',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        <select class="form-control round" name="default_a" id='default_a'>
            
            <option value="0">No</option>
            <option value="1">Yes</option>
        </select>
    </div>
</div>







