<div id="pop_model_1" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h4 class="modal-title">{{trans('general.change_status')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>

            <div class="modal-body">
                <form id="form_model_1">
                   





                    <div class="row">
                        <div class="col mb-1"><label
                                    for="pmethod">{{trans('general.mark_as')}}</label>
                            <select name="status" class="form-control mb-1">
                                <option value="pending">{{trans('payments.pending')}}</option>
                                <option value="approved">{{trans('payments.approved')}}</option>
                                <option value="canceled">{{trans('payments.canceled')}}</option>

                            </select>

                        </div>
                    </div>


                    <div class="row">
                        <div class="col mb-1"><label
                                    for="pmethod">Approval Date</label>

                                     <input type="text" class="form-control mb-1"
                                       placeholder="{{trans('general.payment_date')}}" name="approved_date"
                                       data-toggle="datepicker">


                                       
                           

                        </div>
                    </div>

                    <div class="row">
                        <div class="col mb-1"><label
                                    for="pmethod">Approval Method</label>
                            <select name="approved_method" class="form-control mb-1">
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
                        <div class="col mb-1"><label
                                    for="note">Approved By</label>
                            <input type="text" class="form-control"
                                   name="approved_by" placeholder="Approved By"
                                   value="{{ $words['pay_note']}}"></div>
                    </div>

                      <div class="row">
                        <div class="col mb-1"><label
                                    for="note">{{trans('general.note')}}</label>
                            <input type="text" class="form-control"
                                   name="approval_note" placeholder="{{trans('general.note')}}"
                                   value="{{ $words['pay_note']}}"></div>
                    </div>

                    <div class="modal-footer">
                        <input type="hidden"
                               name="bill_id" value="{{$invoice['id']}}">
                        <input type="hidden"
                               name="bill_type" value="{{$invoice['bill_type']}}">
                        <button type="button" class="btn btn-warning"
                                data-dismiss="modal">{{trans('general.close')}}</button>
                        <input type="hidden" id="action-url_1" value="{{route('biller.quotes.bill_status')}}">
                        <button type="button" class="btn btn-primary submit_model"
                                id="submit_model_1" data-itemid="1">{{trans('general.change_status')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>