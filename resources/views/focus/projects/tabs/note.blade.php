<div class="tab-pane" id="tab_data6" aria-labelledby="tab6" role="tabpanel">
    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#AddNoteModal">
        {{ trans('general.new') }}
    </button>

    <div class="card-body">
        <table id="notes-table" class="table table-striped table-bordered zero-configuration"
            cellspacing="0" width="100%">

            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ trans('general.title') }}</th>
                    <th>{{ trans('general.date') }}</th>
                    <th>{{ trans('projects.users') }}</th>
                    <th>{{ trans('general.action') }}</th>

                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

</div>