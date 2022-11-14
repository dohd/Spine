<div class="form-group row">
    <div class="col-2">
        <label for="month">Sale Month</label>
        <select name="sale_month" id="sale_month" class="custom-select">
            <option value="">All</option>
            @foreach (range(1,12) as $v)
                <option value="{{ $v }}" {{ $v == date('m')? 'selected' : '' }}>
                    {{ DateTime::createFromFormat('!m', $v)->format('F') }}
                </option>
            @endforeach
        </select>
    </div>
    
    <div class="col-2">
        <label for="status">Tax Rate</label>
        <select name="sale_tax_rate" id="sale_tax_rate" class="custom-select">
            @foreach ($additionals as $row)
                <option value="{{ $row->value }}" {{ $row->is_default? 'selected' : '' }}>
                    {{ $row->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-3 ml-auto">
        <label for="file_status">Action Status</label>
        <div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="sale_file_all" id="sale_file_all">
                <label for="sale_file_all">File All</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="sale_file_all" id="sale_remove_all">
                <label class="text-danger" for="sale_remove_all">Remove All</label>
            </div>
        </div>
    </div>
</div>

<div class="responsive">
    <table id="saleTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>#</th>
                <th>Type</th>
                <th>Invoice Date</th>
                <th>Buyer</th>
                <th>Invoice / Credit Note No.</th>
                <th>Description</th>
                <th>Taxable Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody></tbody>                      
    </table>
</div>

<div>
    <div class="ml-auto col-2">
        <div class="label">Total Taxable Amount</div>
        {{ Form::text('sale_subtotal', null, ['class' => 'form-control', 'id' => 'sale_subtotal', 'readonly']) }}
    </div>
    <div class="ml-auto col-2">
        <div class="label">Total Tax</div>
        {{ Form::text('sale_tax', null, ['class' => 'form-control', 'id' => 'sale_tax', 'readonly']) }}
    </div>
    <div class="ml-auto col-2">
        <div class="label">Total Amount (VAT Inc)</div>
        {{ Form::text('sale_total', null, ['class' => 'form-control', 'id' => 'sale_total', 'readonly']) }}
    </div>
</div>