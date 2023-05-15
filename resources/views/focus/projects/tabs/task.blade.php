<div class="tab-pane" id="tab_data3" aria-labelledby="tab3" role="tabpanel">
    <button type="button" class="btn btn-info float-right mr-2" data-toggle="modal" data-target="#AddTaskModal">
        <i class="fa fa-plus-circle"></i> Task
    </button>
    <div class="card-body">
        <table id="tasks-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Milestone</th>
                    <th>{{ trans('tasks.task') }}</th>
                    <th>{{ trans('tasks.start') }}</th>
                    <th>Due Date</th>
                    <th>{{ trans('tasks.status') }}</th>
                    <th>Assigned To</th>
                    <th>{{ trans('labels.general.actions') }}</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
