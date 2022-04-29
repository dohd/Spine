<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModal" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Issuance Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::open(['route' => 'biller.issuance.update_status', 'method' => 'POST']) }}
                <div class="modal-body">
                    <div class="form-group">
                        <input type="hidden" name="id" value="{{ $issuance->id }}">
                        <select name="status" class="form-control" id="">
                            @foreach (['partial', 'complete'] as $val)
                                <option value="{{ $val }}" {{ $issuance->quote->issuance_status == $val? 'selected' : '' }}>
                                    {{ strtoupper($val) }}
                                </option>
                            @endforeach
                        </select>                        
                    </div>
                    <div class="form-group float-right">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        {{ Form::submit('Update', ['class' => 'btn btn-primary']) }}
                    </div>
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>