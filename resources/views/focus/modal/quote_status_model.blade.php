<div id="pop_model_1" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Approval</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                {{ Form::open(['route' => ['biller.quotes.approve_quote', $quote], 'method' => 'POST', 'id' => 'form-approve']) }}
                    <div class="row">
                        <div class="col mb-1">
                            <label for="status">{{trans('general.mark_as')}}</label>
                            <select name="status" class="form-control mb-1" required>
                                <option value="0">-- Select Status --</option>
                                <option value="pending">{{ trans('payments.pending') }}</option>
                                <option value="approved">{{ trans('payments.approved') }}</option>
                                <option value="cancelled">{{ trans('payments.canceled') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-1"><label for="approval-date">Approval Date</label>
                            <input type="text" class="form-control mb-1 datepicker" name="approved_date" id="approveddate" required />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-1"><label for="approval-method">Approval Method</label>
                            <select class="form-control mb-1" name="approved_method" id="approvedmethod" required>
                                <option value="None">-- Select Method --</option>
                                <option value="Email">Email</option>
                                <option value="SMS">SMS</option>
                                <option value="Whatsapp">Whatsapp</option>
                                <option value="Call">Call</option>
                                <option value="LPO">LPO</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-1"><label for="approved-by">Approved By</label>
                            <input type="text" class="form-control" name="approved_by" id="approvedby" placeholder="Approved By" required />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-1"><label for="note">{{trans('general.note')}}</label>
                            <input type="text" class="form-control" name="approval_note" placeholder="{{trans('general.note')}}" required />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" data-dismiss="modal">{{trans('general.close')}}</button>                       
                        <button type="submit" class="btn btn-primary" id="btn_approve">Approve</button> 
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>