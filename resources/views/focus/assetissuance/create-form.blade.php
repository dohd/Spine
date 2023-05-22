
@php
/**
<div class="table-responsive">
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
                <td>{{ Form::text('name[]', null, ['class' => 'form-control stockname', 'placeholder' => 'Product Name', 'id' => 'stockname-0', 'required']) }}</td>
                <td>{{ Form::text('qty[]', null, ['class' => 'form-control qty', 'id' => 'qty-0', 'required']) }}</td>
                <td>{{ Form::text('serial_number[]', null, ['class' => 'form-control serial_number', 'id' => 'serial_number-0', 'required']) }}</td>
                <td>{{ Form::text('qty_issued[]', null, ['class' => 'form-control qty_issued', 'placeholder' => '0.00', 'id' => 'qty_issued-0', 'required']) }}</td>
                <td><a href="javascript:" class="btn btn-light remove"><i class="danger fa fa-trash fa-lg"></i></a></td> 

                {{-- <td><input type="text" class="form-control stockname" name="name[]" placeholder="Product Name" id="stockname-0"></td>
                <td><input disabled type="number" class="form-control qty" name="qty[]" id="qty-0" value="1"></td>  
                <td><input disabled type="text" class="form-control serial_number" name="serial_number[]" id="serial-0" value="1"></td> 
                <td><input type="number" class="form-control issued" name="qty_issued[]" id="issued-0"></td>
                <td><button type="button" class="btn btn-danger d-none remove" id="remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                <input type="hidden" id="stockitemid-0" name="item_id[]">
                <input type="hidden" name="id[]" value="0"> --}}
            </tr>
            <!-- end layout -->

            <!-- fetched rows -->
            {{-- @isset ($assetissuance_items)
                @php ($i = 0)
                @foreach ($assetissuance_items as $item)
                    @if ($item)
                        <tr>
                            <td><input type="text" class="form-control stockname" name="name[]" value="{{ $item->name }}" placeholder="Product Name" id='stockname-{{$i}}'></td>
                            <td><input type="number" class="form-control qty" name="qty[]" value="{{ $item->qty }}" id="qty-{{$i}}"></td>                    
                            <td><input type="number" class="form-control serial_number" name="serial_number[]" id="serial-{{$i}}" value="{{ $item->serial_number }}"></td>
                            <td><input type="number" class="form-control issued" name="qty_issued[]" id="issued-{{$i}}" value="{{ $item->qty_issued}}"></td>
                            <td><button type="button" class="btn btn-danger remove" id="remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                            <input type="hidden" id="stockitemid-{{$i}}" name="item_id[]" value="{{ $item->item_id }}">
                            <input type="hidden" name="id[]" value="{{ $item->id }}">
                        </tr>
                        @php ($i++)
                    @endif
                @endforeach
            @endisset --}}
        </tbody>
    </table>
</div>
<a href="javascript:" class="btn btn-success" aria-label="Left Align" id="addstock"><i class="fa fa-plus-square"></i> Add Row</a>
*/
@endphp