<div id="pop_model_4" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h4 class="modal-title">Add LPO</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>

            <div class="modal-body">
                <form id="form_model_4">
                   





                   


                    <div class="row">
                        <div class="col mb-1"><label
                                    for="pmethod">LPO  Date</label>

                                     <input type="text" class="form-control mb-1"
                                       placeholder="LPO  Date" name="lpo_date"
                                       data-toggle="datepicker">


                                       
                           

                        </div>
                    </div>

                  

                      <div class="row">
                        <div class="col mb-1"><label
                                    for="note">LPO Amount</label>
                            <input type="text" class="form-control"
                                   name="lpo_amount" placeholder="LPO Amount"
                                   value="{{ $words['pay_note']}}"></div>
                    </div>

                      <div class="row">
                        <div class="col mb-1"><label
                                    for="note">LPO Number</label>
                            <input type="text" class="form-control"
                                   name="lpo_number" placeholder="LPO Number"
                                   value="{{ $words['pay_note']}}"></div>
                    </div>

                    <div class="modal-footer">
                        <input type="hidden"
                               name="bill_id" value="{{$invoice['id']}}">
                        <input type="hidden"
                               name="bill_type" value="{{$invoice['bill_type']}}">
                        <button type="button" class="btn btn-warning"
                                data-dismiss="modal">{{trans('general.close')}}</button>
                        <input type="hidden" id="action-url_4" value="{{route('biller.quotes.lpo')}}">
                        <button type="button" class="btn btn-primary submit_model"
                                id="submit_model_4" data-itemid="4">Update Lpo Details</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>