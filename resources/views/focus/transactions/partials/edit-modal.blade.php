<div class="modal fade" id="editTrModal" tabindex="-1" role="dialog" aria-labelledby="editTrModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Transaction</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{ Form::open(['route' => ['biller.transactions.update', $tr], 'method' => 'PATCH']) }}
                    <div class="form-group">
                        <label for="account">Account</label>
                        <select name="account_id" class="form-control" id="account" data-id="{{ $tr->account_id }}" data-placeholder="Search Account">
                        </select>                        
                    </div>
                    @if ($tr->debit > 0)
                        <div class="form-group">
                            <label for="debit">Debit</label>
                            <input type="text" class="form-control" name="debit" value="{{ number_format($tr->debit, 2) }}">
                            <input type="hidden" name="credit" value="0.00">
                        </div>
                    @elseif ($tr->credit > 0)
                        <div class="form-group">
                            <label for="credit">Credit</label>
                            <input type="text" class="form-control" name="credit" value="{{ number_format($tr->credit, 2) }}">
                            <input type="hidden" name="debit" value="0.00">
                        </div>
                    @endif
                    <div class="form-group">
                        <label for="note">Note</label>
                        <input type="text" class="form-control" name="note" value="{{ $tr->note }}">
                    </div>
                    <div class="form-group float-right">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        {{ Form::submit('Update', ['class' => 'btn btn-primary']) }}
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>