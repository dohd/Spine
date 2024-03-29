<!-- cancel -->
<div id="pop_model_3" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h4 class="modal-title">{{trans('quotes.convert_invoice')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form_model_3">

                    <div class="alert alert-danger p-1 round">
                        <input type="checkbox" name="delete_item"
                               value="{{$invoice['id']}}"> {{trans('quotes.convert_invoice_delete')}}
                    </div>


                    <div class="modal-footer">
                        <input type="hidden" name="bill_id" value="{{$invoice['id']}}">
                        <input type="hidden" name="bill_type" value="{{$invoice['bill_type']}}">
                        <input type="hidden" id="action-url_3" value="{{route('biller.quotes.convert')}}">
                        <button type="button" class="btn btn-info"
                                data-dismiss="modal">{{trans('general.close')}}</button>
                        <button type="button" class="btn btn-success submit_model"
                                id="submit_model_3" data-itemid="3">{{trans('quotes.convert')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
