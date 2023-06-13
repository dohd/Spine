@extends ('core.layouts.app')

@section ('title', 'Individual | Surcharges')

@section('content')
<div>
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Items Per Requisition Surcharges</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        {{-- @include('focus.pricelistsSupplier.partials.pricelists-header-buttons') --}}
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                       
                        <div class="card-content">
                            <div class="card-body">
                                {{ Form::model($items, ['route' => ['biller.surcharges.pay', $items], 'method' => 'PUT']) }}
                                     @csrf
                                {{-- <form action="{{ route('biller.surcharges.pay') }}" method="put">
                                    @csrf
                                    @method('PATCH') --}}
                                        <div class="table-responsive mt-5">
                                            <table class="table text-center tfr my_stripe_single" id="issueTbl">
                                                <thead>
                                                    <tr class="item_header bg-gradient-directional-blue white ">
                                                        <th class="text-center">Product Name</th>
                                                        <th class="text-center">Issued Qty</th>
                                                        <th class="text-center">Returned Qty</th>
                                                        <th class="text-center">Serial Number</th>
                                                        <th class="text-center">Total Cost</th>  
                                                        <th class="text-center">To Pay items</th> 
                                                        <th class="text-center">Per Item Cost</th> 
                                                        <th class="text-center">Total Cost of lost</th>
                                                        <th class="text-center">Enter Cost</th>            
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($items->item as $item_issued)
                                                    @if ($item_issued->qty_issued >0)
                                                    <tr>
                                                        <td>{{$item_issued->name}}</td>
                                                        <td>{{$item_issued->qty_issued}}</td>
                                                        <td class="text-center">
                                                            @if ($item_issued->qty_issued > 0) 
                                                                <input type="hidden" name="" value="{{ $total = \App\Models\assetreturned\AssetreturnedItems::where('item_id',$item_issued->item_id)->where('asset_returned_id', $item_issued->asset_returned_id)->sum('returned_item') }}" id="">
                                                            @endif
                                                            {{$total}}
                                                        </td>
                                                    <td>{{$item_issued->serial_number}}</td>
                                                    <td class="text-center">
                                                        @if ($item_issued->qty_issued > 0) 
                                                            <input type="hidden" name="" value="{{ $total_cost = \App\Models\assetreturned\AssetreturnedItems::where('item_id',$item_issued->item_id)->where('asset_returned_id', $item_issued->asset_returned_id)->sum('purchase_price') }}" id="">
                                                        @endif
                                                        {{$total_cost}}
                                                        </td>
                                                        <td>{{$ss = $item_issued->qty_issued - $total}}</td>
                                                        <td>{{$costperitem = $total_cost / $total}}</td>
                                                        <td>{{$costtotal = $ss * $costperitem}}</td>
                                                        <td><input type="number" class="cost" name="cost[]" id="cost" value="{{$item_issued->pay_price}}"></td>
                                                    <input type="hidden" name="itemId[]" id="itemId" value="{{$item_issued->item_id}}">
                                                    <input type="hidden" name="asset_returned_id[]" id="asset_returned_id" value="{{$item_issued->asset_returned_id}}">
                                                    </tr>
                                                    @endif
                                                @endforeach
                                                </tbody>
                                            </table>
                                            <hr>
                                            <h6>Total: <span class="float-right sum" id="sum"></span></h6>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-primary submit">Update Payable</button>
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </div>
                               {{-- </form> --}}
                               {{ Form::close() }}
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('extra-scripts')
<script>
     $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});
    sum();
    $('.submit').click(function (e) { 
        e.preventDefault();
        var pay_price = $('.sum').text();
        var assetId = $('#asset_returned_id').val();
        console.log(assetId);
        $.ajax({
            method: "put",
            url: "{{ route('biller.surcharges.send') }}",
            data: {
                pay_price: pay_price,
                assetId: assetId,
            },
            success: function (response) {
                // console.log(response);
            }
        });
    });
    // $('form').submit(function (e) { 
    //     e.preventDefault();
    //     console.log($(this).serializeArray());
    //     // var pay_price = $('.sum').text();
    //     // var assetId = $('#asset_returned_id').val();
    //     // var itemid = $('#itemId').val();
    //     // var cost = $('.cost').val();
    //     // console.log(assetId);
    //     // $.ajax({
    //     //     method: "put",
    //     //     url: "{{ route('biller.surcharges.send') }}",
    //     //     data: {
    //     //         pay_price: pay_price,
    //     //         assetId: assetId,

    //     //         cost: cost,
    //     //         itemid: itemid,
    //     //     },
    //     //     success: function (response) {
    //     //         console.log(response);
    //     //     }
    //     // });
        
    // });
    $("#issueTbl").on('input', '.cost', function () {
       var calculated_total_sum = 0;
     
       $("#issueTbl .cost").each(function () {
           var get_textbox_value = $(this).val();
           if ($.isNumeric(get_textbox_value)) {
              calculated_total_sum += parseFloat(get_textbox_value);
              }                  
            });
              $("#sum").html(calculated_total_sum);
       });
       function sum() {
            var calculated_total_sum = 0;
     
            $("#issueTbl .cost").each(function () {
                var get_textbox_value = $(this).val();
                if ($.isNumeric(get_textbox_value)) {
                    calculated_total_sum += parseFloat(get_textbox_value);
                    }                  
                });
                    $("#sum").html(calculated_total_sum);

       }
</script>
@endsection