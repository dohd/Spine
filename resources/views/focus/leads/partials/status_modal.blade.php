<div class="modal fade" id="status-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Update Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($lead, ['route' => ['biller.leads.update_status', $lead->id] ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" name="status" id="status">
                            <option value="0">Open</option>
                            <option value="1">Closed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reason">Reason</label>
                        <select class="form-control" name="reason" id="reason">
                            <option value="new">New</option>
                            <option value="won">Won</option>
                            <option value="lost">Lost</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Update', ['class' => "btn btn-primary"]) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>