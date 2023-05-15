<div class="tab-pane" id="tab_data6" aria-labelledby="tab6" role="tabpanel">
    <button type="button" class="btn btn-info float-right mr-2" data-toggle="modal" data-target="#AddNoteModal">
        <i class="fa fa-plus-circle"></i> Note
    </button>
    <div class="card-body">
        <table id="notes-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ trans('general.title') }}</th>
                    <th>Content</th>
                    <th>Created By</th>
                    <th>Created At</th>
                    <th>{{ trans('general.action') }}</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>