<div class="tab-pane" id="tab_data9" aria-labelledby="tab9" role="tabpanel">
    <div class="card-body">
        <div class="row mb-1">
            <div class="col-2">
                <label for="category">Expense Category</label>                             
                @php($categories=['Inventory Stock', 'Direct Purchase Stock', 'Direct Purchase Service', 'Purchase Order Stock'])
                <select class="custom-select" id="expCategory">
                    <option value="">-- Select Expense --</option>
                    @foreach ($categories as $value)
                        <option value="{{ $value }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-3">
                <label for="account">Accounting Ledger</label>
                <select class="custom-select" id="accountLedger" data-placeholder="Search Accounting Ledger">
                    <option value=""></option>
                    @foreach ($exp_accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->number }}-{{ $account->holder }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-3">
                <label for="supplier">Supplier</label>   
                <select class="custom-select" id="supplier" data-placeholder="Choose Supplier">
                    <option value=""></option>           
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <table id="expTotals" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Inventory Stock</th>
                    <th>Labour Service</th>
                    <th>Direct Purchase Stock</th>
                    <th>Direct Purchase Service</th>
                    <th>Purchase Order Stock</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1000</td>
                    <td>1000</td>
                    <td>1000</td>
                    <td>1000</td>
                    <td>1000</td>
                </tr>
            </tbody>
        </table>

        <table id="expItems" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Expense Category</th>
                    <th>Supplier</th>
                    <th>Item Description</th>
                    <th>UoM</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Inventory Stock</td>
                    <td>Front-freeze</td>
                    <td>Product 1</td>
                    <td>LOT</td>
                    <td>1</td>
                    <td>1,000</td>
                    <td>1,000</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
