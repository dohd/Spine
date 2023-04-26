<div class="card-body">
    <div class="row">
        <div class="form-group col-12">
            <label for="name">Project Title</label>
            {{ Form::text('name', null, ['class' => 'new-todo-item form-control required', 'placeholder' => trans('projects.name')]) }}
        </div>
    </div>
    <div class="row">
        <div class="form-group col-4">
            <label for="status">Select Status</label>
            <select class="custom-select required" id="todo-select" name="status">
                <option value="">-- select status --</option>
                @foreach($statuses as $row)
                    <option value="{{ $row->id }}" {{ $project->status == $row->id? 'selected' : '' }}>
                        {{ $row->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group col-4">
            <label for="priority">Select Priority</label>
            <select class="custom-select required" id="todo-select" name="priority">
                <option value="">-- select priority --</option>
                @foreach (['low', 'medium', 'high', 'urgent'] as $val)
                    <option value="{{ $val }}" {{ in_array($project->priority, [$val, ucfirst($val)]) ? 'selected' : '' }}>
                        {{ ucfirst($val) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group col-4">
            <label for="tags">Select Tags</label>
            <select class="form-control select-box" name="tags[]" id="tags" data-placeholder="{{ trans('tags.select') }}" multiple>
                @foreach($tags as $row)
                    <option value="{{ $row->id }}" {{ in_array($row->id, (@$project->tags->pluck('id')->toArray() ?: [])) ? 'selected' : '' }}>
                        {{ $row->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group col-12">
        <label for="short_desc">Short Description</label>
        {{ Form::text('short_desc', null, ['class' => 'new-todo-desc form-control required', 'placeholder' => trans('tasks.short_desc'), 'id' => 'new-todo-desc']) }}
    </div>
    <div class="form-group col-12">
        <label for="note">Long Description</label>
        {{ Form::textarea('note', null, ['class' => 'new-todo-item form-control required', 'placeholder' => trans('tasks.description'), 'rows' => '5']) }}
    </div>
    <div class="row">
        <div class="form-group col-3">
            <label for="sdate">
                {{ trans('meta.from_date') }}
            </label>
            {{ Form::text('start_date', null, ['class' => 'form-control from_date required', 'data-toggle' => 'datepicker']) }}
            {{ Form::time('time_from', timeFormat(@$project->start_date), ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-3">
            <label for="sdate">{{ trans('meta.to_date') }}</label>
            {{ Form::text('end_date', null, ['class' => 'form-control to_date required', 'data-toggle' => 'datepicker']) }}
            {{ Form::time('time_to', timeFormat(@$project->end_date), ['class' => 'form-control']) }}
        </div>

        <div class="form-group col-3">
            <label for="sdate">{{trans('tasks.link_to_calender')}}</label>
            @if (@$project->events)
            <input type="checkbox" class="form-control" name="link_to_calender" checked>
            @else
            <input type="checkbox" class="form-control" name="link_to_calender">
            @endif
        </div>
        <div class="form-group col-3">
            <label for="color">Select Color</label>
            @if (@$project->events)
            {{ Form::text('color', $project->events->first()->color, ['class' => 'form-control round', 'id'=>'color']) }}
            @else
            {{ Form::text('color', '#0b97f4', ['class' => 'form-control round', 'id'=>'color']) }}
            @endif
        </div>
    </div>

    <div class="row">
        <div class="form-group col-4">
            <label for="sdate">Project Phase</label>
            {{ Form::text('phase', null, ['class' => 'new-todo-item form-control', 'placeholder' => trans('projects.phase')]) }}
        </div>
        
        <div class="form-group col-4">
            <label for="worth">Extimated Cost</label>
            {{ Form::text('worth', numberFormat(@$project->worth), ['class' => 'new-todo-item form-control']) }}
        </div>
        <div class="form-group col-4">
            <label for="share">Project Share</label>
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
        </div>
    </div>

    <div class="row">
        <div class="form-group position-relative has-icon-left col-md-4">
            <label for="assign_to">Assign To:</label>
            <select class="form-control select-box" name="employees[]" id="employee" data-placeholder="{{trans('tasks.assign')}}" multiple>
                @foreach($employees as $employee)
                    <option value="{{ $employee['id'] }}" {{ in_array($employee->id, (@$project->users->pluck('id')->toArray() ?: []))? 'selected' : '' }}>
                        {{ $employee['first_name'] }} {{ $employee['last_name'] }} 
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group position-relative has-icon-left  col-md-4">
            <label for="customer">Search Customer</label>
            <select id="person" name="customer_id" class="form-control required select-box"  data-placeholder="{{trans('customers.customer')}}" >
                @isset($project->customer)
                    <option value="{{ $project->customer->id }}">
                        {{ $project->customer->name}} - {{ $project->customer->company}}
                    </option>
                @endisset
            </select>
        </div>

        <div class="form-group position-relative has-icon-left  col-md-4">
            <label for="branch">Select Branch</label>
            <select id="branch_id" name="branch_id" class="form-control required select-box"  data-placeholder="Choose Branch" >
                @isset($project->branch)
                    <option value="{{ $project->branch->id }}">
                        {{ $project->branch->name}}
                    </option>
                @endisset
            </select>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-4">
            <label for="quote">Primary / Main Quote</label>
            <select required id="main_quote" name="main_quote" class="form-control required select-box" data-placeholder="Primary / Main Quote">
            </select>
        </div>
    </div>
</div>
