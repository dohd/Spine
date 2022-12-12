<div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
    <table class="table-responsive tfr my_stripe" id="stockTbl">
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
                <td><input type="text" class="form-control stockname" name="name[]" placeholder="Product Name" id='stockname-0'></td>
                <td><input type="text" class="form-control qty" name="qty[]" id="qty-0" value="1"></td>  
                <td><input type="text" class="form-control serial_number" name="serial_number[]" id="serial-0" value="1"></td> 
                <td><input type="text" class="form-control issued" name="qty_issued[]" id="issued-0"></td>
                <td><button type="button" class="btn btn-danger d-none remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                <input type="hidden" id="stockitemid-0" name="item_id[]">
                <input type="hidden" class="stocktaxr" name="taxrate[]">
                <input type="hidden" class="stockamountr" name="amount[]">
                <input type="hidden" class="stockitemprojectid" name="itemproject_id[]" value="0">
                <input type="hidden" name="type[]" value="Stock">
                <input type="hidden" name="id[]" value="0">
            </tr>
            <!-- end layout -->

            <!-- fetched rows -->
            @isset ($assetreturned_items)
                @php ($i = 0)
                @foreach ($assetreturned_items as $item)
                    @if ($item)
                        <tr>
                            <td><input type="text" class="form-control stockname" name="name[]" value="{{ $item->name }}" placeholder="Product Name" id='stockname-{{$i}}'></td>
                            <td><input type="text" class="form-control qty" name="qty[]" value="{{ $item->qty }}" id="qty-{{$i}}"></td>                    
                            <td><input type="text" class="form-control serial_number" name="serial_number[]" id="serial-{{$i}}" value="{{ $item->serial_number }}"></td>
                            <td><input type="text" class="form-control issued" name="qty_issued[]" id="issued-{{$i}}" value="{{ $item->qty_issued}}"></td>
                            <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                            <input type="hidden" id="stockitemid-{{$i}}" name="item_id[]" value="{{ $item->item_id }}">
                            {{-- <input type="hidden" class="stocktaxr" name="taxrate[]" value="{{ (float) $item->taxrate }}">
                            <input type="hidden" class="stockamountr" name="amount[]" value="{{ (float) $item->amount }}">
                            <input type="hidden" class="stockitemprojectid" name="itemproject_id[]" value="0">
                            <input type="hidden" name="type[]" value="Stock"> --}}
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                        </tr>
                        {{-- <tr>
                            <td colspan=2>
                                <textarea id="stockdescr-{{$i}}" class="form-control descr" name="description[]" placeholder="Product Description">{{ $item->description }}</textarea>
                            </td>
                            <td colspan="6"></td>
                        </tr> --}}
                        @php ($i++)
                    @endif
                @endforeach
            @endisset
            <!-- end fetched rows -->
            <tr class="bg-white">
                <td>
                    <button type="button" class="btn btn-success" aria-label="Left Align" id="addstock">
                        <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                    </button>
                </td>
                <td colspan="7"></td>
            </tr>
        </tbody>
    </table>
    
</div>
