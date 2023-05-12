
<div class="row">
    <fieldset class="form-group col-12"> 
        {{ Form::text('name', null, ['class' => 'new-todo-item form-control required', 'placeholder' => trans('projects.name')]) }}
    </fieldset>
</div>
<div class="row">
    <fieldset class="form-group col-md-4">
        <select class="custom-select required" id="todo-select" name="status">
            <option value="">-- select status --</option>
            @foreach($statuses as $row)
                <option value="{{ $row->id }}" {{ $project->status == $row->id? 'selected' : '' }}>
                    {{ $row->name }}
                </option>
            @endforeach
        </select>
    </fieldset>

    <fieldset class="form-group col-md-4">
        <select class="custom-select required" id="todo-select" name="priority">
            <option value="">-- select priority --</option>
            @foreach (['low', 'medium', 'high', 'urgent'] as $val)
                <option value="{{ $val }}" {{ in_array($project->priority, [$val, ucfirst($val)]) ? 'selected' : '' }}>
                    {{ ucfirst($val) }}
                </option>
            @endforeach
        </select>
    </fieldset>

    <fieldset class="form-group col-md-4">
        <select class="form-control select-box" name="tags[]" id="tags" data-placeholder="{{ trans('tags.select') }}" multiple>
            @foreach($tags as $row)
                <option value="{{ $row->id }}" {{ in_array($row->id, (@$project->tags->pluck('id')->toArray() ?: [])) ? 'selected' : '' }}>
                    {{ $row->name }}
                </option>
            @endforeach
        </select>
    </fieldset>
</div>
<fieldset class="form-group position-relative has-icon-left col-12">
    <div class="form-control-position"><i class="icon-emoticon-smile"></i></div>
    {{ Form::text('short_desc', null, ['class' => 'new-todo-desc form-control required', 'placeholder' => trans('tasks.short_desc'), 'id' => 'new-todo-desc']) }}
</fieldset>
<fieldset class="form-group col-12">
    {{ Form::textarea('note', null, ['class' => 'new-todo-item form-control required', 'placeholder' => trans('tasks.description'), 'rows' => '5']) }}
</fieldset>
<div class="form-group row">
    <div class="col-md-4 col-xs-12 mt-1">
        <div class="row">
            <label class="col-sm-4 col-xs-6 control-label" for="sdate">
                {{ trans('meta.from_date') }}
            </label>
            <div class="col-sm-6 col-xs-6">
                {{ Form::text('start_date', null, ['class' => 'form-control from_date required', 'data-toggle' => 'datepicker']) }}
                {{ Form::time('time_from', timeFormat(@$project->start_date), ['class' => 'form-control']) }}
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xs-12 mt-1">
        <div class="row">
            <label class="col-sm-4 col-xs-6  control-label" for="sdate">{{ trans('meta.to_date') }}</label>
            <div class="col-sm-6 col-xs-6 ">
                {{ Form::text('end_date', null, ['class' => 'form-control to_date required', 'data-toggle' => 'datepicker']) }}
                {{ Form::time('time_to', timeFormat(@$project->end_date), ['class' => 'form-control']) }}
            </div>
        </div>
    </div>

    <div class="col-md-4 col-xs-12 mt-1">
        <div class="row">
            <label class="col-sm-4 col-xs-6 control-label" for="sdate">{{trans('tasks.link_to_calender')}}</label>
            @if(@$project->events)
                <div class="col-sm-6 col-xs-6">
                    <input type="checkbox" class="form-control" name="link_to_calender" checked>
                    {{ Form::text('color', $project->events->first()->color, ['class' => 'form-control round', 'id'=>'color']) }}
                </div>
            @else
                <div class="col-sm-6 col-xs-6">
                    <input type="checkbox" class="form-control" name="link_to_calender">
                    {{ Form::text('color', '#0b97f4', ['class' => 'form-control round', 'id'=>'color']) }}
                </div>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <fieldset class="form-group col-md-6">
        {{ Form::text('phase', null, ['class' => 'new-todo-item form-control', 'placeholder' => trans('projects.phase')]) }}
    </fieldset>

    <fieldset class="form-group col-md-3">
        {{ Form::text('worth', @$project->worth? numberFormat(@$project->worth) : '', ['class' => 'new-todo-item form-control', 'placeholder' => 'Estimated Budget']) }}
    </fieldset>
    <fieldset class="form-group col-md-3">
        <select class="form-control select-box" name="project_share" data-placeholder="{{trans('projects.project_share')}}">
            <option value="{{ @$projectshare }}" selected>-- {{trans('projects.project_share')}} --</option>
            @php
                $shares_types = [
                    trans('projects.private'),
                    trans('projects.internal'),
                    trans('projects.external'),
                    trans('projects.internal_participate'),
                    trans('projects.external_participate'),
                    trans('projects.global_participate'),
                    trans('projects.global_view')
                ];
            @endphp
            @foreach ($shares_types as $i => $val)
                <option value="{{ $i }}" {{ $i == $project->project_share? 'selected' : '' }}>
                    {{ $val }}
                </option>
            @endforeach
        </select>
    </fieldset>
</div>

<div class="row">
    <fieldset class="form-group position-relative has-icon-left col-md-4">
        <select class="form-control select-box" name="employees[]" id="employee" data-placeholder="{{trans('tasks.assign')}}" multiple>
            @foreach($employees as $employee)
                <option value="{{ $employee['id'] }}" {{ in_array($employee->id, (@$project->users->pluck('id')->toArray() ?: []))? 'selected' : '' }}>
                    {{ $employee['first_name'] }} {{ $employee['last_name'] }} 
                </option>
            @endforeach
        </select>
    </fieldset>

    <fieldset class="form-group position-relative has-icon-left  col-md-4">
        <select id="person" name="customer_id" class="form-control required select-box"  data-placeholder="{{trans('customers.customer')}}" >
            @isset($project->customer)
                <option value="{{ $project->customer->id }}">
                    {{ $project->customer->name}} - {{ $project->customer->company}}
                </option>
            @endisset
        </select>
    </fieldset>

    <fieldset class="form-group position-relative has-icon-left  col-md-4">
        <select id="branch_id" name="branch_id" class="form-control required select-box"  data-placeholder="Choose Branch" >
            <option value="">-- Select Branch --</option>
            @isset($project->branch)
                <option value="{{ $project->branch->id }}" selected>
                    {{ $project->branch->name}}
                </option>
            @endisset
        </select>
    </fieldset>
</div>
