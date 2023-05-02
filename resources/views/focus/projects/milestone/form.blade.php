<div class="row">
    <fieldset class="form-group col-12">
        <input type="hidden" name="project_id" value="{{$milestone->project_id}}">
        <input type="text" class="new-todo-item form-control"
               placeholder="{{trans('additionals.name')}}" value="{{$milestone->name}}" name="name">
    </fieldset>
</div>


<fieldset class="form-group">
    <textarea class="new-todo-item form-control"  placeholder="{{trans('tasks.description')}}"
              rows="6" name="description">{{$milestone->note}}</textarea>
</fieldset>
<div class="form-group row mt-3">
    <div class="col-4">
        <label for="sdate">{{trans('general.due_date')}}</label>
        <input type="text" class="form-control required to_date"
                       placeholder="End Date" name="duedate" value="{{$milestone->duedate}}"
                       data-toggle="datepicker" autocomplete="false">
                <input type="time" name="time_to" class="form-control" value="23:59">
    </div>
    <div class="col-4">
        {{ Form::label( 'color', trans('miscs.color'),['class' => 'col-2 control-label']) }}
        {{ Form::text('color', $milestone->color, ['class' => 'form-control round', 'id'=>'color','placeholder' => trans('miscs.color'),'autocomplete'=>'off']) }}
    </div>
    <div class="col-4">
        <label for="extimated_milestone"> <span class="text-primary">(Extimated Milestone Amount: <span class="extimate font-weight-bold text-dark">0.00</span>)</span></label>
        <input type="number" class="form-control" value="{{$milestone->extimated_milestone_amount}}" name="extimated_milestone_amount" id="extimated-milestone" placeholder="0.0" required>
        <input type="hidden" id="limit">
    </div>
</div>