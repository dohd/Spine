<div class="modal" tabindex="-1" role="dialog" id="mergedLog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Stock Issuance Log</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table id="mergedLogTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>  
                            <th>Name</th>
                            <th>UoM</th>
                            <th>Qty</th>
                            <th>Reqxn No.</th>
                            <th>Warehouse</th>
                            <th>Date</th> 
                            <th>Action</th>                                                  
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="text-center text-success font-large-1"><i class="fa fa-spinner spinner"></i></td>
                        </tr>
                    </tbody>                   
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="post-stock">Post Issued Stock</button>
            </div>
        </div>
    </div>
</div>
