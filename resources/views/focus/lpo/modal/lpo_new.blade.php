<div class="modal fade" id="AddLpoModal" tabindex="-1" role="dialog" aria-labelledby="AddLpoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create LPO</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ route('biller.lpo.store') }}" method="POST" id="lpo-form">
            @csrf
            <div class="row">
                <div class="form-group col-6">
                    <div><label for="customer">Search Customer</label></div>
                    <select id="person" name="customer_id" class="form-control select-box" data-placeholder="{{ trans('customers.customer') }}" required>
                    </select>
                </div>
                <div class="form-group col-6">
                    <div><label for="branch">Branch</label></div>
                    <select id="branch_id" name="branch_id" class="form-control select-box" data-placeholder="Branch">
                    </select>
                </div>                
            </div> 
            <div class="row">
                <div class="form-group col-4">
                    <div><label for="lpo_date">Date</label></div>
                    <input type="date" name="date" class="form-control">
                </div>
                <div class="form-group col-4">
                    <div><label for="lpo_no">LPO Number</label></div>
                    <input type="text" name="lpo_no" class="form-control">
                </div>
                <div class="form-group col-4">
                    <div><label for="lpo_amount">Amount</label></div>
                    <input type="number" name="amount" class="form-control">
                </div>
            </div>  
            <div class="row">                
                <div class="form-group col-10">
                    <div><label for="lpo_amount">Detail & Remarks</label></div>
                    <textarea name="remark" id="remark" cols="100" rows="8" class="form-control"></textarea>
                </div>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary" id="create-btn">Create</button>
      </div>
    </div>
  </div>
</div>
