<div class="row">
    <div class="col-12 cmp-pnl">
        <div id="employee_namepanel" class="inner-cmp-pnl">                        
            <div class="form-group row"> 
                <div class="col-5">
                    <label for="payer" class="caption">Search Employee</label>                                       
                    <select class="form-control" id="employeebox" data-placeholder="Search Employee"></select>
                    <input type="hidden" name="employee_id" value="{{ @$assetissuance->employee_id ?: 1 }}" id="employeeid">
                    <input type="hidden" name="employee_name" value="{{ @$assetissuance->employee_name ?: 1 }}" id="employee">
                </div> 
                <div class="col-3">
                    {{ Form::label( 'issue', 'Date Issued',['class' => 'col control-label']) }}
                    <div class='col'>
                        {{ Form::text('issue_date', null, ['class' => 'form-control box-size datepicker', 'id'=>'issue_date']) }}
                    </div>
                </div> 
                <div class="col-2">
                    
                    {{ Form::label( 'return', 'Expected Return Date',['class' => 'col control-label']) }}
                        <div class='col'>
                            {{ Form::text('return_date', null, ['class' => 'form-control box-size datepicker', 'id'=>'return_date']) }}
                        </div>
                </div>    
                <div class="col-2">
                    <label for="toAddInfo" class="caption">Requisition Number*</label>
                        {{ Form::text('acquisition_number', null, ['class' => 'form-control', 'id'=>'requisition', 'placeholder'=>'Requisition Number', 'rows'=>'1', '']) }}
                    </div>
                </div>                                                           
            </div> 
        </div>
    </div>
</div> 

<div class="cmp-pnl form-group row">
    <div class="col-9 mb-2">
        <label for="toAddInfo" class="caption">Note*</label>
        <div>
            {{ Form::textarea('note', null, ['class' => 'form-control', 'placeholder' => 'Note', 'rows'=>'3', '']) }}
        </div>
    </div>
    <div class="table-responsive card">
        <table class="table text-center tfr my_stripe_single" id="productsTbl">
            <thead>
                <tr class="item_header bg-gradient-directional-blue white ">
                    <th width="35%" class="text-center">Product Name</th>
                    <th width="7%" class="text-center">{{trans('general.quantity')}}</th>
                    <th width="10%" class="text-center">Serial Number</th>
                    <th width="10%" class="text-center">Quantity Issue</th>
                    <th width="10%" class="text-center">Actions</th>               
                </tr>
            </thead>
            <tbody>
                <!-- layout -->
                <tr>
                    <td><input type="text" class="form-control stockname" name="name[]" placeholder="Product Name" id="stockname-0"></td>
                    <td><input disabled type="number" class="form-control qty" id="qty-0" name="qty[]"></td>  
                    <td><input disabled type="text" class="form-control serial_number" name="serial_number[]" id="serial-0" value="1"></td> 
                    <td><input type="number" class="form-control issued" name="qty_issued[]" id="issued-0"></td>
                    <td><button type="button" class="btn btn-danger remove" id="remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                    <input type="hidden" id="stockitemid-0" name="item_id[]">
                    <input type="hidden" name="id[]" value="0">
                    <input type="hidden" id="quantity-0" name="quantity[]">
                    <input type="hidden" id="serial_numb-0" name="serial_number[]">
                    <input type="hidden" id="purchase_price-0" name="purchase_price[]">


                    @isset ($assetissuance_items)
                        @php ($i = 0)
                        @foreach ($assetissuance_items as $item)
                            @if ($item)
                                <tr>
                                    <td><input type="text" class="form-control stockname" name="name[]" value="{{ $item->name }}" placeholder="Product Name" id='stockname-{{$i}}'></td>
                                    <td><input type="text" class="form-control qty" name="qty[]" value="{{ $item->qty }}" id="qty-{{$i}}"></td>                    
                                    <td><input type="text" class="form-control serial_number" name="serial_number[]" id="serial-{{$i}}" value="{{ $item->serial_number }}"></td>
                                    <td><input type="text" class="form-control issued" name="qty_issued[]" id="issued-{{$i}}" value="{{ $item->qty_issued}}"></td>
                                    <td><button type="button" class="btn btn-danger remove" id="remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                                    <input type="hidden" id="stockitemid-{{$i}}" name="item_id[]" value="{{ $item->item_id }}">
                                    <input type="hidden" name="id[]" value="{{ $item->id }}">
                                    <input type="hidden" id="quantity-{{$i}}" name="quantity[]">
                                    
                                </tr>
                                @php ($i++)
                            @endif
                        @endforeach
                    @endisset
                </tr>
            </tbody>
        </table>
    </div>
    <a href="javascript:" class="btn btn-success" aria-label="Left Align" id="addstock"><i class="fa fa-plus-square"></i> Add Row</a>

</div>

<div class="px-2 float-right mb-3">
    {{ Form::submit('Issue Product', ['class' => 'btn btn-primary sub-btn btn-lg']) }}
</div>
