<div class="modal fade" id="returnItem" tabindex="-1" role="dialog" aria-labelledby="returnItemLabel" aria-hidden="true">
    <div class="modal-dialog mw-50" role="document">
        <div class="modal-content">
          <form action="{{ route('biller.assetreturned.send') }}" method="post">
            <div class="modal-header">
              <h5 class="modal-title" id="returnItemLabel">Returns Products</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body"> 
                <div class="card mt-3">
                    <div class="card-body">
                      @csrf
                         <div class="form-group">
                          <div class="col">
                              <label for="product_name">Item Name</label>
                              <input type="text" class="form-control" id="product-name" readonly>
                          </div>
                        </div>
                        <div class="form-group">
                          <div class="col">
                              <label for="qty_issued">Quantity Issued</label>
                              <input type="text" class="form-control" id="quantity-issued" name="qty_issued" readonly>
                          </div>
                        </div>
                        <div class="form-group">
                          <div class="col">
                              <label for="qty_returned">Returned Quantity</label>
                              <input type="text" class="form-control" id="qty-return" name="qty_returned" readonly>
                          </div>
                        </div>
                        <div class="form-group">
                          <div class="col">
                              <label for="return">Return Items</label>
                              <input type="text" class="form-control" id="return-items" name="return">
                          </div>
                        </div>
                        <div class="form-group">
                          <div class="col">
                            <label for="lost">Lost Items</label>
                            <input type="text" class="form-control" id="lost-items" name="lost">
                        </div>
                        </div>
                        <div class="form-group">
                          <div class="col">
                            <label for="broken">Broken Items</label>
                            <input type="text" class="form-control" id="broken-item" name="broken">
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary submit">Submit</button>
            </div>
          </form>
        </div>
      </div>