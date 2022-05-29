<div class="form-group row">
    <div class="col-6">
        <label for="client">Client</label>
        <select name="ref_id" id="client" class="form-control" data-placeholder="Choose client" required></select>
        <input type="hidden" name="is_client" value="1" id="is_client">
    </div>
    <div class="col-6">
        <label for="supplier">Supplier</label>
        <select name="ref_id" id="supplier" class="form-control" data-placeholder="Choose supplier" required></select>
    </div>
</div>
<div class="table-responsive">
    <table id="listTbl" class="table table-sm tfr my_stripe_single text-center">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>#</th>
                <th>Product Name</th>
                <th width="20%">Price</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td><input type="text" class="form-control" name="name[]" id="name-0"></td>
                <td><input type="text" class="form-control" name="price[]" id="price-0"></td>
                <td><button class="btn btn-outline-light d-none remove"><i class="fa fa-trash fa-lg text-danger"></i></button></td>
                <input type="hidden" name="product_id[]" id="id-0">
            </tr>
        </tbody>
    </table>
</div>
<div class="form-group row">
    <div class="col-12">
        <button class="btn btn-success" type="button" id="addRow">
            <i class="fa fa-plus-square" aria-hidden="true"></i> Add
        </button>
    </div>
    <div class="col-12">
        {{ Form::submit('Submit', ['class' => 'btn btn-primary btn-lg float-right']) }}
    </div>
</div>