<div class="tab-pane" id="tab_data2" aria-labelledby="tab2" role="tabpanel">
    {{-- @if(project_access($project->id)) --}}
        <button type="button" class="btn btn-info" id="addt" data-toggle="modal" data-target="#AddMileStoneModal">
            <i class="fa fa-plus-circle"></i> Milestone
        </button>
    {{-- @endif --}}
    <ul class="timeline">
        @php
            $flag = true;
            $total = count($project->milestones);
        @endphp
        @foreach ($project->milestones as $row)
            <li class="{!! (!$flag)? 'timeline-inverted' : '' !!}" id="m_{{$row['id']}}">
                <div class="timeline-badge" style="background-color:@if ($row['color']) {{$row['color']}} @else #0b97f4  @endif;">
                    {{$total}}
                </div>
                <div class="timeline-panel">
                    <div class="timeline-heading">
                        <h4 class="timeline-title">{{$row['name']}}</h4>
                        <p>
                            <small class="text-muted">
                                [{{trans('general.due_date')}} {{dateTimeFormat($row['due_date'])}}]
                            </small>
                        </p>
                    </div>
                    {{-- @if (project_access($project->id)) --}}
                        <div class="timeline-body mb-1">
                            <p>{{$row['note']}}</p>
                            <p>Estimated Milestone Amount: <b>{{ amountFormat($row['estimated_milestone_amount']) }}</b></p>
                        </div>
                    {{-- @endif --}}
                    <small class="text-muted"><i class="fa fa-user"></i>
                        <strong>{{ @$row->creator->fullname }}</strong>
                        <i class="fa fa-clock-o"></i> {{trans('general.created')}} {{dateTimeFormat($row['created_at'])}}
                    </small>
                    <div>
                        <div class="btn-group">
                            <a href="#" class="edit-object mr-1"  data-object-id="{{$row['id']}}">
                                <i class="ft ft-edit" style="font-size: 1.5em"></i>
                            </a>

                            <a href="#" class="delete-object mr-1" data-object-type="2" data-object-id="{{$row['id']}}">
                                <i class="fa fa-trash fa-lg danger"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </li>
            @php
                $flag = !$flag;
                $total--;
            @endphp
        @endforeach
    </ul>
</div>