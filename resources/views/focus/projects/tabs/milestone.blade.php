<div class="tab-pane" id="tab_data2" aria-labelledby="tab2" role="tabpanel">
    @if (project_access($project->id))
        <button type="button" class="btn btn-info" id="addt" data-toggle="modal"
            data-target="#AddMileStoneModal">
            {{ trans('projects.milestone_add') }}
        </button>
    @endif
    <ul class="timeline">
        @php
            $flag = true;
            $total = count($project->milestones);
        @endphp
        @foreach ($project->milestones as $row)
            <li class="@if (!$flag) timeline-inverted @endif "
                id="m_{{ $row['id'] }}">
                <div class="timeline-badge"
                    style="background-color:@if ($row['color']) {{ $row['color'] }} @else #0b97f4 @endif;">
                    {{ $total }}</div>
                <div class="timeline-panel">
                    <div class="timeline-heading">
                        <h4 class="timeline-title">{{ $row['name'] }}</h4>
                        <p>
                            <small class="text-muted">
                                [{{ trans('general.due_date') }}
                                {{ dateTimeFormat($row['due_date']) }}
                                ]
                            </small>

                        </p>
                    </div>
                    @if (project_access($project->id))
                        <div class="timeline-body mb-1">
                            <p>{{ $row['note'] }}</p><a href="#" class=" delete-object"
                                data-object-type="2" data-object-id="{{ $row['id'] }}"><i
                                    class="danger fa fa-trash"></i></a>
                        </div>
                    @endif
                    <small class="text-muted"><i class="fa fa-user"></i>
                        <strong>{{ $row->creator->first_name }}
                            {{ $row->creator->last_name }}</strong>
                        <i class="fa fa-clock-o"></i> {{ trans('general.created') }}
                        {{ dateTimeFormat($row['created_at']) }}
                    </small>
                </div>
            </li>
            @php
                $flag = !$flag;
                $total--;
            @endphp
        @endforeach


    </ul>

</div>