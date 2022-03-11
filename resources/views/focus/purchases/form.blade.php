<div class="row">
    <div class="col-sm-6 cmp-pnl">
        <div id="customerpanel" class="inner-cmp-pnl">
            <h3 class="title">Bill </h3>                                                                
            <div class="form-group row">
                <div class="col-5">
                    <div><label for="supplier-type">Select Supplier Type</label></div>
                    <div class="d-inline-block custom-control custom-checkbox mr-1">
                        <input type="radio" class="custom-control-input bg-primary" name="supplier_type" id="colorCheck1" value="walk-in" checked>
                        <label class="custom-control-label" for="colorCheck1">Walkin</label>
                    </div>
                    <div class="d-inline-block custom-control custom-checkbox mr-1">
                        <input type="radio" class="custom-control-input bg-purple" name="supplier_type" value="supplier" id="colorCheck3">
                        <label class="custom-control-label" for="colorCheck3">{{trans('suppliers.supplier')}}</label>
                    </div>
                </div>
                <div class="col-7">
                        <label for="payer" class="caption">Search Supplier</label>                                       
                        <select class="form-control" id="supplierbox" data-placeholder="Search Supplier" disabled></select>
                        <input type="hidden" name="supplier_id" value="0" id="supplierid">
                </div>
            </div>
            
            <div class="form-group row">
                <div class="col-sm-8">
                    <label for="payer" class="caption">Supplire Name*</label>
                    <div class="input-group ">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>                                            
                        {{ Form::text('suppliername', null, ['class' => 'form-control round', 'placeholder' => 'Supplier Name', 'id' => 'supplier', 'required']) }}
                    </div>
                </div>
                <div class="col-sm-4"><label for="taxid" class="caption">Tax ID</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                        {{ Form::text('supplier_taxid', null, ['class' => 'form-control round', 'placeholder' => 'Tax Id', 'id'=>'taxid', 'required']) }}
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <table class="table-responsive tfr" id="transxnTbl">
                    <thead>
                        <tr class="item_header bg-gradient-directional-blue white">
                            @foreach (['Item', 'Inventory Item', 'Expenses', 'Asset & Equipments', 'Total'] as $val)
                                <th width="20%" class="text-center">{{ $val }}</th>
                            @endforeach                                                  
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">Line Total</td>
                            @for ($i = 0; $i < 4; $i++)
                                <td class="text-center">0.00</td>
                            @endfor                                                
                        </tr>                                                  
                        <tr>
                            <td class="text-center">Tax</td>
                            @for ($i = 0; $i < 4; $i++)
                                <td class="text-center">0.00</td>
                            @endfor                                                
                        </tr>
                        <tr>
                            <td class="text-center">Grand Total</td>
                            @for ($i = 0; $i < 4; $i++)
                                <td class="text-center">0.00</td>
                            @endfor                                                                                                      
                        </tr>
                        <tr class="sub_c" style="display: table-row;">
                            <td align="right" colspan="3">
                                @foreach (['paidttl', 'grandtax', 'grandttl'] as $val)
                                    <input type="hidden" name="{{ $val }}" id="{{ $val }}" value="0"> 
                                @endforeach 
                                {{ Form::submit('Post Transaction', ['class' => 'btn btn-success sub-btn btn-lg']) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-sm-6 cmp-pnl">
        <div class="inner-cmp-pnl">
            <h3 class="title">{{trans('purchaseorders.properties')}}</h3>
            <div class="form-group row">
                <div class="col-sm-4">
                    <label for="tid" class="caption">Transaction ID*</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        {{ Form::number('transxn_ref', @$last_id->tid+1, ['class' => 'form-control round']) }}
                    </div>
                </div>
                <div class="col-sm-4"><label for="transaction_date" class="caption">Purchase Date*</label>
                    <div class="input-group">                                            
                        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'transaction_date', 'data-date-auto-close']) }}
                    </div>
                </div>
                <div class="col-sm-4"><label for="due_date" class="caption">Due Date*</label>
                    <div class="input-group">                                            
                        {{ Form::text('due_date', null, ['class' => 'form-control datepicker', 'id' => 'due_date']) }}
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-4"><label for="ref_type" class="caption">Document Type*</label>
                    <div class="input-group">                                            
                        <select class="form-control" name="doc_ref_type" id="ref_type" required>
                            <option value="">-- Select Type --</option>
                            @foreach (['Invoice', 'Receipt', 'DNote', 'Voucher'] as $val)
                                <option value="{{ $val }}">{{ $val }}</option>
                            @endforeach                                                        
                        </select>
                    </div>
                </div>
                <div class="col-sm-4">
                    <label for="refer_no" class="caption">{{trans('general.reference')}} No.</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>                                            
                        {{ Form::text('doc_ref', null, ['class' => 'form-control round', 'placeholder' => trans('general.reference'), 'required']) }}
                    </div>
                </div>
                <div class="col-sm-4">
                    <label for="taxFormat" class="caption">{{trans('general.tax')}}*</label>
                    <select class="form-control" name="tax" id="tax">
                        @foreach ($additionals as $tax)
                            <option value="{{ (int) $tax->value }}" {{ $tax->is_default ? 'selected' : ''}}>
                                {{ $tax->name }} 
                            </option>
                        @endforeach                                                    
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="project" class="caption">Projects</label>
                        <select class="form-control" name="project_id" id="project" required>
                            <option value="">-- Select Project --</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-12">
                    <label for="toAddInfo" class="caption">{{trans('general.note')}}*</label>
                    {{ Form::textarea('note', null, ['class' => 'form-control', 'placeholder' => trans('general.note'), 'rows'=>'2', 'required']) }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tab Menus -->
<ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified" role="tablist">
    <li class="nav-item bg-gradient-directional-blue">
        <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">Inventory/Stock Items</a>
    </li>
    <li class="nav-item bg-danger">
        <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">Expenses</a>
    </li>
    <li class="nav-item bg-success">
        <a class="nav-link " id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">Assets & Equipments</a>
    </li>
</ul>
<div class="tab-content px-1 pt-1">
    <!-- tab1 -->
    @include('focus.purchases.partials.stock_tab')
    <!-- tab2 -->
    @include('focus.purchases.partials.expense_tab')
    <!-- tab3 -->
    @include('focus.purchases.partials.asset_tab')
</div>
