<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Approve Payroll</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('biller.payroll.approve_payroll') }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status"> Status</label>
                        <select class="form-control" name="status" id="status">
                            @foreach (['pending','approved', 'rejected'] as $val)
                                <option value="{{ $val }}">
                                    {{ ucfirst($val) }}
                                </option>
                            @endforeach                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
                        {{ Form::text('approval_date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
                        <input type="hidden" name="id" value="{{ @$payroll->id}}">
                    </div>
                    <div class="form-group">
                        <label for="amount">Total NetPay</label>
                        {{ Form::text('amount', amountFormat(@$payroll->total_netpay), ['class' => 'form-control', 'id' => 'amount', 'readonly']) }}
                    </div>
                    <div class="form-group">
                        <label for="note">Note</label>
                        {{ Form::textarea('approval_note', null, ['class' => 'form-control', 'rows' => '4', 'id' => 'note']) }}
                    </div>
                    {{-- <div class="form-group">
                        <label for="account">Pay From Account</label>
                        <select name="account_id" id="account" class="custom-select" required>  
                            <option value="">-- select account --</option>                                 
                            @foreach ($accounts as $row)
                                <option value="{{ $row->id }}" {{ $row->id == @$billpayment->account_id? 'selected' : '' }}>
                                    {{ $row->holder }}
                                </option>
                            @endforeach
                        </select>
                    </div>  --}}
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Save', ['class' => "btn btn-primary"]) }}
                </div>
            </form>
        </div>
    </div>
</div>