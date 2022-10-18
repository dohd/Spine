<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Project Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($project, ['route' => array('biller.projects.update', $project), 'method' => 'PATCH' ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" name="status" id="status">
                            @foreach (['open', 'closed'] as $val)
                                <option value="{{ $val }}" {{ $val == $project->status? 'selected' : '' }}>
                                    {{ ucfirst($val) }}
                                </option>
                            @endforeach                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reason">Reason</label>
                        {{ Form::text('end_note', null, ['class' => 'form-control']) }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Submit', ['class' => "btn btn-primary"]) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>