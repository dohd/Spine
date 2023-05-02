<div class="tab-pane" id="tab_data3" aria-labelledby="tab3" role="tabpanel">
    @if (access()->allow('task-create') and project_access($project->id))
        <button type="button" class="btn btn-info" data-toggle="modal"
            data-target="#AddTaskModal">
            {{ trans('tasks.new_task') }}
        </button>
    @endif
    <div class="card-body">
        <table id="tasks-table" class="table table-striped table-bordered zero-configuration"
            cellspacing="0" width="100%">

            <thead>
                <tr>
                    <th>{{ trans('tasks.task') }}</th>
                    <th>{{ trans('tasks.start') }}</th>
                    <th>{{ trans('tasks.duedate') }}</th>
                    <th>{{ trans('tasks.status') }}</th>


                    <th>{{ trans('labels.general.actions') }}</th>
                </tr>
            </thead>

            <tbody></tbody>
        </table>
    </div>

</div>